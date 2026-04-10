import hashlib
import hmac
import json
import os
import sqlite3
from http.server import SimpleHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path


BASE_DIR = Path(__file__).resolve().parent
DB_PATH = Path("database") / "account.db"
PBKDF2_ITERATIONS = 100000
DEFAULT_ACCOUNTS = (
  ("ua", "admin123", "ua@example.com"),
  ("UATest1", "1234", "uatest1@example.com"),
)


def hash_password(password: str, salt: bytes, iterations: int) -> str:
  return hashlib.pbkdf2_hmac("sha256", password.encode("utf-8"), salt, iterations).hex()


def connect_database() -> sqlite3.Connection:
  DB_PATH.parent.mkdir(exist_ok=True)
  connection = sqlite3.connect(f"file:{DB_PATH.as_posix()}?nolock=1", uri=True)
  connection.execute("PRAGMA journal_mode=MEMORY")
  return connection


def ensure_database() -> None:
  connection = connect_database()
  try:
    connection.execute(
      """
      CREATE TABLE IF NOT EXISTS Account (
        ID VARCHAR(50) PRIMARY KEY,
        PasswordHash VARCHAR(64) NOT NULL,
        Salt VARCHAR(32) NOT NULL,
        Iterations INTEGER NOT NULL,
        Email VARCHAR(255) NOT NULL
      )
      """
    )

    for user_id, password, email in DEFAULT_ACCOUNTS:
      existing = connection.execute("SELECT 1 FROM Account WHERE ID = ?", (user_id,)).fetchone()
      if existing is not None:
        continue

      salt = os.urandom(16)
      password_hash = hash_password(password, salt, PBKDF2_ITERATIONS)
      connection.execute(
        """
        INSERT INTO Account (ID, PasswordHash, Salt, Iterations, Email)
        VALUES (?, ?, ?, ?, ?)
        """,
        (user_id, password_hash, salt.hex(), PBKDF2_ITERATIONS, email),
      )

    connection.commit()
  finally:
    connection.close()


def verify_credentials(user_id: str, password: str) -> bool:
  connection = connect_database()
  try:
    row = connection.execute(
      "SELECT PasswordHash, Salt, Iterations FROM Account WHERE ID = ?",
      (user_id,),
    ).fetchone()
  finally:
    connection.close()

  if row is None:
    return False

  stored_hash, salt_hex, iterations = row
  computed_hash = hash_password(password, bytes.fromhex(salt_hex), int(iterations))
  return hmac.compare_digest(stored_hash, computed_hash)


class RequestHandler(SimpleHTTPRequestHandler):
  def do_POST(self) -> None:
    if self.path != "/api/login":
      self.send_error(404, "Not Found")
      return

    content_length = int(self.headers.get("Content-Length", "0"))
    raw_body = self.rfile.read(content_length)

    try:
      payload = json.loads(raw_body.decode("utf-8"))
    except json.JSONDecodeError:
      self._send_json(400, {"success": False, "message": "Invalid JSON"})
      return

    user_id = str(payload.get("id", "")).strip()
    password = str(payload.get("password", ""))
    if not user_id or not password:
      self._send_json(400, {"success": False, "message": "Missing credentials"})
      return

    if verify_credentials(user_id, password):
      self._send_json(200, {"success": True})
      return

    self._send_json(401, {"success": False, "message": "Invalid credentials"})

  def _send_json(self, status_code: int, payload: dict) -> None:
    body = json.dumps(payload).encode("utf-8")
    self.send_response(status_code)
    self.send_header("Content-Type", "application/json")
    self.send_header("Content-Length", str(len(body)))
    self.end_headers()
    self.wfile.write(body)


def main() -> None:
  os.chdir(BASE_DIR)
  ensure_database()
  host = os.environ.get("CSIT314_HOST", "127.0.0.1")
  port = int(os.environ.get("CSIT314_PORT", "8000"))
  server = ThreadingHTTPServer((host, port), RequestHandler)
  print(f"Server running at http://{host}:{port}")
  server.serve_forever()


if __name__ == "__main__":
  main()

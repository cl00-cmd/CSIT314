import hashlib
import hmac
import json
import sqlite3
from http.server import SimpleHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path


BASE_DIR = Path(__file__).resolve().parent
DB_PATH = BASE_DIR / "account.db"
PBKDF2_ITERATIONS = 100000


def hash_password(password: str, salt: bytes, iterations: int) -> str:
  return hashlib.pbkdf2_hmac("sha256", password.encode("utf-8"), salt, iterations).hex()


def ensure_database() -> None:
  connection = sqlite3.connect(DB_PATH)
  try:
    connection.execute(
      """
      CREATE TABLE IF NOT EXISTS Account (
        ID TEXT PRIMARY KEY,
        PasswordHash TEXT NOT NULL,
        Salt TEXT NOT NULL,
        Iterations INTEGER NOT NULL,
        Email TEXT NOT NULL
      )
      """
    )

    existing = connection.execute("SELECT 1 FROM Account WHERE ID = ?", ("ua",)).fetchone()
    if existing is None:
      salt = hashlib.sha256(b"default-ua-salt").digest()[:16]
      password_hash = hash_password("admin123", salt, PBKDF2_ITERATIONS)
      connection.execute(
        """
        INSERT INTO Account (ID, PasswordHash, Salt, Iterations, Email)
        VALUES (?, ?, ?, ?, ?)
        """,
        ("ua", password_hash, salt.hex(), PBKDF2_ITERATIONS, "ua@example.com"),
      )

    connection.commit()
  finally:
    connection.close()


def verify_credentials(user_id: str, password: str) -> bool:
  connection = sqlite3.connect(DB_PATH)
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
  ensure_database()
  server = ThreadingHTTPServer(("127.0.0.1", 8000), RequestHandler)
  print("Server running at http://127.0.0.1:8000")
  server.serve_forever()


if __name__ == "__main__":
  main()

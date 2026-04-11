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
  ("ua", "admin123", "ua@example.com", "user_admin"),
  ("UATest1", "1234", "uatest1@example.com", "user"),
)
ALLOWED_ROLES = {"user", "user_admin"}


class Account:
  def __init__(
    self,
    user_id: str,
    email: str,
    role: str = "user",
    password: str | None = None,
    password_hash: str | None = None,
    salt_hex: str | None = None,
    iterations: int = PBKDF2_ITERATIONS,
  ) -> None:
    self.user_id = user_id.strip()
    self.email = email.strip()
    self.role = role.strip() or "user"
    self.password = password
    self.password_hash = password_hash
    self.salt_hex = salt_hex
    self.iterations = iterations

  def set_password(self, password: str) -> None:
    salt = os.urandom(16)
    self.password = password
    self.salt_hex = salt.hex()
    self.password_hash = hash_password(password, salt, self.iterations)

  def verify_password(self, password: str) -> bool:
    if self.password_hash is None or self.salt_hex is None:
      return False

    computed_hash = hash_password(password, bytes.fromhex(self.salt_hex), int(self.iterations))
    return hmac.compare_digest(self.password_hash, computed_hash)

  def to_response_payload(self) -> dict:
    return {
      "id": self.user_id,
      "email": self.email,
      "role": self.role,
    }

  def save_account(self, repository: "AccountRepository") -> bool:
    repository.save(self)
    return True


class AccountRepository:
  def __init__(self, database_path: Path) -> None:
    self.database_path = database_path

  def connect(self) -> sqlite3.Connection:
    self.database_path.parent.mkdir(exist_ok=True)
    connection = sqlite3.connect(f"file:{self.database_path.as_posix()}?nolock=1", uri=True)
    connection.execute("PRAGMA journal_mode=MEMORY")
    return connection

  def ensure_schema(self) -> None:
    connection = self.connect()
    try:
      connection.execute(
        """
        CREATE TABLE IF NOT EXISTS Account (
          ID VARCHAR(50) PRIMARY KEY,
          PasswordHash VARCHAR(64) NOT NULL,
          Salt VARCHAR(32) NOT NULL,
          Iterations INTEGER NOT NULL,
          Email VARCHAR(255) NOT NULL,
          Role VARCHAR(50) NOT NULL DEFAULT 'user'
        )
        """
      )

      columns = {
        row[1]
        for row in connection.execute("PRAGMA table_info(Account)").fetchall()
      }
      if "Role" not in columns:
        connection.execute(
          "ALTER TABLE Account ADD COLUMN Role VARCHAR(50) NOT NULL DEFAULT 'user'"
        )
        connection.execute(
          """
          UPDATE Account
          SET Role = CASE
            WHEN ID = 'ua' THEN 'user_admin'
            ELSE 'user'
          END
          WHERE Role IS NULL OR TRIM(Role) = ''
          """
        )

      connection.commit()
    finally:
      connection.close()

  def get_by_id(self, user_id: str) -> Account | None:
    connection = self.connect()
    try:
      row = connection.execute(
        "SELECT ID, PasswordHash, Salt, Iterations, Email, Role FROM Account WHERE ID = ?",
        (user_id,),
      ).fetchone()
    finally:
      connection.close()

    if row is None:
      return None

    return Account(
      user_id=row[0],
      password_hash=row[1],
      salt_hex=row[2],
      iterations=int(row[3]),
      email=row[4],
      role=row[5],
    )

  def save(self, account: Account) -> None:
    if account.password_hash is None or account.salt_hex is None:
      raise ValueError("Account password has not been set")

    connection = self.connect()
    try:
      connection.execute(
        """
        INSERT INTO Account (ID, PasswordHash, Salt, Iterations, Email, Role)
        VALUES (?, ?, ?, ?, ?, ?)
        """,
        (
          account.user_id,
          account.password_hash,
          account.salt_hex,
          account.iterations,
          account.email,
          account.role,
        ),
      )
      connection.commit()
    finally:
      connection.close()

  def seed_defaults(self, accounts: tuple[tuple[str, str, str, str], ...]) -> None:
    for user_id, password, email, role in accounts:
      existing = self.get_by_id(user_id)
      if existing is not None:
        connection = self.connect()
        try:
          connection.execute(
            "UPDATE Account SET Email = ?, Role = ? WHERE ID = ?",
            (email, role, user_id),
          )
          connection.commit()
        finally:
          connection.close()
        continue

      account = Account(user_id=user_id, email=email, role=role)
      account.set_password(password)
      self.save(account)


class UACreateAccountC:
  def __init__(self, repository: AccountRepository) -> None:
    self.repository = repository

  def save_account(
    self,
    actor_id: str,
    user_id: str,
    email: str,
    password: str,
    role: str = "user",
  ) -> bool:
    actor = self.repository.get_by_id(actor_id)
    if actor is None or actor.role != "user_admin":
      raise PermissionError("Only User Admin accounts can create new users")

    if not user_id.strip() or not email.strip() or not password:
      raise ValueError("Missing account details")

    if role not in ALLOWED_ROLES:
      raise ValueError("Invalid role")

    if self.repository.get_by_id(user_id.strip()) is not None:
      raise ValueError("Account ID already exists")

    account = Account(user_id=user_id, email=email, role=role)
    account.set_password(password)
    return account.save_account(self.repository)


ACCOUNT_REPOSITORY = AccountRepository(DB_PATH)


def hash_password(password: str, salt: bytes, iterations: int) -> str:
  return hashlib.pbkdf2_hmac("sha256", password.encode("utf-8"), salt, iterations).hex()


def connect_database() -> sqlite3.Connection:
  return ACCOUNT_REPOSITORY.connect()


def ensure_database() -> None:
  ACCOUNT_REPOSITORY.ensure_schema()
  ACCOUNT_REPOSITORY.seed_defaults(DEFAULT_ACCOUNTS)


def verify_credentials(user_id: str, password: str) -> dict | None:
  account = ACCOUNT_REPOSITORY.get_by_id(user_id)
  if account is None or not account.verify_password(password):
    return None

  return account.to_response_payload()


class RequestHandler(SimpleHTTPRequestHandler):
  def do_POST(self) -> None:
    if self.path == "/api/login":
      self._handle_login()
      return

    if self.path == "/api/accounts":
      self._handle_create_account()
      return

    self.send_error(404, "Not Found")

  def _read_json_payload(self) -> dict | None:
    content_length = int(self.headers.get("Content-Length", "0"))
    raw_body = self.rfile.read(content_length)

    try:
      return json.loads(raw_body.decode("utf-8"))
    except json.JSONDecodeError:
      self._send_json(400, {"success": False, "message": "Invalid JSON"})
      return None

  def _handle_login(self) -> None:
    payload = self._read_json_payload()
    if payload is None:
      return

    user_id = str(payload.get("id", "")).strip()
    password = str(payload.get("password", ""))
    if not user_id or not password:
      self._send_json(400, {"success": False, "message": "Missing credentials"})
      return

    account = verify_credentials(user_id, password)
    if account is not None:
      self._send_json(200, {"success": True, "account": account})
      return

    self._send_json(401, {"success": False, "message": "Invalid credentials"})

  def _handle_create_account(self) -> None:
    payload = self._read_json_payload()
    if payload is None:
      return

    control = UACreateAccountC(ACCOUNT_REPOSITORY)
    actor_id = str(payload.get("actorId", "")).strip()
    user_id = str(payload.get("id", "")).strip()
    email = str(payload.get("email", "")).strip()
    password = str(payload.get("password", ""))
    role = str(payload.get("role", "user")).strip() or "user"

    try:
      creation_status = control.save_account(actor_id, user_id, email, password, role)
    except PermissionError as error:
      self._send_json(403, {"success": False, "message": str(error)})
      return
    except ValueError as error:
      self._send_json(400, {"success": False, "message": str(error)})
      return

    if creation_status:
      self._send_json(201, {"success": True, "message": "Account Created"})
      return

    self._send_json(500, {"success": False, "message": "Unable to create account"})

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

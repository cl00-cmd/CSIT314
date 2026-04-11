# CSIT314

Simple User Admin (UA) login/logout prototype with a static HTML/CSS/JS frontend and a Python backend.

## Features

- Login form (`login.html`) sends credentials to `POST /api/login`
- Credentials are verified against an SQLite `Account` table
- Accounts include a `Role` field so different user types can be identified
- Passwords are stored as PBKDF2-SHA256 hashes with per-user salts
- Dashboard access (`dashboard.html`) is protected by `sessionStorage`
- Logout page (`logout.html`) clears the login session
- User Admin accounts can create new user accounts from the dashboard

## Project files

- `server.py` - HTTP server and login API, talks to database and third party sources
- `scripts/auth.js` - frontend login/logout flow and dashboard guard
- `database/account.db` - SQL database (auto-created at runtime)
- `login.html`, `dashboard.html`, `logout.html`, `styles.css` - UI

## Run

1. Start the server from the project root:
   - Windows: `py server.py`
   - macOS/Linux: `python3 server.py`
2. Open in your browser:
   - `http://127.0.0.1:8000/login.html`

The server automatically creates `database/account.db`, ensures the `Account` table exists, and seeds default accounts if missing.

## Configuration

- `CSIT314_HOST` (default: `127.0.0.1`)
- `CSIT314_PORT` (default: `8000`)

## Demo credentials

- Account 1
  - User ID: `ua`
  - Password: `admin123`
  - Email: `ua@example.com`
  - Role: `user_admin`
- Account 2
  - User ID: `UATest1`
  - Password: `1234`
  - Email: `uatest1@example.com`
  - Role: `user`

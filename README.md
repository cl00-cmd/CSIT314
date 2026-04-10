# CSIT314

Simple UA (User Admin) login/logout prototype based on BCE and sequence flow.
Authentication now uses a SQL database (SQLite) with an `Account` table.
Login and logout behavior is exposed through reusable methods on the `Account` class.

## Pages

- `login.html` - UA enters ID and password
- `dashboard.html` - blank dashboard with `Welcome User Admin`
- `logout.html` - logout page that clears session and returns to login

## Run

1. Start the server:
   - `cd <your-local-path>/CSIT314`
   - Windows: `py server.py`
   - macOS/Linux: `python3 server.py`
2. Open:
   - `http://127.0.0.1:8000/login.html`

The server creates `database/account.db` automatically (ignored in git) and ensures the `Account` table exists.

## Demo credentials

- Account 1
  - User ID: `ua`
  - Password: `admin123`
  - Email: `ua@example.com`
- Account 2
  - User ID: `UATest1`
  - Password: `1234`
  - Email: `uatest1@example.com`

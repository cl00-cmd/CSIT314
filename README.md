# CSIT314

Simple UA (User Admin) login/logout prototype based on BCE and sequence flow.
Authentication now uses a SQL database (SQLite) with an `Account` table.
Login and logout behavior is exposed through reusable methods on the `Account` class.

## Pages

- `login.html` — UA enters ID and password
- `dashboard.html` — blank dashboard with `Welcome User Admin`
- `logout.html` — logout page that clears session and returns to login

## Run

1. Start the server:
   - `python3 /home/runner/work/CSIT314/CSIT314/server.py`
2. Open:
   - `http://127.0.0.1:8000/login.html`

The server creates `account.db` automatically (ignored in git) and ensures the `Account` table exists.

## Demo credentials

- User ID: `ua`
- Password: `admin123`
- Email: `ua@example.com`
- User ID: `UATest1`
- Password: `1234`
- Email: `uatest1@example.com`

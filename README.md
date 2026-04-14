# CSIT314

Simple User Admin (UA) login/logout prototype with a static HTML/CSS/JS frontend and a Python backend backed by MySQL.

## Features

- Login form (`login.html`) sends credentials to `POST /api/login`
- Credentials are verified against a MySQL `Account` table
- Accounts include a `Role` field so different user types can be identified
- Passwords are stored as PBKDF2-SHA256 hashes with per-user salts
- Dashboard access (`dashboard.html`) is protected by `sessionStorage`
- Logout page (`logout.html`) clears the login session
- User Admin accounts can create new user accounts from the dashboard

## Project files

- `server.py` - HTTP server and login API, talks to database and third party sources
- `scripts/auth.js` - frontend login/logout flow and dashboard guard
- `database/schema.sql` - MySQL schema reference for the `Account` table
- `login.html`, `dashboard.html`, `logout.html`, `styles.css` - UI

## Run
Prerequisite: Open Powershell
1. Install the Python dependency:
   - Windows: `py -m pip install -r requirements.txt`
   - macOS/Linux: `python3 -m pip install -r requirements.txt`
2. Make sure MySQL is running and phpMyAdmin points to the same MySQL server.
3. Optional environment variables for the MySQL connection:
   - `DB_HOST` (default: `127.0.0.1`)
   - `DB_PORT` (default: `3306`)
   - `DB_USER` (default: `root`)
   - `DB_PASSWORD` (default: empty)
   - `DB_NAME` (default: `csit314`)
4. Start the server from the project root:
   - Windows: `py server.py`
   - macOS/Linux: `python3 server.py`
5. Open in your browser:
   - `http://127.0.0.1:8000/login.html`

The server automatically creates the MySQL database if it does not exist, ensures the `Account` table exists, and seeds default accounts if missing.

## Using XAMPP

If you are testing locally with XAMPP:

1. Start `MySQL` in the XAMPP Control Panel.
2. Leave MySQL running while testing this project.
3. Open phpMyAdmin if you want to inspect the database.
4. You may optionally import `database/schema.sql` manually, but the Python app can also create the database and table automatically.
5. This project does not run from XAMPP `htdocs` like a PHP project. You still need to start the backend with `py server.py`.

## Configuration

- `CSIT314_HOST` (default: `127.0.0.1`)
- `CSIT314_PORT` (default: `8000`)
- `DB_HOST` (default: `127.0.0.1`)
- `DB_PORT` (default: `3306`)
- `DB_USER` (default: `root`)
- `DB_PASSWORD` (default: empty)
- `DB_NAME` (default: `csit314`)

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
  - Role: `user_admin`

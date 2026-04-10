# CSIT314

Simple UA (User Admin) login/logout prototype based on BCE and sequence flow.
On successful login, account details are stored in browser IndexedDB in an `Account` store with `ID`, `Password` (PBKDF2 hash string), and `Email` fields.

## Pages

- `login.html` — UA enters ID and password
- `dashboard.html` — blank dashboard with `welcome User Admin`
- `logout.html` — logout page that clears session and returns to login

## Run

Open `/home/runner/work/CSIT314/CSIT314/login.html` in a browser.

## Demo credentials

- User ID: `ua`
- Password: `admin123`
- Email: `ua@example.com`

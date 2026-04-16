# CSIT314 BCE PHP Fundraising System

This project is now rebuilt as a PHP `Boundary -> Controller -> Entity` fundraising website for the CSIT314 assignment.

## Architecture

- `Boundary/` contains the user-facing PHP pages.
- `Controller/` contains the application logic called by each boundary page.
- `Entity/` contains the database access layer only.
- `config/Database.php` manages the shared PDO MySQL connection.
- `database/schema.sql` creates the full system tables.
- `database/setup.php` creates the schema and loads large demo data.
- `assets/css/app.css` contains the shared styling.

## Assignment Coverage

- Manage different user accounts and profiles for `user_admin`, `fund_raiser`, `donor`, and `platform_manager`.
- Fund raisers can create and update fundraising activities.
- Donors can search, view, favourite, and donate to campaigns.
- Fund raisers can track campaign views and shortlist counts.
- Fund raisers can search completed FSA history by service type and date period.
- Donors can search donation history and see campaign progress by category and date period.
- Platform managers can manage categories and view daily, weekly, and monthly reports.

## Run With XAMPP

1. Put this project inside your XAMPP `htdocs` folder, or configure Apache to serve this folder.
2. Start `Apache` and `MySQL` in XAMPP.
3. Make sure PHP PDO MySQL is enabled.
4. Optional environment variables:
   - `DB_HOST` default `127.0.0.1`
   - `DB_PORT` default `3306`
   - `DB_NAME` default `csit314_fundraising`
   - `DB_USER` default `root`
   - `DB_PASSWORD` default empty
5. Run `database/setup.php` once from the browser or CLI to create tables and seed demo data.
6. Open `Boundary/login.php`.

If you already ran an older version of this project, run `database/setup.php` again so the latest schema changes are applied.

## Demo Credentials

- `admin01 / password123`
- `fr01 / password123`
- `donor01 / password123`
- `pm01 / password123`

## Demo Data

`database/setup.php` creates large sample data for live demonstration:

- 100 users
- 100 categories
- 120 campaigns
- 180+ favourites
- 300 views
- 220 donations

## Notes

- The older Python prototype files are still in the repository as legacy reference, but the new website flow is the PHP BCE implementation.
- In BCE usage for this project, each Boundary page calls a Controller, and each Controller calls one or more Entities for database work.

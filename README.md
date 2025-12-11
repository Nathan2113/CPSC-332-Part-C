# CPSC-332-Part-C – Movie Theatre App

## Prerequisites
- PHP 8+ (CLI). On Windows with XAMPP: `C:/xampp/php/php.exe`.
- MySQL/MariaDB (tested with MariaDB 10.4 via XAMPP).
- Composer not required.

## Setup
1) Install PHP and MySQL/MariaDB (XAMPP OK).
2) Copy the repo to your machine.
3) Configure DB connection in `includes/config.php` (default for XAMPP):
   - `DB_DSN = mysql:host=127.0.0.1;port=3306;dbname=MovieTheatreDB;charset=utf8mb4`
   - `DB_USER = root`
   - `DB_PASS = ''` (set if you have one)
4) Import SQL (from `sql/`):
   ```bash
   mysql -u root < sql/movie_theatre_schema.sql
   mysql -u root < sql/triggers.sql
   mysql -u root < sql/procedures.sql
   mysql -u root < sql/views.sql
   mysql -u root < sql/movie_theatre_dml.sql
   ```
   If imports complain about duplicates, drop the DB and re-run the files in that order.
5) Start MySQL (via XAMPP Control Panel or `C:/xampp/mysql_start.bat`).
6) Start the PHP built-in server from project root:
   ```bash
   C:/xampp/php/php.exe -S localhost:8000 -t public
   ```

## Sample credentials / data
- Use any email/name at purchase time; customers are auto-created.
- The seed data includes movies, showtimes, seats, and tickets. Seats marked “Sold” are from the seed tickets.

## Quick test script (click path)
1) Browse movies: `http://localhost:8000/movies.php`
2) Find a movie, click it, then click a showtime’s “View Seats”.
3) On the seat map, pick a few available seats, enter name/email, submit to purchase.
4) After purchase, you land on `my_tickets.php` showing your tickets; try “Refund” on one.
5) View reports: `http://localhost:8000/reports.php`

## Assets and SQL
- CSS: `public/assets/styles.css`
- SQL: all scripts are under `sql/` (schema, triggers, procedures, views, data, backup).

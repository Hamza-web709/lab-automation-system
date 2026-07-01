# Smart Lab Automation System

Core PHP, MySQL, GSAP, and Lottie web application for internal electrical appliance laboratory testing workflows.

## Features

- Secure session authentication with `password_hash`, `password_verify`, and CSRF tokens.
- Dashboard analytics for products, testing status, pass/fail counts, CPRI movement, and re-making.
- Product type, testing department, and testing type CRUD modules.
- Product registration with generated 10-character Product ID.
- Testing records with generated 12-character Test ID.
- Multiple tester persons per testing record.
- Advanced search by product, test, department, result, status, date, tester, and next action.
- Product reports, testing reports, and print-ready single test reports.
- Premium responsive light dashboard UI using GSAP animations and local Lottie JSON placeholders.

## Tech Stack

- HTML5, CSS3, Vanilla JavaScript
- Core PHP with PDO prepared statements
- MySQL
- GSAP and ScrollTrigger via CDN
- Lottie Web via CDN with local JSON files in `assets/lottie`
- Apache/XAMPP compatible

## Folder Structure

```text
assets/
  css/
  js/
  lottie/
  images/
config/
includes/
auth/
admin/
  products/
  product-types/
  testing-departments/
  testing-types/
  tests/
  search/
  reports/
  users/
database/
```

## Database Setup

1. Start Apache and MySQL in XAMPP.
2. Open phpMyAdmin.
3. Import `database/lab_automation.sql`.
4. The import creates `lab_automation_db`, tables, seed master data, and the default admin account.

Default login:

```text
Email: admin@labautomation.test
Password: admin123
```

Role logins:

```text
Admin:       admin or admin@labautomation.test     / admin123
Lab Manager: manager or manager@labautomation.test / manager123
Tester:      tester or tester@labautomation.test   / tester123
```

For an existing database, run `database/role_update.sql` after importing the original schema. It adds `users.username`, adds `testing_records.assigned_tester_id`, ensures the role enum supports all three roles, and seeds the default role accounts.

If you only need the username-login change on an already role-enabled database, run `database/username_update.sql`.

## XAMPP Setup

Place this folder inside `C:\xampp\htdocs`.

Current workspace URL:

```text
http://localhost/lab-automation/
```

If you rename the folder to `lab-automation-system`, use:

```text
http://localhost/lab-automation-system/
```

The app auto-detects its base folder under `htdocs`.

## Product ID Generation

Product IDs are generated in `includes/functions.php` using:

```text
product type code + revise number + manufacturing number
```

The result is normalized to uppercase letters/numbers, trimmed or padded to 10 characters, then checked for uniqueness. If a duplicate exists, a safe sequence is added at the end.

## Test ID Generation

Test IDs are generated using:

```text
product code + revise number + testing code + test roll number
```

The result is normalized to 12 characters and checked against existing testing records. Duplicate values receive a safe sequence suffix.

## Workflow

1. Register master data: Product Types, Testing Departments, Testing Types.
2. Register manufactured products.
3. Create testing records with criteria, expected output, observed output, remarks, result, status, next action, and tester persons.
4. Passing internal tests can be marked for CPRI.
5. Failed tests can be sent for re-making.
6. Reports and advanced search provide traceable product/testing history.

## Notes

- Database credentials live in `config/database.php`.
- Local Lottie files are lightweight placeholders and can be replaced with richer JSON animations using the same filenames.
- All admin pages are protected by `includes/auth_check.php`.
- Output is escaped with `e()` and database writes use PDO prepared statements.

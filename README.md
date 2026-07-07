# Human Resource Management System (HRMS)

A full-stack Human Resource Management System built using PHP, MySQL, HTML, CSS and JavaScript. This repository contains a modular HRMS with user authentication, profile management, attendance tracking, leave management, and payroll overview pages.

## Quick Start / Installation

These steps will get you a development environment running locally.

1. Clone the repository

   git clone https://github.com/abhiXmondal/Human-Resource-Management-System.git
   cd Human-Resource-Management-System

2. Create the database

   - Create a MySQL database named `hrms` (or choose another name and update `config/db_connection.php`).
   - Import the schema:

     mysql -u <db_user> -p hrms < schema/schema.sql

3. Configure database credentials

   - Edit `config/db_connection.php` with your DB_HOST, DB_USER, DB_PASSWORD and DB_NAME values.
   - For better security, move credentials to environment variables and modify `db_connection.php` accordingly.

4. Create an initial admin user

   - Run the helper script to create an admin account (recommended):

     php scripts/create_admin.php

   The script will prompt for email, full name, password and employee ID. It will insert the admin user and create an empty profile row.

5. Serve the project

   - Use a local web server such as XAMPP, MAMP or PHP's built-in server:

     php -S localhost:8000

   - Open your browser at `http://localhost:8000/login.html` to log in.


## Feature Overview

- User registration and authentication (signup/login)
- Role-based access (admin vs employee)
- Employee profile management (view/update)
- Attendance tracking and monthly summaries
- Leave request submission and approval workflow
- Payroll overview and payslips


## Developer Notes

- API endpoints live under `api/` and auth endpoints under `auth/`.
- `includes/auth_check.php` and `includes/admin_check.php` protect API routes and pages using PHP sessions.
- Passwords are hashed with `password_hash` and verified with `password_verify`.


## Recommended Next Steps

- Move DB credentials to environment variables and add `.env.example`.
- Add CSRF protection for POST endpoints.
- Add automated tests or a Postman collection for manual API verification.
- Add screenshots and refine the UI; a dark-mode theme is a nice enhancement.


## License

Specify your license here (MIT, Apache-2.0, etc.)

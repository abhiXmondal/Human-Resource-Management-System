# Human Resource Management System (HRMS)

A full-stack Human Resource Management System built using PHP, MySQL, HTML, CSS, JavaScript and Bootstrap. This repository contains a simple, modular HRMS that includes user authentication, employee profiles, attendance tracking, leave management, and payroll overview pages.

## Project Overview

This project provides a web-based HR management system with two primary user roles: admin and employee. Admins can manage employees, process payroll, and approve leaves. Employees can view their attendance, request leave, and view payroll information.

Key features:
- User registration and authentication (signup/login)
- Role-based access (admin vs employee)
- Employee profile management
- Attendance tracking and monthly summaries
- Leave request submission and approval workflow
- Payroll overview and salary breakdown UI


## Repository Structure

- / (root)
  - admindashboard.html, admindashboard.css — Admin UI
  - employee_dashboard.html, employee_dashboard.css, employee_dashboard.js — Employee UI and client-side scripts
  - login.html, signup.html, privacy.html — Authentication & policy pages
  - login.js, signup.js — Client-side auth scripts
  - auth/ (expected) — backend auth endpoints (login.php, signup.php)
  - api/ (expected) — profile, attendance, leave endpoints
  - includes/ (expected) — auth middleware (auth_check.php, admin_check.php)
  - config/ (expected) — db_connection.php
  - README.md — this file


## Installation

1. Clone the repository:

   git clone https://github.com/abhiXmondal/Human-Resource-Management-System.git
   cd Human-Resource-Management-System

2. Configure the database:
   - Create a MySQL database named `hrms` (or update db_connection.php to match your database name).
   - Import the SQL schema (not included in this repository). Create the following minimal tables used by the app: `users`, `employee_profile`, `attendance`, `leave_requests`.

3. Configure web server:
   - Place the project in your web server's document root (e.g., XAMPP htdocs or Apache/Nginx configured root).
   - Ensure PHP (7.4+) and MySQL are available. Adjust DB credentials in `config/db_connection.php`.

4. Open the app in your browser:
   - Visit `/login.html` to log in or `/signup.html` to create an account.


## Development Notes & Conventions

- This repository follows a modular pattern where frontend files are served statically and PHP handles API and server-side logic.
- Sessions are used for authentication (`auth_check.php`).
- Passwords are hashed via `password_hash` and verified with `password_verify`.


## Security & Next Steps

- Add CSRF protection on forms and validate server-side inputs thoroughly.
- Move DB credentials to environment variables or a protected config file outside web root.
- Implement email verification and password reset workflows.
- Add prepared SQL schema and seed data for easier setup.


## Technologies

- Frontend: HTML5, CSS3, JavaScript, Font Awesome
- Backend: PHP (MySQLi), MySQL


## Future Enhancements

- Role-based dashboards with fine-grained permissions
- Exportable attendance and payroll reports (CSV/PDF)
- Notifications and audit logs
- Unit/integration tests for API endpoints


## Screenshots

(Add screenshots of dashboards and pages in this section)


---


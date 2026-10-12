# Medical Case Management

A small Laravel app for tracking medical implant cases from Draft to Completed.
Uses **dummy data only**. No real patient information.

## Tech stack
Laravel, MySQL, Blade + Tailwind (from Laravel Breeze), Laravel Sanctum (API tokens), PHPUnit.

## Features
- Login / logout (Laravel Breeze)
- Roles: Admin (full access) and Viewer (read only)
- Dashboard with case counts
- Case list with search (case number or surgeon), status filter and pagination (10 per page)
- Create / edit form, details page, delete (Admin only, enforced by a Policy on the server)
- REST API with Sanctum tokens

## Setup
```
git clone <your-repo-url>
cd medical-case-app
composer install
npm install
npm run build
copy .env.example .env        (Mac/Linux: cp .env.example .env)
php artisan key:generate
```
Create an empty MySQL database called `medical_cases`, then put your MySQL username and password in `.env` (`DB_USERNAME`, `DB_PASSWORD`).
```
php artisan migrate --seed
php artisan serve
```
Open http://127.0.0.1:8000

## Test logins (dummy)
| Role | Email | Password |
|---|---|---|
| Admin | admin@example.com | Admin@123 |
| Viewer | viewer@example.com | Viewer@123 |

## Run tests
```
php artisan test
```
Tests use an in-memory SQLite database (see `phpunit.xml`), not your MySQL data.

## API
All responses look like `{ "success": true, "data": ..., "message": "..." }`.
Send the token as `Authorization: Bearer <token>` and the header `Accept: application/json`.

| Method | Endpoint | Purpose | Who |
|---|---|---|---|
| POST | /api/login | Get a token | Anyone |
| POST | /api/logout | Revoke the token | Logged-in |
| GET | /api/cases | List cases (paginated) | Admin, Viewer |
| POST | /api/cases | Create a case | Admin |
| GET | /api/cases/{id} | Get one case | Admin, Viewer |
| PUT | /api/cases/{id} | Update a case | Admin |
| DELETE | /api/cases/{id} | Delete a case | Admin |

Status codes: 200, 201, 401 (not logged in), 403 (not allowed), 404 (not found), 422 (validation, with field messages under `errors`).

## Assumptions and known limitations
- Changing a case's status is part of editing, so only Admin can do it.
- New registrations default to the Viewer role (the Breeze register page was kept).
- No Postman collection is included yet.
- Bonus tasks (Engineer role, priority filter, sorting, Nager.Date API, deployment docs) are not done.

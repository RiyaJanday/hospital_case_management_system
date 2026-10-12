# Medical Case Management

A small Laravel web app and REST API for tracking medical implant cases from **Draft** to **Completed**.
Built for the Jajal Medical Software Developer Intern practical assignment.

> **Dummy data only.** No real patient information is stored anywhere in this project.

![Laravel](https://img.shields.io/badge/Laravel-Blade-FF2D20?logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)
![Sanctum](https://img.shields.io/badge/API-Sanctum-orange)
![Tests](https://img.shields.io/badge/tests-48%20feature%20tests-brightgreen)

---

## Table of contents

1. [Features](#features)
2. [Tech stack](#tech-stack)
3. [Roles and permissions](#roles-and-permissions)
4. [Getting started](#getting-started)
5. [Test logins](#test-logins)
6. [Running the tests](#running-the-tests)
7. [REST API](#rest-api)
8. [Project structure](#project-structure)
9. [Security notes](#security-notes)
10. [Documentation](#documentation)
11. [Assumptions and known limitations](#assumptions-and-known-limitations)
12. [Future enhancements](#future-enhancements)

---

## Features

**Web app (Blade)**
- Login and logout with a clear message for a wrong email or password
- Dashboard with counts for Total, Planning, In Review, Approved and Completed cases
- Case list with search (Case Number or Surgeon Name), status filter and pagination (10 per page)
- One create/edit form with validation errors shown next to each field
- Case details page showing every field, who created the case and when
- Works on a laptop and a phone (the table scrolls sideways on small screens)

**REST API (Laravel Sanctum)**
- Token login and logout
- Full case CRUD with one consistent JSON shape
- Correct status codes: 200, 201, 401, 403, 404 and 422, with field-level validation messages

**Case fields**

| Field | Rule |
|---|---|
| Case Number | Required, unique (for example `CASE-0001`) |
| Patient Reference | Required, a dummy code and not a real name |
| Surgeon Name | Required |
| Implant Type | Required |
| Status | Required: Draft, Planning, In Review, Approved, Completed, Cancelled |
| Priority | Required: Low, Medium, High |
| Surgery Date | Optional, must be a valid date |
| Created By | Set automatically to the logged-in user |
| Created At / Updated At | Set automatically |

---

## Tech stack

| Area | Choice |
|---|---|
| Framework | Laravel (PHP) |
| Database | MySQL |
| Frontend | Blade templates with Tailwind CSS (from Laravel Breeze) |
| Web authentication | Laravel Breeze |
| API authentication | Laravel Sanctum tokens |
| Authorization | Laravel Policy (`MedicalCasePolicy`) |
| Validation | Form Request classes |
| Testing | PHPUnit feature tests |

---

## Roles and permissions

| Role | View cases | Create / edit cases | Change status | Delete cases |
|---|---|---|---|---|
| Admin | Yes | Yes | Yes | Yes |
| Viewer | Yes | No | No | No |

Buttons are hidden for users who are not allowed to use them, **and** the server also blocks the request.
A Viewer who sends a delete request by hand gets a `403`.

---

## Getting started

### Requirements

- PHP 8.2 or newer and Composer
- MySQL 8
- Node.js and npm
- Git

### Installation

```
git clone <your-repo-url>
cd medical-case-app
composer install
npm install
npm run build
```

Create your environment file:

```
copy .env.example .env
```

On macOS or Linux use `cp .env.example .env`.

Generate the application key:

```
php artisan key:generate
```

### Database

Create an empty MySQL database:

```sql
CREATE DATABASE medical_cases;
```

Open `.env` and set your MySQL password:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medical_cases
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

Create the tables and add the dummy data (2 users and 25 cases):

```
php artisan migrate --seed
```

To wipe everything and start again, run `php artisan migrate:fresh --seed`.

### Run the app

```
php artisan serve
```

Open http://127.0.0.1:8000 and log in with one of the accounts below.

---

## Test logins

These are dummy accounts created by the seeder.

| Role | Email | Password |
|---|---|---|
| Admin | admin@gmail.com | Admin@123 |
| Viewer | viewer@gmail.com | Viewer@123 |

---

## Running the tests

```
php artisan test
```

The tests use an in-memory SQLite database (set in `phpunit.xml`), so they never touch your MySQL data.

**48 feature tests** cover:

- **Authentication:** valid login, invalid login, and guests blocked from every web page and API endpoint
- **Admin:** create, edit (including changing the status and keeping the same Case Number), delete, and validation errors
- **Viewer:** can see the list and details, has no Create, Edit or Delete buttons, and gets `403` on create, edit, update and delete
- **Case list:** search, status filter, 10 cases per page, and dashboard counts
- **API:** login, using a real token, logout revoking the token, list, show, create, update, delete, and the 401, 403, 404 and 422 responses in the standard shape
- **Seeded accounts:** the logins in this README exist and work

---

## REST API

Send the header `Accept: application/json` on every request.
Protected endpoints also need `Authorization: Bearer <token>`.

### Endpoints

| Method | Endpoint | Purpose | Who can call |
|---|---|---|---|
| POST | `/api/login` | Get an API token | Anyone |
| POST | `/api/logout` | Revoke the current token | Logged-in users |
| GET | `/api/cases` | List cases (paginated, 10 per page) | Admin, Viewer |
| POST | `/api/cases` | Create a case | Admin |
| GET | `/api/cases/{id}` | Get one case | Admin, Viewer |
| PUT | `/api/cases/{id}` | Update a case | Admin |
| DELETE | `/api/cases/{id}` | Delete a case | Admin |

### Response shape

Every response looks like this:

```json
{
  "success": true,
  "data": {},
  "message": "Case created."
}
```

Validation errors (`422`) add an `errors` object with one list of messages per field:

```json
{
  "success": false,
  "data": null,
  "message": "Validation failed.",
  "errors": {
    "case_number": ["The case number has already been taken."]
  }
}
```

### Status codes

| Code | Meaning |
|---|---|
| 200 | OK |
| 201 | Created |
| 401 | Not logged in, or wrong email or password on login |
| 403 | Logged in but not allowed (for example a Viewer trying to delete) |
| 404 | Case not found |
| 422 | Validation failed |

### Example with curl

Log in and copy the token from the response:

```
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"admin@gmail.com\",\"password\":\"Admin@123\"}"
```

List cases:

```
curl http://127.0.0.1:8000/api/cases \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Create a case:

```
curl -X POST http://127.0.0.1:8000/api/cases \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d "{\"case_number\":\"CASE-5001\",\"patient_reference\":\"PAT-50001\",\"surgeon_name\":\"Dr. Shah\",\"implant_type\":\"Knee Implant\",\"status\":\"Draft\",\"priority\":\"High\",\"surgery_date\":\"2026-12-01\"}"
```

These examples use Windows-friendly quoting. On macOS or Linux you can use single quotes around the JSON instead.

---

## Project structure

```
app/
  Http/
    Controllers/
      DashboardController.php        Dashboard counts
      MedicalCaseController.php      Web case screens
      Api/
        AuthController.php           API login and logout
        MedicalCaseController.php    API case endpoints
    Requests/
      MedicalCaseRequest.php         Validation for create and edit (web and API)
      ApiLoginRequest.php            Validation for API login
  Models/
    MedicalCase.php                  Case model, allowed statuses and priorities
    User.php                         User model with the role check
  Policies/
    MedicalCasePolicy.php            Who can view, create, edit and delete
bootstrap/app.php                    JSON error responses for the API
database/
  factories/MedicalCaseFactory.php   Fake case data
  migrations/                        users role column and cases table
  seeders/DatabaseSeeder.php         2 users and 25 cases
resources/views/
  dashboard.blade.php
  cases/                             index, create, edit, show and shared form fields
routes/
  web.php                            Web routes (login required)
  api.php                            API routes (Sanctum)
tests/Feature/
  MedicalCaseTest.php                The 6 required tests
  CaseWebTest.php                    Web behavior and role restrictions
  CaseApiTest.php                    API behavior and status codes
  SeededAccountsTest.php             Seeder and documented logins
docs/
  database.md                        Tables, columns and relationships
  traceability.md                    Requirement to code to test
```

---

## Security notes

- **Authorization on the server.** `MedicalCasePolicy` is checked in every controller action, and `MedicalCaseRequest` blocks non-admins before validation runs.
- **Validation in Form Requests**, not inside controllers. The Case Number unique rule ignores the case being edited.
- **Mass assignment protection** with `$fillable` on `MedicalCase` and the `Fillable` attribute on `User`.
- **Eloquent only.** There is no raw SQL with user input.
- **CSRF protection** stays on for every web form.
- **Secrets stay out of Git.** `.env` is ignored, and `.env.example` has no real passwords.

---

## Documentation

- [Database design](docs/database.md): tables, columns, indexes and how they relate
- [Traceability](docs/traceability.md): requirements linked to their code and their tests

---

## Assumptions and known limitations

- Changing a case's status is part of editing, so only Admin can do it.
- New registrations get the Viewer role. The Breeze register page was kept.
- The API update (`PUT`) expects all required fields, not a partial update.
- No Postman collection is included yet.
- None of the bonus tasks are done.

---

## Future enhancements

- Browser tests (Laravel Dusk) for the Blade pages
- A Postman collection exported into the repo
- Remove the public register page so only admins create accounts
- Bonus tasks: Engineer role, priority filter and column sorting, public holidays from Nager.Date next to the surgery date, `docs/deployment.md`, and a live deployment

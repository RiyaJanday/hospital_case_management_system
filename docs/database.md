# Database

The app uses MySQL. Tables are created with Laravel migrations.

## users
| Column | Type | Notes |
|---|---|---|
| id | bigint, primary key | auto-increment |
| name | varchar | |
| email | varchar | unique |
| password | varchar | hashed |
| role | varchar | `admin` or `viewer` (default `viewer`) |
| created_at, updated_at | timestamp | |

## cases
| Column | Type | Notes |
|---|---|---|
| id | bigint, primary key | auto-increment |
| case_number | varchar | **unique index**, e.g. CASE-0001 |
| patient_reference | varchar | dummy code, not a real name |
| surgeon_name | varchar | |
| implant_type | varchar | |
| status | varchar | **indexed**; Draft, Planning, In Review, Approved, Completed, Cancelled |
| priority | varchar | Low, Medium, High |
| surgery_date | date, nullable | optional |
| created_by | bigint | **foreign key** to `users.id` |
| created_at, updated_at | timestamp | |

## Relationship

```
users (1) ------< (many) cases
  id                       created_by
```

One user can create many cases. Each case belongs to exactly one creating user.

`personal_access_tokens` (created by Laravel Sanctum) stores API tokens. `sessions`, `cache` and `jobs` tables are standard Laravel tables.

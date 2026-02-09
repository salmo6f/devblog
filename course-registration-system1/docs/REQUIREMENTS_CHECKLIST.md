# Requirements Checklist (From PDF)

This document maps the official project requirements to this repository.

## Technology Requirements
- PHP backend (Core PHP): ✅
- Session-based authentication: ✅ (`src/auth.php`)
- Database: MySQL ✅ (`config/db.php`, `database.sql`)
- Frontend: Server-side rendering + Bootstrap styling: ✅ (Bootswatch theme + Bootstrap)
- Git & GitHub: ✅ (use this repo)
- Jira (task creation/assignment/progress): ✅ (project process requirement — see `docs/JIRA_GUIDE.md`)

## Scope (Minimum Features)
### User Management
- Registration: ✅ (`public/register.php`)
- Login: ✅ (`public/login.php`)
- Sessions: ✅ (`src/auth.php`)
- Role-based access control: ✅ (`src/auth.php`, Admin/Student portals)

### CRUD
- Create/Read/Update/Delete via web forms: ✅
  - Courses CRUD: ✅ (`admin/courses.php`, `admin/add-course.php`, `admin/edit-course.php`)
  - Modern school entities: ✅
    - Terms: ✅ (`admin/terms.php`)
    - Departments: ✅ (`admin/departments.php`)
    - Sections: ✅ (`admin/sections.php`)
    - Enrollments: ✅ (`admin/enrollments.php`, `student/sections.php`)

## Architecture / Security
- SQL Injection prevention (prepared statements): ✅ (all auth + most modern pages)
- XSS protection: ✅ (`src/util.php` via `h()`)
- CSRF protection on forms: ✅ (`src/csrf.php` + all POST actions)

## Error Handling
- User-friendly error messages: ✅ (Bootstrap alerts + flash messages)
- Server-side validation: ✅ (login/register + admin/student actions)
- Custom 404 page: ✅ (`public/404.php`)

## Code & Design Requirements
- Clean structure (`config/`, `public/`, `src/`): ✅
- Separation of concerns:
  - Shared bootstrap + auth + view helpers: ✅ (`src/bootstrap.php`, `src/auth.php`, `src/view.php`)
  - Pages focus on flow + templates: ✅
- Responsive UI: ✅ (Bootstrap)

## Submission Requirements
- `database.sql`: ✅ (schema + seed data)
- No real secrets: ✅ (DB uses env vars with safe defaults; do not commit real passwords)


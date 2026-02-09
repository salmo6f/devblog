# Course Registration System

## 1. Project Overview
Course Registration System waa **PHP Web Application** oo loogu talagalay in lagu maareeyo:

- User registration iyo login  
- Role-based access control (Admin & Student)  
- CRUD operations for courses  
- Student course registration  
- Security features (CSRF, SQL injection prevention, XSS protection)  
- Responsive UI using Bootstrap  
- Custom 404 error page  

Project-kan waxaa loogu talagalay **practical learning** ee Web Development, Database Integration, Authentication & Authorization, iyo UI design.

---

## 2. Technology Requirements

**Backend:** PHP (Core PHP)  
**Database:** MySQL  
**Frontend:** PHP Server-Side Rendering + Bootstrap  
**Version Control:** Git & GitHub  
**Project Management:** Jira (task tracking, sprint management)  

---

## 3. Project Scope (Minimum Features)

### 3.1 User Management
- User registration (`public/register.php`)  
- User login (`public/login.php`)  
- Authentication via PHP sessions  
- Role-based access (Admin vs Student)  

### 3.2 CRUD Operations
- Admin:
  - Add/Edit/Delete courses  
  - View all courses (`admin/courses.php`)  
  - View all students (`admin/students.php`)  
- Student:
  - View all courses (`student/courses.php`)  
  - Register for courses  
  - View my courses (`student/my-courses.php`)  

### 3.3 Security
- SQL Injection Prevention (Prepared Statements)  
- CSRF Protection on all forms (`src/csrf.php`)  
- XSS Protection (`htmlspecialchars()` on output)  
- Server-side form validation (`empty()`, `filter_var()` for email)  

### 3.4 Error Handling
- User-friendly error messages for all forms  
- Custom 404 page (`public/404.php`)  

---

## 4. Code & Design Requirements
- Clean, readable, and well-documented code  
- Folder structure:

```
course-registration-system1/
  admin/              # admin pages (require admin login)
  student/            # student pages (require student login)
  public/             # public pages (login/register/landing + assets)
  config/             # DB config (blocked by .htaccess)
  src/                # shared helpers (blocked by .htaccess)
  database.sql        # DB schema + sample data
  index.php           # redirects to public/
```

---

## 5. Setup (XAMPP)

1) Start **Apache** and **MySQL** in XAMPP.

2) Create/import database:
   - Open `http://localhost/phpmyadmin`
   - Import `database.sql`
   - Or run (PowerShell):
     - `powershell -ExecutionPolicy Bypass -File scripts/setup-db.ps1`

Notes:
- `database.sql` includes the modern school tables (terms/sections/enrollments).
- If you already imported an older version and want to upgrade, run:
  - `powershell -ExecutionPolicy Bypass -File scripts/setup-db.ps1 -Modern`

3) Configure DB connection (if needed):
   - File: `config/db.php`
   - Or set env vars:
     - `CRS_DB_HOST` (default `localhost`)
     - `CRS_DB_USER` (default `root`)
     - `CRS_DB_PASS` (default empty)
     - `CRS_DB_NAME` (default `course_registration`)

4) Run the app in the browser:
   - `http://localhost/course-registration-system1/`
   - or `http://localhost/course-registration-system1/public/`

Note: the project folder must be inside the **same** XAMPP Apache `htdocs` you are running (commonly `C:\\xampp\\htdocs` on Windows).

---

## 6. Default Admin Login

- Email: `admin@example.com`
- Password: `admin123`

---

## 7. Pages

### Public
- Landing: `public/index.php`
- Login: `public/login.php`
- Register: `public/register.php`

### Admin
- Dashboard: `admin/dashboard.php`
- Courses: `admin/courses.php`
- Students: `admin/students.php`
- Sections (modern): `admin/sections.php`
- Terms (modern): `admin/terms.php`
- Departments (modern): `admin/departments.php`

### Student
- Dashboard: `student/dashboard.php`
- Browse sections (modern): `student/sections.php`
- My enrollments (modern): `student/my-enrollments.php`
- My courses: `student/my-courses.php`

---

## 8. Requirement Checklist (PDF)

- See `docs/REQUIREMENTS_CHECKLIST.md`
- Jira process notes: `docs/JIRA_GUIDE.md`


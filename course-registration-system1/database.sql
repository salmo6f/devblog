CREATE DATABASE IF NOT EXISTS course_registration;
USE course_registration;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','student') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    course_code VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS student_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_student_course (student_id, course_id),
    CONSTRAINT fk_student_courses_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_student_courses_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin (password: admin123)
-- This is a bcrypt hash; login.php also supports legacy MD5 and will auto-upgrade.
INSERT INTO users (name,email,password,role)
VALUES ('Admin','admin@example.com','$2y$10$adRvHrmBQ17NJswXwIuDfufaM0731CLRkguYT98DBiS5Eqe6AVkrC','admin')
ON DUPLICATE KEY UPDATE role='admin';

-- Sample courses (optional)
INSERT INTO courses (course_name, course_code) VALUES
('Introduction to Programming','CS101'),
('Database Systems','CS205'),
('Web Development','CS220')
ON DUPLICATE KEY UPDATE course_name=VALUES(course_name);

-- =========================
-- Modern school-ready tables
-- =========================

CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    code VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_departments_code (code),
    UNIQUE KEY uniq_departments_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_terms_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_details (
    course_id INT PRIMARY KEY,
    department_id INT NULL,
    credits TINYINT UNSIGNED NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_course_details_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_course_details_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    term_id INT NOT NULL,
    section_code VARCHAR(20) NOT NULL,
    instructor_name VARCHAR(120) NULL,
    days VARCHAR(40) NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    room VARCHAR(60) NULL,
    capacity INT NOT NULL DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_sections_course_term_code (course_id, term_id, section_code),
    CONSTRAINT fk_sections_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_sections_term FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    section_id INT NOT NULL,
    status ENUM('enrolled','waitlisted','dropped') NOT NULL DEFAULT 'enrolled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_enrollments_student_section (student_id, section_id),
    KEY idx_enrollments_section_status (section_id, status),
    CONSTRAINT fk_enrollments_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_enrollments_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed a default active term if none exists
INSERT INTO terms (name, is_active)
SELECT 'Spring 2026', 1
WHERE NOT EXISTS (SELECT 1 FROM terms);

-- Seed one section (optional) for CS101 in the active term
INSERT INTO sections (course_id, term_id, section_code, instructor_name, days, start_time, end_time, room, capacity)
SELECT c.id, t.id, 'A', 'Staff', 'Mon/Wed', '09:00:00', '10:15:00', 'Room 101', 30
FROM courses c
JOIN terms t ON t.is_active=1
WHERE c.course_code='CS101'
  AND NOT EXISTS (SELECT 1 FROM sections)
LIMIT 1;

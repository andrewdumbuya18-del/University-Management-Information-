CREATE DATABASE IF NOT EXISTS smis_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smis_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS final_clearance;
DROP TABLE IF EXISTS exam_clearance;
DROP TABLE IF EXISTS registration_clearance;
DROP TABLE IF EXISTS finance_clearance;
DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS lecturer_modules;
DROP TABLE IF EXISTS student_modules;
DROP TABLE IF EXISTS semester_modules;
DROP TABLE IF EXISTS finance_officers;
DROP TABLE IF EXISTS lecturers;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS modules;
DROP TABLE IF EXISTS semesters;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','student','lecturer','finance') NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role (role),
  INDEX idx_users_status (status)
) ENGINE=InnoDB;

CREATE TABLE classes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  description TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE semesters (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  academic_year VARCHAR(20) NOT NULL,
  starts_on DATE NULL,
  ends_on DATE NULL,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  UNIQUE KEY uq_semester_year (name, academic_year)
) ENGINE=InnoDB;

CREATE TABLE modules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NOT NULL UNIQUE,
  title VARCHAR(150) NOT NULL,
  credits TINYINT UNSIGNED NOT NULL DEFAULT 3,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_modules_title (title)
) ENGINE=InnoDB;

CREATE TABLE students (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  student_number VARCHAR(40) NOT NULL UNIQUE,
  class_id INT UNSIGNED NULL,
  semester_id INT UNSIGNED NULL,
  gender VARCHAR(20) NULL,
  phone VARCHAR(40) NULL,
  address VARCHAR(255) NULL,
  date_of_birth DATE NULL,
  document_path VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_students_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
  CONSTRAINT fk_students_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL,
  INDEX idx_students_class (class_id),
  INDEX idx_students_semester (semester_id)
) ENGINE=InnoDB;

CREATE TABLE lecturers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  staff_number VARCHAR(40) NOT NULL UNIQUE,
  department VARCHAR(120) NULL,
  phone VARCHAR(40) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_lecturers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE finance_officers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  staff_number VARCHAR(40) NOT NULL UNIQUE,
  phone VARCHAR(40) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_finance_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE semester_modules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  semester_id INT UNSIGNED NOT NULL,
  module_id INT UNSIGNED NOT NULL,
  CONSTRAINT fk_semester_modules_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
  CONSTRAINT fk_semester_modules_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
  UNIQUE KEY uq_semester_module (semester_id, module_id)
) ENGINE=InnoDB;

CREATE TABLE student_modules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  module_id INT UNSIGNED NOT NULL,
  semester_id INT UNSIGNED NOT NULL,
  assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_student_modules_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_student_modules_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
  CONSTRAINT fk_student_modules_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
  UNIQUE KEY uq_student_module_semester (student_id, module_id, semester_id),
  INDEX idx_student_modules_module (module_id)
) ENGINE=InnoDB;

CREATE TABLE lecturer_modules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lecturer_id INT UNSIGNED NOT NULL,
  module_id INT UNSIGNED NOT NULL,
  class_id INT UNSIGNED NOT NULL,
  semester_id INT UNSIGNED NOT NULL,
  assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_lecturer_modules_lecturer FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE CASCADE,
  CONSTRAINT fk_lecturer_modules_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
  CONSTRAINT fk_lecturer_modules_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  CONSTRAINT fk_lecturer_modules_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
  UNIQUE KEY uq_lecturer_assignment (lecturer_id, module_id, class_id, semester_id)
) ENGINE=InnoDB;

CREATE TABLE attendance (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  module_id INT UNSIGNED NOT NULL,
  class_id INT UNSIGNED NOT NULL,
  lecturer_id INT UNSIGNED NOT NULL,
  attendance_date DATE NOT NULL,
  status ENUM('present','absent','late') NOT NULL,
  remarks VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_attendance_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_attendance_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
  CONSTRAINT fk_attendance_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  CONSTRAINT fk_attendance_lecturer FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE CASCADE,
  UNIQUE KEY uq_attendance_student_module_date (student_id, module_id, attendance_date),
  INDEX idx_attendance_date (attendance_date)
) ENGINE=InnoDB;

CREATE TABLE grades (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  module_id INT UNSIGNED NOT NULL,
  lecturer_id INT UNSIGNED NOT NULL,
  coursework DECIMAL(5,2) NOT NULL DEFAULT 0,
  examination DECIMAL(5,2) NOT NULL DEFAULT 0,
  final_grade DECIMAL(5,2) NOT NULL DEFAULT 0,
  letter_grade VARCHAR(4) NULL,
  remarks VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_grades_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_grades_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
  CONSTRAINT fk_grades_lecturer FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE CASCADE,
  UNIQUE KEY uq_grade_student_module (student_id, module_id)
) ENGINE=InnoDB;

-- Marking convention enforced by the application:
-- coursework is entered out of 30, examination out of 70,
-- and final_grade is calculated as coursework + examination.

CREATE TABLE finance_clearance (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL UNIQUE,
  status ENUM('pending','cleared','not_cleared') NOT NULL DEFAULT 'pending',
  remarks VARCHAR(255) NULL,
  cleared_by INT UNSIGNED NULL,
  cleared_at DATETIME NULL,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_finance_clearance_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_finance_clearance_user FOREIGN KEY (cleared_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE registration_clearance (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL UNIQUE,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  remarks VARCHAR(255) NULL,
  approved_by INT UNSIGNED NULL,
  approved_at DATETIME NULL,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_registration_clearance_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_registration_clearance_user FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE exam_clearance (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL UNIQUE,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  remarks VARCHAR(255) NULL,
  approved_by INT UNSIGNED NULL,
  approved_at DATETIME NULL,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_exam_clearance_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_exam_clearance_user FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE final_clearance (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL UNIQUE,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  remarks VARCHAR(255) NULL,
  approved_by INT UNSIGNED NULL,
  approved_at DATETIME NULL,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_final_clearance_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_final_clearance_user FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_notifications_user_read (user_id, is_read)
) ENGINE=InnoDB;

CREATE TABLE password_resets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_password_resets_token (token_hash)
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  action VARCHAR(120) NOT NULL,
  entity VARCHAR(120) NULL,
  entity_id VARCHAR(80) NULL,
  ip_address VARCHAR(80) NULL,
  user_agent VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_activity_user (user_id),
  INDEX idx_activity_action (action),
  INDEX idx_activity_created (created_at)
) ENGINE=InnoDB;

INSERT INTO users (id, name, email, password, role, status) VALUES
(1, 'System Administrator', 'admin@smis.test', '$2y$10$FQMiwNoGjGH3wCHvU4g5TuZEF/WF3F7h/s2ksG2ypNV/6mV168mJy', 'admin', 'active'),
(2, 'John Kamara', 'student@smis.test', '$2y$10$FQMiwNoGjGH3wCHvU4g5TuZEF/WF3F7h/s2ksG2ypNV/6mV168mJy', 'student', 'active'),
(3, 'Dr. Aminata Conteh', 'lecturer@smis.test', '$2y$10$FQMiwNoGjGH3wCHvU4g5TuZEF/WF3F7h/s2ksG2ypNV/6mV168mJy', 'lecturer', 'active'),
(4, 'Mary Bangura', 'finance@smis.test', '$2y$10$FQMiwNoGjGH3wCHvU4g5TuZEF/WF3F7h/s2ksG2ypNV/6mV168mJy', 'finance', 'active');

INSERT INTO classes (id, name, description) VALUES
(1, 'BIT 2', 'Bachelor of Information Technology year two'),
(2, 'BCS 1', 'Bachelor of Computer Science year one');

INSERT INTO semesters (id, name, academic_year, starts_on, ends_on, status) VALUES
(1, 'Semester 1', '2026/2027', '2026-09-01', '2026-12-18', 'open'),
(2, 'Semester 2', '2026/2027', '2027-01-12', '2027-05-29', 'open');

INSERT INTO modules (id, code, title, credits) VALUES
(1, 'MIS201', 'Management Information Systems', 3),
(2, 'DBS201', 'Database Systems', 3),
(3, 'WEB201', 'Web Application Development', 3),
(4, 'NET201', 'Computer Networks', 3),
(5, 'PRG201', 'Programming II', 3),
(6, 'SYS201', 'Systems Analysis and Design', 3),
(7, 'MIS101', 'Introduction to MIS', 3),
(8, 'DBS101', 'Database Fundamentals', 3),
(9, 'WEB101', 'Web Design Fundamentals', 3),
(10, 'NET101', 'Networking Fundamentals', 3),
(11, 'PRG101', 'Programming I', 3),
(12, 'SYS101', 'Computer Systems', 3);

INSERT INTO semester_modules (semester_id, module_id) VALUES
(1,1),(1,2),(1,3),(1,4),(1,5),(1,6),
(2,7),(2,8),(2,9),(2,10),(2,11),(2,12);

INSERT INTO students (id, user_id, student_number, class_id, semester_id, gender, phone, address) VALUES
(1, 2, 'STU-2026-001', 1, 1, 'Male', '+232 77 000 001', 'Freetown');

INSERT INTO lecturers (id, user_id, staff_number, department, phone) VALUES
(1, 3, 'LEC-001', 'Computing', '+232 77 000 002');

INSERT INTO finance_officers (id, user_id, staff_number, phone) VALUES
(1, 4, 'FIN-001', '+232 77 000 003');

INSERT INTO student_modules (student_id, module_id, semester_id) VALUES
(1,1,1),(1,2,1),(1,3,1),(1,4,1),(1,5,1),(1,6,1);

INSERT INTO lecturer_modules (lecturer_id, module_id, class_id, semester_id) VALUES
(1,1,1,1),(1,2,1,1),(1,3,1,1);

INSERT INTO finance_clearance (student_id, status, remarks) VALUES (1, 'pending', 'Awaiting payment verification');
INSERT INTO registration_clearance (student_id, status, remarks) VALUES (1, 'pending', 'Awaiting registrar approval');
INSERT INTO exam_clearance (student_id, status, remarks) VALUES (1, 'pending', 'Awaiting examination office approval');
INSERT INTO final_clearance (student_id, status, remarks) VALUES (1, 'pending', 'Final clearance requires all prior approvals');

INSERT INTO notifications (user_id, title, message) VALUES
(2, 'Welcome to SMIS', 'Your student account has been created. Use the dashboard to view modules, grades, attendance, and clearance.');

INSERT INTO activity_logs (user_id, action, entity, entity_id, ip_address, user_agent) VALUES
(1, 'seeded_database', 'system', 'smis_db', '127.0.0.1', 'schema.sql');

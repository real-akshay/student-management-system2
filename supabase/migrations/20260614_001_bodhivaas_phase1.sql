-- Bodhivaas Phase-1 Migration
-- Added: 2026-06-14
-- Non-destructive: creates new tables for modules planned in Phase 1+; run on staging first and backup DB.

SET FOREIGN_KEY_CHECKS=0;

-- ATTENDANCE
CREATE TABLE IF NOT EXISTS attendance (
  id VARCHAR(36) PRIMARY KEY,
  student_id VARCHAR(36) NOT NULL,
  course_id VARCHAR(36),
  attendance_date DATE NOT NULL,
  status ENUM('present','absent','late','excused') NOT NULL DEFAULT 'present',
  marked_by VARCHAR(36) DEFAULT NULL,
  marked_role ENUM('admin','faculty') DEFAULT 'faculty',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_attendance_student (student_id),
  INDEX idx_attendance_course (course_id),
  INDEX idx_attendance_date (attendance_date),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS attendance_reports (
  id VARCHAR(36) PRIMARY KEY,
  student_id VARCHAR(36) NOT NULL,
  course_id VARCHAR(36),
  year_month CHAR(7) NOT NULL,
  present_count INT DEFAULT 0,
  total_count INT DEFAULT 0,
  percentage DECIMAL(5,2) DEFAULT 0.00,
  generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_attrep_student_month (student_id, year_month),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- EXAMS / SUBJECTS / MARKS / RESULTS
CREATE TABLE IF NOT EXISTS subjects (
  id VARCHAR(36) PRIMARY KEY,
  course_id VARCHAR(36) NOT NULL,
  subject_name VARCHAR(150) NOT NULL,
  subject_code VARCHAR(64),
  credits DECIMAL(4,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_course_subject (course_id, subject_code),
  INDEX idx_subject_course (course_id),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS exams (
  id VARCHAR(36) PRIMARY KEY,
  course_id VARCHAR(36) NOT NULL,
  exam_name VARCHAR(150) NOT NULL,
  exam_type VARCHAR(50) DEFAULT 'semester',
  exam_date DATE,
  total_marks INT DEFAULT 100,
  duration_minutes INT DEFAULT NULL,
  created_by VARCHAR(36) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_exam_course (course_id),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS marks (
  id VARCHAR(36) PRIMARY KEY,
  exam_id VARCHAR(36) NOT NULL,
  subject_id VARCHAR(36),
  student_id VARCHAR(36) NOT NULL,
  marks_obtained DECIMAL(7,2) DEFAULT 0,
  max_marks DECIMAL(7,2) DEFAULT 100,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_marks_exam (exam_id),
  INDEX idx_marks_student (student_id),
  FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS results (
  id VARCHAR(36) PRIMARY KEY,
  student_id VARCHAR(36) NOT NULL,
  exam_id VARCHAR(36) NOT NULL,
  total_marks DECIMAL(8,2) DEFAULT 0,
  percentage DECIMAL(5,2) DEFAULT 0.00,
  grade VARCHAR(4),
  gpa DECIMAL(4,2) DEFAULT 0.00,
  published TINYINT(1) DEFAULT 0,
  published_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_result_student (student_id),
  INDEX idx_result_exam (exam_id),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- FEE MANAGEMENT
CREATE TABLE IF NOT EXISTS fees (
  id VARCHAR(36) PRIMARY KEY,
  course_id VARCHAR(36) DEFAULT NULL,
  title VARCHAR(150) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  due_date DATE DEFAULT NULL,
  frequency ENUM('one-time','semester','yearly','monthly') DEFAULT 'one-time',
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_fee_course (course_id),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
  id VARCHAR(36) PRIMARY KEY,
  student_id VARCHAR(36) NOT NULL,
  fee_id VARCHAR(36) DEFAULT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  method VARCHAR(50) DEFAULT 'cash',
  reference VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_payment_student (student_id),
  INDEX idx_payment_fee (fee_id),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (fee_id) REFERENCES fees(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS receipts (
  id VARCHAR(36) PRIMARY KEY,
  payment_id VARCHAR(36) NOT NULL,
  receipt_no VARCHAR(100) NOT NULL UNIQUE,
  generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  pdf_path VARCHAR(255),
  INDEX idx_receipt_payment (payment_id),
  FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NOTES & ASSIGNMENTS
CREATE TABLE IF NOT EXISTS notes (
  id VARCHAR(36) PRIMARY KEY,
  course_id VARCHAR(36) DEFAULT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  file_path VARCHAR(255),
  uploaded_by VARCHAR(36),
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_notes_course (course_id),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS assignments (
  id VARCHAR(36) PRIMARY KEY,
  course_id VARCHAR(36) DEFAULT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  due_date DATETIME,
  max_marks INT DEFAULT 100,
  created_by VARCHAR(36),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_assign_course (course_id),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS assignment_submissions (
  id VARCHAR(36) PRIMARY KEY,
  assignment_id VARCHAR(36) NOT NULL,
  student_id VARCHAR(36) NOT NULL,
  file_path VARCHAR(255),
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  marks_obtained DECIMAL(7,2) DEFAULT NULL,
  feedback TEXT,
  status ENUM('submitted','graded','late','rejected') DEFAULT 'submitted',
  INDEX idx_subm_assignment (assignment_id),
  INDEX idx_subm_student (student_id),
  FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- FACULTY
CREATE TABLE IF NOT EXISTS faculty (
  id VARCHAR(36) PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(32),
  password_hash VARCHAR(255) NOT NULL,
  bio TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS faculty_courses (
  id VARCHAR(36) PRIMARY KEY,
  faculty_id VARCHAR(36) NOT NULL,
  course_id VARCHAR(36) NOT NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_fac_course (faculty_id, course_id),
  FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS faculty_subjects (
  id VARCHAR(36) PRIMARY KEY,
  faculty_id VARCHAR(36) NOT NULL,
  subject_id VARCHAR(36) NOT NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_fac_subj (faculty_id, subject_id),
  FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PLACEMENT
CREATE TABLE IF NOT EXISTS companies (
  id VARCHAR(36) PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  website VARCHAR(255),
  contact_email VARCHAR(150),
  contact_phone VARCHAR(50),
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_company_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS jobs (
  id VARCHAR(36) PRIMARY KEY,
  company_id VARCHAR(36) NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  location VARCHAR(200),
  salary_range VARCHAR(100),
  posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  deadline DATE,
  is_active TINYINT(1) DEFAULT 1,
  INDEX idx_jobs_company (company_id),
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS applications (
  id VARCHAR(36) PRIMARY KEY,
  job_id VARCHAR(36) NOT NULL,
  student_id VARCHAR(36) NOT NULL,
  resume_path VARCHAR(255),
  cover_letter TEXT,
  status ENUM('applied','shortlisted','interview','offered','rejected') DEFAULT 'applied',
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_app_job (job_id),
  INDEX idx_app_student (student_id),
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- LIBRARY
CREATE TABLE IF NOT EXISTS books (
  id VARCHAR(36) PRIMARY KEY,
  isbn VARCHAR(32),
  title VARCHAR(300) NOT NULL,
  author VARCHAR(200),
  publisher VARCHAR(200),
  year_published YEAR,
  copies_total INT DEFAULT 1,
  copies_available INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_book_isbn (isbn),
  INDEX idx_book_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS issued_books (
  id VARCHAR(36) PRIMARY KEY,
  book_id VARCHAR(36) NOT NULL,
  student_id VARCHAR(36) NOT NULL,
  issue_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  due_date DATE,
  return_date TIMESTAMP NULL DEFAULT NULL,
  fine_amount DECIMAL(10,2) DEFAULT 0.00,
  status ENUM('issued','returned','overdue') DEFAULT 'issued',
  INDEX idx_issue_book (book_id),
  INDEX idx_issue_student (student_id),
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CERTIFICATES
CREATE TABLE IF NOT EXISTS certificates (
  id VARCHAR(36) PRIMARY KEY,
  student_id VARCHAR(36) NOT NULL,
  course_id VARCHAR(36) DEFAULT NULL,
  certificate_type VARCHAR(100) NOT NULL,
  issue_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  qr_code TEXT,
  pdf_path VARCHAR(255),
  INDEX idx_cert_student (student_id),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS=1;

-- End of Bodhivaas Phase-1 migration

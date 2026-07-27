-- Schema Database E-Learning untuk PostgreSQL + Supabase

-- Hapus tabel jika sudah ada (untuk inisialisasi ulang)
DROP VIEW IF EXISTS admin_dashboard_stats CASCADE;
DROP TABLE IF EXISTS system_settings CASCADE;
DROP TABLE IF EXISTS feedback CASCADE;
DROP TABLE IF EXISTS failed_login_attempts CASCADE;
DROP TABLE IF EXISTS admin_logs CASCADE;
DROP TABLE IF EXISTS admin_sessions CASCADE;
DROP TABLE IF EXISTS admins CASCADE;
DROP TABLE IF EXISTS submissions CASCADE;
DROP TABLE IF EXISTS materials CASCADE;
DROP TABLE IF EXISTS schedules CASCADE;
DROP TABLE IF EXISTS grades CASCADE;
DROP TABLE IF EXISTS quiz_answers CASCADE;
DROP TABLE IF EXISTS quiz_attempts CASCADE;
DROP TABLE IF EXISTS question_options CASCADE;
DROP TABLE IF EXISTS quiz_questions CASCADE;
DROP TABLE IF EXISTS quizzes CASCADE;
DROP TABLE IF EXISTS exam_attempts CASCADE;
DROP TABLE IF EXISTS exams CASCADE;
DROP TABLE IF EXISTS announcements CASCADE;
DROP TABLE IF EXISTS course_enrollments CASCADE;
DROP TABLE IF EXISTS assignments CASCADE;
DROP TABLE IF EXISTS courses CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- 1. Tabel users
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    gelar VARCHAR(100) DEFAULT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL CHECK (role IN ('student', 'lecturer', 'admin')),
    nim VARCHAR(20) DEFAULT NULL,
    nik VARCHAR(20) DEFAULT NULL,
    gender VARCHAR(20) DEFAULT NULL CHECK (gender IN ('Laki-laki', 'Perempuan')),
    study_program VARCHAR(100) DEFAULT NULL,
    jabatan_akademik VARCHAR(100) DEFAULT NULL,
    bidang_keahlian VARCHAR(255) DEFAULT NULL,
    status_kepegawaian VARCHAR(100) DEFAULT NULL,
    religion VARCHAR(50) DEFAULT NULL,
    nationality VARCHAR(100) DEFAULT 'Indonesia',
    place_of_birth VARCHAR(100) DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    address TEXT DEFAULT NULL,
    phone_number VARCHAR(20) DEFAULT NULL,
    ruang_kerja VARCHAR(100) DEFAULT NULL,
    jam_konsultasi VARCHAR(255) DEFAULT NULL,
    previous_school VARCHAR(255) DEFAULT NULL,
    nisn VARCHAR(50) DEFAULT NULL,
    school_city VARCHAR(100) DEFAULT NULL,
    profile_picture_url VARCHAR(255) DEFAULT 'default_profile.svg',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel courses
CREATE TABLE courses (
    course_id SERIAL PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    course_code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    credits INT NOT NULL DEFAULT 3,
    lecturer_id INT DEFAULT NULL REFERENCES users(user_id) ON DELETE SET NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabel course_enrollments
CREATE TABLE course_enrollments (
    enrollment_id SERIAL PRIMARY KEY,
    student_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    course_id INT NOT NULL REFERENCES courses(course_id) ON DELETE CASCADE,
    enrollment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (student_id, course_id)
);

-- 4. Tabel announcements
CREATE TABLE announcements (
    announcement_id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    published_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    lecturer_id INT DEFAULT NULL REFERENCES users(user_id) ON DELETE SET NULL,
    course_id INT DEFAULT NULL REFERENCES courses(course_id) ON DELETE CASCADE
);

-- 5. Tabel assignments
CREATE TABLE assignments (
    assignment_id SERIAL PRIMARY KEY,
    course_id INT NOT NULL REFERENCES courses(course_id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    due_date TIMESTAMP NOT NULL,
    max_grade DECIMAL(5,2) DEFAULT 100.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    file_path VARCHAR(255) DEFAULT NULL,
    file_type VARCHAR(50) DEFAULT NULL
);

-- 6. Tabel submissions
CREATE TABLE submissions (
    submission_id SERIAL PRIMARY KEY,
    assignment_id INT NOT NULL REFERENCES assignments(assignment_id) ON DELETE CASCADE,
    student_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    submission_file_path VARCHAR(255) DEFAULT NULL,
    submission_text TEXT DEFAULT NULL,
    submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    grade DECIMAL(5,2) DEFAULT NULL,
    feedback TEXT DEFAULT NULL
);

-- 7. Tabel quizzes
CREATE TABLE quizzes (
    quiz_id SERIAL PRIMARY KEY,
    course_id INT NOT NULL REFERENCES courses(course_id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    duration_minutes INT NOT NULL,
    total_questions INT NOT NULL,
    passing_score DECIMAL(5,2) NOT NULL DEFAULT 70.00,
    start_date TIMESTAMP DEFAULT NULL,
    end_date TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 8. Tabel quiz_questions
CREATE TABLE quiz_questions (
    question_id SERIAL PRIMARY KEY,
    quiz_id INT NOT NULL REFERENCES quizzes(quiz_id) ON DELETE CASCADE,
    question_text TEXT NOT NULL,
    question_formula TEXT DEFAULT NULL,
    question_type VARCHAR(20) NOT NULL CHECK (question_type IN ('multiple_choice', 'essay', 'code')),
    explanation TEXT DEFAULT NULL
);

-- 9. Tabel question_options
CREATE TABLE question_options (
    option_id SERIAL PRIMARY KEY,
    question_id INT NOT NULL REFERENCES quiz_questions(question_id) ON DELETE CASCADE,
    option_text TEXT NOT NULL,
    is_correct BOOLEAN NOT NULL DEFAULT FALSE
);

-- 10. Tabel quiz_attempts
CREATE TABLE quiz_attempts (
    attempt_id SERIAL PRIMARY KEY,
    quiz_id INT NOT NULL REFERENCES quizzes(quiz_id) ON DELETE CASCADE,
    student_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    start_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP DEFAULT NULL,
    score DECIMAL(5,2) DEFAULT NULL,
    is_completed BOOLEAN NOT NULL DEFAULT FALSE
);

-- 11. Tabel quiz_answers
CREATE TABLE quiz_answers (
    answer_id SERIAL PRIMARY KEY,
    attempt_id INT NOT NULL REFERENCES quiz_attempts(attempt_id) ON DELETE CASCADE,
    question_id INT NOT NULL REFERENCES quiz_questions(question_id) ON DELETE CASCADE,
    selected_option_id INT DEFAULT NULL REFERENCES question_options(option_id) ON DELETE SET NULL,
    essay_answer TEXT DEFAULT NULL,
    is_correct BOOLEAN DEFAULT NULL
);

-- 12. Tabel exams
CREATE TABLE exams (
    exam_id SERIAL PRIMARY KEY,
    course_id INT DEFAULT NULL REFERENCES courses(course_id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    exam_type VARCHAR(10) NOT NULL CHECK (exam_type IN ('UTS', 'UAS', 'Quiz')),
    exam_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(50) DEFAULT NULL,
    is_online BOOLEAN NOT NULL DEFAULT FALSE,
    online_link VARCHAR(255) DEFAULT NULL,
    duration_minutes INT NOT NULL,
    total_questions INT DEFAULT NULL,
    exam_status VARCHAR(20) DEFAULT 'Scheduled' CHECK (exam_status IN ('Scheduled', 'Active', 'Completed', 'Canceled')),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    quiz_id INT DEFAULT NULL REFERENCES quizzes(quiz_id) ON DELETE SET NULL
);

-- 13. Tabel exam_attempts
CREATE TABLE exam_attempts (
    attempt_id SERIAL PRIMARY KEY,
    exam_id INT NOT NULL REFERENCES exams(exam_id) ON DELETE CASCADE,
    student_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    start_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP DEFAULT NULL,
    score DECIMAL(5,2) DEFAULT NULL,
    is_completed BOOLEAN NOT NULL DEFAULT FALSE,
    UNIQUE (exam_id, student_id)
);

-- 14. Tabel failed_login_attempts
CREATE TABLE failed_login_attempts (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT DEFAULT NULL,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 15. Tabel feedback
CREATE TABLE feedback (
    feedback_id SERIAL PRIMARY KEY,
    name VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 16. Tabel grades
CREATE TABLE grades (
    grade_id SERIAL PRIMARY KEY,
    student_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    course_id INT DEFAULT NULL REFERENCES courses(course_id) ON DELETE SET NULL,
    item_id INT DEFAULT NULL,
    grade_value DECIMAL(5,2) DEFAULT NULL,
    grade_letter VARCHAR(5) NOT NULL,
    grade_points DECIMAL(3,2) NOT NULL,
    feedback TEXT DEFAULT NULL,
    grade_type VARCHAR(20) NOT NULL CHECK (grade_type IN ('Assignment', 'Quiz', 'UTS', 'UAS', 'Final Course', 'Final GPA', 'Partisipasi')),
    semester VARCHAR(20) DEFAULT NULL,
    academic_year VARCHAR(10) DEFAULT NULL,
    graded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    graded_by INT NOT NULL
);

-- 17. Tabel materials
CREATE TABLE materials (
    material_id SERIAL PRIMARY KEY,
    course_id INT NOT NULL REFERENCES courses(course_id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    file_type VARCHAR(50) DEFAULT NULL,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 18. Tabel schedules
CREATE TABLE schedules (
    schedule_id SERIAL PRIMARY KEY,
    course_id INT NOT NULL REFERENCES courses(course_id) ON DELETE CASCADE,
    lecturer_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    day_of_week VARCHAR(15) NOT NULL CHECK (day_of_week IN ('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')),
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(50) DEFAULT NULL,
    class_type VARCHAR(15) NOT NULL CHECK (class_type IN ('Teori', 'Praktikum')),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 19. Tabel system_settings
CREATE TABLE system_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 20. Tabel Admin (Tambahan baru untuk memenuhi VIEW stats)
CREATE TABLE admins (
    admin_id SERIAL PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admin_sessions (
    session_id SERIAL PRIMARY KEY,
    admin_id INT REFERENCES admins(admin_id) ON DELETE CASCADE,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    is_active INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL
);

CREATE TABLE admin_logs (
    log_id SERIAL PRIMARY KEY,
    admin_id INT REFERENCES admins(admin_id) ON DELETE SET NULL,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 21. View admin_dashboard_stats
CREATE OR REPLACE VIEW admin_dashboard_stats AS
SELECT 
    (SELECT COUNT(*) FROM admins WHERE status = 'active') AS active_admins,
    (SELECT COUNT(*) FROM admin_sessions WHERE is_active = 1 AND expires_at > CURRENT_TIMESTAMP) AS active_sessions,
    (SELECT COUNT(*) FROM admin_logs WHERE CAST(created_at AS DATE) = CURRENT_DATE) AS today_activities,
    (SELECT COUNT(*) FROM failed_login_attempts WHERE CAST(attempted_at AS DATE) = CURRENT_DATE) AS today_failed_attempts;

-- Triggers untuk auto-update updated_at (PostgreSQL membutuhkan trigger function untuk ini)
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_courses_updated_at BEFORE UPDATE ON courses FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_assignments_updated_at BEFORE UPDATE ON assignments FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_quizzes_updated_at BEFORE UPDATE ON quizzes FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_exams_updated_at BEFORE UPDATE ON exams FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_schedules_updated_at BEFORE UPDATE ON schedules FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_system_settings_updated_at BEFORE UPDATE ON system_settings FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_admins_updated_at BEFORE UPDATE ON admins FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

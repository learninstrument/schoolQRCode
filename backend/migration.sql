-- =====================================================
-- NBTE Certificate System — Database Migration
-- Run this in phpMyAdmin or MySQL terminal
-- =====================================================

-- 1. Create the courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Add course_id and level columns to certificates table
ALTER TABLE certificates ADD COLUMN course_id INT DEFAULT NULL AFTER reg_number;
ALTER TABLE certificates ADD COLUMN level VARCHAR(50) DEFAULT NULL AFTER course_id;

-- 3. (Optional) Insert some sample courses
-- INSERT INTO courses (course_name) VALUES ('LEVEL 3 IN MASONRY');
-- INSERT INTO courses (course_name) VALUES ('LEVEL 2 IN PLUMBING');
-- INSERT INTO courses (course_name) VALUES ('LEVEL 3 IN ELECTRICAL INSTALLATION');

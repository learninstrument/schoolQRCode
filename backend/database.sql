-- ============================================
-- Certificate QR Code Verification System
-- Database Setup Script
-- ============================================

-- Create the database
CREATE DATABASE IF NOT EXISTS cert_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Select the database
USE cert_system;

-- Drop existing table if schema has changed (development only)
DROP TABLE IF EXISTS certificates;

-- Create the certificates table
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    photo VARCHAR(500) NOT NULL,
    reg_number VARCHAR(100) NOT NULL,
    date_of_award DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Drop existing admins table if schema has changed (development only)
DROP TABLE IF EXISTS admins;

-- Create the admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: password123)
-- Hash generated via password_hash('password123', PASSWORD_DEFAULT)
INSERT INTO admins (username, password_hash) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');


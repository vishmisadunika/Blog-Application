-- Database Schema for Blog Application
-- IN2120 Web Programming - University of Moratuwa

-- Create database (run this separately if needed)
-- CREATE DATABASE IF NOT EXISTS blog_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE blog_app;

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS blogPost;
DROP TABLE IF EXISTS user;

-- User table
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog post table
CREATE TABLE blogPost (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Insert a sample admin user (password: admin123)
-- Password hash for 'admin123' generated with password_hash()
-- INSERT INTO user (username, email, password, role) VALUES
-- ('admin', 'admin@example.com', '$2y$10$YourGeneratedHashHere', 'admin');

-- Note: To generate password hashes, use PHP:
-- echo password_hash('yourpassword', PASSWORD_DEFAULT);

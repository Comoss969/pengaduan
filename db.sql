-- Database creation script for pengaduan
CREATE DATABASE IF NOT EXISTS pengaduan;
USE pengaduan;

-- Table for users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    profile_picture VARCHAR(255) DEFAULT NULL
);

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO users (username, password, role) VALUES ('admin', '$2y$10$uw2eutvzpP/0zSYgWT.OJObEp6WKusPFb2ohi1Ofv80B5ha5IITci', 'admin');

-- Table for posts
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    nama VARCHAR(100) NULL,
    keluhan TEXT NOT NULL,
    censored_keluhan TEXT NULL,
    foto VARCHAR(255) NULL,
    tanggal_post DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_anonim BOOLEAN DEFAULT FALSE,
    profanity_count INT DEFAULT 0
);

-- Table for comments
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    komentar TEXT NOT NULL,
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_admin BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table for profanity logs
CREATE TABLE IF NOT EXISTS profanity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    content_type ENUM('post', 'comment') NOT NULL,
    original_text TEXT NOT NULL,
    found_words TEXT NOT NULL,
    detected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    moderated BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table for activity logs (for admin actions like deleting comments)
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    target_type VARCHAR(50) NOT NULL,
    target_id INT NOT NULL,
    details TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

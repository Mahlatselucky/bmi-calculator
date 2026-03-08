-- ============================================
-- BMI Calculator - Full Database Schema
-- Run this in phpMyAdmin > SQL tab
-- ============================================

CREATE DATABASE IF NOT EXISTS bmi_calculator;
USE bmi_calculator;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- BMI Records table (linked to users)
CREATE TABLE IF NOT EXISTS bmi_records (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    age        INT NOT NULL,
    weight     DECIMAL(6,2) NOT NULL,
    height     DECIMAL(6,2) NOT NULL,
    unit       ENUM('metric','imperial') DEFAULT 'metric',
    bmi        DECIMAL(5,2) NOT NULL,
    category   VARCHAR(30)  NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

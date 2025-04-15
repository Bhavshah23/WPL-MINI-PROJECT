-- Create and use the database
CREATE DATABASE IF NOT EXISTS jainz_matrimony;
USE jainz_matrimony;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    gotra VARCHAR(50) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    verification_token VARCHAR(64),
    email_verified TINYINT(1) DEFAULT 0,
    last_login DATETIME,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'inactive',
    profile_picture VARCHAR(255),
    bio TEXT,
    occupation VARCHAR(100),
    income_range VARCHAR(50),
    education VARCHAR(100),
    height DECIMAL(4,2),
    marital_status ENUM('never_married', 'divorced', 'widowed') DEFAULT 'never_married',
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Password reset table
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (email),
    INDEX (token)
);

-- User preferences table
CREATE TABLE user_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    min_age INT DEFAULT 18,
    max_age INT DEFAULT 40,
    preferred_gotra VARCHAR(255),
    preferred_location VARCHAR(100),
    preferred_education VARCHAR(100),
    min_height DECIMAL(4,2),
    max_height DECIMAL(4,2),
    preferred_marital_status VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_gotra ON users(gotra);
CREATE INDEX idx_users_gender ON users(gender);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_verification ON users(verification_token);

-- Insert common Jain gotras
CREATE TABLE gotras (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO gotras (name) VALUES 
    ('Agarwal'),
    ('Oswal'),
    ('Khandelwal'),
    ('Porwal'),
    ('Bangad'),
    ('Bohra'),
    ('Kothari'),
    ('Sanghvi'),
    ('Jain'),
    ('Sethia'),
    ('Singhi'),
    ('Bhandari'),
    ('Lodha'),
    ('Patni'),
    ('Dugar'),
    ('Bafna');

-- Create audit log table
CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create user connections table
CREATE TABLE connections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'blocked') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================================
-- Create Secure Database
-- =========================================

CREATE DATABASE IF NOT EXISTS secured_bank_demo
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE secured_bank_demo;

-- =========================================
-- Drop Existing Tables
-- =========================================

DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS feedbacks;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS users;

-- =========================================
-- Users
-- =========================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    balance DECIMAL(12,2) NOT NULL DEFAULT 0,
    background VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- Transactions
-- =========================================

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_user INT NULL,
    to_user INT NULL,
    amount DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_from_user (from_user),
    INDEX idx_to_user (to_user),

    CONSTRAINT fk_transactions_from
        FOREIGN KEY (from_user)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_transactions_to
        FOREIGN KEY (to_user)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- =========================================
-- Feedback
-- =========================================

CREATE TABLE feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_feedback_user (user_id),

    CONSTRAINT fk_feedback_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- =========================================
-- Login Attempts (Rate Limiting)
-- =========================================

CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,

    username VARCHAR(100) NOT NULL,

    ip_address VARCHAR(45) NOT NULL,

    attempts INT NOT NULL DEFAULT 1,

    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_username_ip (username, ip_address),

    INDEX idx_username (username),
    INDEX idx_ip (ip_address)
);

CREATE TABLE security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,

    username VARCHAR(100) NULL,

    ip_address VARCHAR(45) NOT NULL,

    event VARCHAR(50) NOT NULL,

    details TEXT,

    user_agent VARCHAR(255),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_username (username),
    INDEX idx_event (event),
    INDEX idx_created_at (created_at)
);
-- =========================================
-- Secure Demo Users
-- =========================================

INSERT INTO users
(id, username, password, role, balance, created_at)
VALUES
(1, 'admin',
'$2y$10$ug7iTxOzsSHzI2mPwjg/G.5y8/EInEl8y.la.hUJ3VqfrjmxxajF6',
'admin',
103001.00,
'2026-02-15 22:40:36'),

(2, 'alice',
'$2y$10$g0fb2v4.x0ly5USE0iT8m./KYSXE4GI9.TftZu6zIDizoFi00j0Tm',
'user',
2035.00,
'2026-02-15 22:40:36'),

(3, 'bob',
'$2y$10$PcMubcsTwZQxbH/Z9P7zfOxJYu3uuaHYR9.7g5Ob.tcbvd9zrgML.',
'user',
3000.00,
'2026-02-15 22:40:36');

-- =========================================
-- Least Privilege Database User
-- =========================================

CREATE USER IF NOT EXISTS 'bank_user'@'localhost'
IDENTIFIED BY 'StrongPassword123!';

GRANT
    SELECT,
    INSERT,
    UPDATE,
    DELETE
ON secured_bank_demo.*
TO 'bank_user'@'localhost';

FLUSH PRIVILEGES;

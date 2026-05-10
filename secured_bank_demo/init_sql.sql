-- Create secure database
CREATE DATABASE IF NOT EXISTS secured_bank_demo
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE secured_bank_demo;

DROP TABLE IF EXISTS feedbacks;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS users;

-- Users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  balance DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  background VARCHAR(255) DEFAULT NULL
);

-- Transactions table
CREATE TABLE transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  from_user INT,
  to_user INT,
  amount DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (from_user) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (to_user) REFERENCES users(id) ON DELETE SET NULL
);

-- Feedback table
CREATE TABLE feedbacks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =========================================
-- Secure Demo Users (passwords must be hashed)
-- Example hashes (replace with real password_hash values)
-- =========================================

INSERT INTO users (id, username, password, role, balance, created_at)
VALUES
(1, 'admin', '$2y$10$ug7iTxOzsSHzI2mPwjg/G.5y8/EInEl8y.la.hUJ3VqfrjmxxajF6', 'admin', 103001.00, '2026-02-15 22:40:36'),
(2, 'alice', '$2y$10$g0fb2v4.x0ly5USE0iT8m./KYSXE4GI9.TftZu6zIDizoFi00j0Tm', 'user', 2035.00, '2026-02-15 22:40:36'),
(3, 'bob', '$2y$10$PcMubcsTwZQxbH/Z9P7zfOxJYu3uuaHYR9.7g5Ob.tcbvd9zrgML.', 'user', 3000.00, '2026-02-15 22:40:36');

-- =========================================
-- Secure Database User (Least Privilege)
-- =========================================

CREATE USER IF NOT EXISTS 'bank_user'@'localhost'
IDENTIFIED BY 'StrongPassword123!';

GRANT SELECT, INSERT, UPDATE, DELETE ON secured_bank_demo.* TO 'bank_user'@'localhost';

FLUSH PRIVILEGES;
CREATE DATABASE IF NOT EXISTS bank_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bank_demo;

DROP TABLE IF EXISTS feedbacks;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  balance DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  from_user INT,
  to_user INT,
  amount DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (from_user) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (to_user) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE feedbacks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

ALTER TABLE users
ADD COLUMN background VARCHAR(255) DEFAULT NULL;



INSERT INTO users (id, username, password, role, balance, created_at, background) VALUES
(1, 'admin', 'admin@1', 'admin', 103001.00, '2026-02-15 22:40:36', NULL),
(2, 'alice', 'alice1', 'user', 2035.00, '2026-02-15 22:40:36', NULL),
(3, 'bob', 'bob2', 'user', 3000.00, '2026-02-15 22:40:36', NULL),
(4, 'charlie', 'charlie3', 'user', 4200.00, '2026-02-15 22:40:36', NULL),
(5, 'david', 'david4', 'user', 3800.00, '2026-02-15 22:40:36', NULL),
(6, 'emma', 'emma5', 'user', 6123.00, '2026-02-15 22:40:36', NULL),
(7, 'frank', 'frank6', 'user', 2750.00, '2026-02-15 22:40:36', NULL),
(8, 'grace', 'grace7', 'user', 4934.00, '2026-02-15 22:40:36', NULL),
(9, 'henry', 'henry8', 'user', 5300.00, '2026-02-15 22:40:36', NULL),
(10, 'irene', 'irene9', 'user', 4600.00, '2026-02-15 22:40:36', NULL),
(11, 'jack', 'jack10', 'user', 3500.00, '2026-02-15 22:40:36', NULL),
(12, 'karen', 'karen11', 'user', 7334.00, '2026-02-15 22:40:36', NULL),
(13, 'leo', 'leo12', 'user', 2900.00, '2026-02-15 22:40:36', NULL),
(14, 'mia', 'mia13', 'user', 6400.00, '2026-02-15 22:40:36', NULL),
(15, 'nina', 'nina14', 'user', 4100.00, '2026-02-15 22:40:36', NULL),
(16, 'oliver', 'oliver15', 'user', 5800.00, '2026-02-15 22:40:36', NULL),
(17, 'paul', 'paul16', 'user', 3300.00, '2026-02-15 22:40:36', NULL),
(18, 'quinn', 'quinn17', 'user', 4500.00, '2026-02-15 22:40:36', NULL),
(19, 'rachel', 'rachel18', 'user', 5200.00, '2026-02-15 22:40:36', NULL),
(20, 'sam', 'sam19', 'user', 4700.00, '2026-02-15 22:40:36', NULL),
(21, 'tina', 'tina20', 'user', 4028.00, '2026-02-15 22:40:36', NULL),
(22, 'uma', 'uma21', 'user', 4946.00, '2026-02-15 22:40:36', NULL),
(23, 'victor', 'victor22', 'user', 6800.00, '2026-02-15 22:40:36', NULL),
(24, 'wendy', 'wendy23', 'user', 5100.00, '2026-02-15 22:40:36', NULL),
(25, 'xavier', 'xavier24', 'user', 3000.00, '2026-02-15 22:40:36', NULL),
(26, 'yara', 'yara25', 'user', 4400.00, '2026-02-15 22:40:36', NULL),

-- attacker account
(28, 'Hacker', 'hacker@defLJYG', 'user', 1233.00, '2026-02-16 09:33:36', NULL);

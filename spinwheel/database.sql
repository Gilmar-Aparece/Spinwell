-- Spin the Wheel Game Database
-- Run this SQL in your MySQL/phpMyAdmin

CREATE DATABASE IF NOT EXISTS spinwheel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE spinwheel_db;

CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS spin_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    prize_label VARCHAR(100) NOT NULL,
    prize_amount DECIMAL(10,2) DEFAULT 0.00,
    spin_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
);

-- Updated payout_requests with bank/ewallet fields
CREATE TABLE IF NOT EXISTS payout_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    player_name VARCHAR(100) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,       -- e.g. GCash, Maya, BDO, BPI
    account_name VARCHAR(150) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    admin_note TEXT NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default: admin / password
INSERT INTO admin_users (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

CREATE OR REPLACE VIEW leaderboard AS
SELECT
    p.id, p.name, p.score,
    COUNT(s.id) AS total_spins,
    SUM(s.prize_amount) AS total_won,
    p.created_at
FROM players p
LEFT JOIN spin_history s ON p.id = s.player_id
GROUP BY p.id
ORDER BY p.score DESC;

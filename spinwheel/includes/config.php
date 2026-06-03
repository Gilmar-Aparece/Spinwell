<?php
// ============================================
// DATABASE CONFIGURATION
// Edit these values to match your server
// ============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Your MySQL username
define('DB_PASS', '');            // Your MySQL password
define('DB_NAME', 'spinwheel_db');

// Game Settings
define('MAX_SPINS_PER_DAY', 3);           // Max spins per player per day
define('MIN_PAYOUT_AMOUNT', 50);          // Minimum PHP to request payout
define('GCASH_ADMIN_NUMBER', '09XX-XXX-XXXX'); // Your GCash number (shown to players)

// ============================================
// DO NOT EDIT BELOW THIS LINE
// ============================================
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

session_start();

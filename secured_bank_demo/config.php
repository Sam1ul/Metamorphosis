<?php
// ======================
// Secure Session Settings
// ======================
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Strict');

session_start();

// Regenerate session ID to prevent fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// ======================
// Security Headers
// ======================
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: no-referrer");
header("Content-Security-Policy:
    default-src 'self';
    script-src 'self' https://cdn.tailwindcss.com;
    style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com;
");

// ======================
// Database Configuration
// ======================

// ⚠ In real-world: use environment variables
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_NAME = getenv('DB_NAME') ?: 'secured_bank_demo';
$DB_USER = getenv('DB_USER') ?: 'bank_user';
$DB_PASS = getenv('DB_PASS') ?: 'StrongPassword123!';

function get_pdo(){
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;

    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

    try {
        return new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    } catch (PDOException $e) {
        // Log error instead of displaying it
        error_log("Database connection failed: " . $e->getMessage());
        die("Internal Server Error");
    }
}

// ======================
// Authentication Helpers
// ======================
function current_user(){
    return $_SESSION['user'] ?? null;
}

function require_login(){
    if(!current_user()){
        header('Location: login.php');
        exit;
    }
}

function require_admin(){
    $u = current_user();
    if(!$u || $u['role'] !== 'admin'){
        http_response_code(403);
        exit("403 Forbidden");
    }
}
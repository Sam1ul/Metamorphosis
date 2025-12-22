<?php
session_start();
$DB_HOST = '127.0.0.1';
$DB_NAME = 'bank_demo';
$DB_USER = 'root';
$DB_PASS = '';

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
        die("DB connection failed: " . htmlspecialchars($e->getMessage()));
    }
}
function current_user(){ return $_SESSION['user'] ?? null; }
function require_login(){ if(!current_user()){ header('Location: login.php'); exit; } }
function require_admin(){ $u=current_user(); if(!$u || $u['role']!=='admin'){ http_response_code(403); echo "403 Forbidden"; exit; } }

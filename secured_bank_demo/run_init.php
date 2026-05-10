<?php
require_once 'functions.php';

// Only allow POST (not GET)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method not allowed.");
}

validate_csrf();

// Optional: allow only admin
$u = current_user();
if (!$u || $u['role'] !== 'admin') {
    http_response_code(403);
    exit("Forbidden");
}

try {
    // Create DB using secure credentials (no root)
    $pdo = get_pdo();

    $schema = file_get_contents(__DIR__ . '/init_sql.sql');

    $statements = array_filter(array_map('trim', explode(';', $schema)));

    foreach ($statements as $s) {
        if ($s === '') continue;
        $pdo->exec($s . ';');
    }

    flash("Database initialized.");
    header('Location: login.php');
    exit;

} catch (Exception $e) {
    error_log($e->getMessage());
    die("Changed Permission: Now on you are not allowed to Reset Database. Contact Database Admin To Reset Database.");
}
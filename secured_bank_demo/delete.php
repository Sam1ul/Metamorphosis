<?php
require_once 'functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method not allowed.");
}

validate_csrf();

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    $pdo = get_pdo();

    // Use prepared statement (safe)
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    flash("User deleted.");
}

header("Location: admin.php");
exit;
<?php
require_once 'functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method not allowed.");
}

validate_csrf();

$pdo = get_pdo();

// Secure query
$stmt = $pdo->prepare("
    SELECT id, username, role, balance
    FROM users
    ORDER BY id
");

$stmt->execute();
$rows = $stmt->fetchAll();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=\"users.csv\"');

$output = fopen('php://output', 'w');

// header row
fputcsv($output, ['id', 'username', 'role', 'balance']);

foreach ($rows as $r) {
    fputcsv($output, [
        $r['id'],
        $r['username'],
        $r['role'],
        $r['balance']
    ]);
}

fclose($output);
exit;
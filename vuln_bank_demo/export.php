<?php
require_once 'functions.php';
$pdo = get_pdo();

// intentionally open to anyone
$sql = "SELECT id, username, role, balance FROM users ORDER BY id";
$rows = $pdo->query($sql)->fetchAll();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="users.csv"');

$output = fopen('php://output', 'w');

// header row
fputcsv($output, ['id', 'username', 'role', 'balance']);

foreach ($rows as $r) {
    fputcsv($output, $r);
}

fclose($output);
exit;

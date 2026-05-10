<?php
require_once 'functions.php';
$pdo = get_pdo();

// intentionally vulnerable: no validation, direct use of GET
$id = $_GET['id'] ?? '';

if ($id !== '') {
    $sql = "DELETE FROM users WHERE id = $id";
    $pdo->exec($sql);
}

// redirect back
header("Location: admin.php");
exit;

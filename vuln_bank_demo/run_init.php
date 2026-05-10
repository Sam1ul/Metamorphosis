<?php
// run_init.php - initialize DB for demo (RUN LOCALLY ONCE), then delete this file!
require_once 'config.php';

try {
    // connect as root to create DB
    $pdoRoot = new PDO("mysql:host=127.0.0.1;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS bank_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

    // now connect normally
    $pdo = get_pdo();

    // run full init script (schema + data)
    $schema = file_get_contents(__DIR__ . '/init_sql.sql');
    $statements = array_filter(array_map('trim', explode(';', $schema)));

    foreach ($statements as $s) {
        if ($s === '') continue;
        $pdo->exec($s . ';');
    }

    header('Location: login.php');
    exit;

} catch(Exception $e){
    echo "<pre>Error: " . htmlspecialchars($e->getMessage()) . "</pre>";
}

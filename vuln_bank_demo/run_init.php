<?php
// run_init.php - initialize DB for demo (RUN LOCALLY ONCE), then delete this file!
require_once 'config.php';
try {
    $pdoRoot = new PDO("mysql:host=127.0.0.1;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS bank_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo = get_pdo();
    $pdo->exec("DROP TABLE IF EXISTS feedbacks;");
    $pdo->exec("DROP TABLE IF EXISTS transactions;");
    $pdo->exec("DROP TABLE IF EXISTS users;");
    $schema = file_get_contents(__DIR__ . '/init_sql.sql');
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    foreach ($statements as $s) {
        if ($s === '') continue;
        $pdo->exec($s . ';');
    }
    $users = [
        ['admin','admin123','admin',10000.00],
        ['alice','alice123','user',5000.00],
        ['bob','bob123','user',3000.00]
    ];
    $stmt = $pdo->prepare("INSERT INTO users (username,password,role,balance) VALUES (?, ?, ?, ?)");
    foreach($users as $u){
        $stmt->execute([$u[0], $u[1], $u[2], $u[3]]);
    }
    echo "<h3>Bank DB initialized.</h3>";
    echo "<p>Users created: <strong>admin / alice / bob</strong></p>";
    echo "<p>Passwords (plaintext for demo): <strong>admin123 / alice123 / bob123</strong></p>";
    echo "<p><strong>IMPORTANT:</strong> Delete run_init.php after use.</p>";
} catch(Exception $e){
    echo "<pre>Error: " . htmlspecialchars($e->getMessage()) . "</pre>";
}

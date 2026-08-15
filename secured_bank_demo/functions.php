<?php
require_once 'config.php';

/* ============================================================
 *  Flash Messaging
 = *=========================================================== */

function flash($msg = null)
{
    if ($msg === null) {
        $message = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $message;
    }

    $_SESSION['flash'] = substr($msg, 0, 300);
}


/* ============================================================
 *  CSRF Protection
 = *=========================================================== */

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');

    return "<input type='hidden' name='csrf_token' value='{$token}'>";
}

function validate_csrf()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $submitted = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';

    if (!$stored || !hash_equals($stored, $submitted)) {

        security_log(
            'INVALID_CSRF',
            '',
            'CSRF token validation failed.'
        );

        http_response_code(400);
        exit('Invalid CSRF token.');
    }

    // Rotate token
    unset($_SESSION['csrf_token']);
}


/* ============================================================
 *  Database Helper
 = *=========================================================== */

function db()
{
    return get_pdo();
}


/* ============================================================
 *  Output Escaping
 = *=========================================================== */

function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}


/* ============================================================
 *  Security Logging
 = *=========================================================== */

function security_log($event, $username = '', $details = '')
{
    $pdo = db();

    $stmt = $pdo->prepare("
    INSERT INTO security_logs
    (username, ip_address, event, details, user_agent)
    VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $username ?: null,
        $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        $event,
        $details,
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}


/* ============================================================
 *  Rate Limiting
 = *=========================================================== */

function check_rate_limit(PDO $pdo, $username)
{
    $ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $pdo->prepare("
    SELECT attempts, last_attempt
    FROM login_attempts
    WHERE username = ?
    AND ip_address = ?
    LIMIT 1
    ");

    $stmt->execute([$username, $ip]);

    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        return;
    }

    // Reset counter after 15 minutes
    if (strtotime($record['last_attempt']) <= time() - 900) {

        $stmt = $pdo->prepare("
        DELETE FROM login_attempts
        WHERE username = ?
        AND ip_address = ?
        ");

        $stmt->execute([$username, $ip]);
        return;
    }

    if ($record['attempts'] >= 5) {

        security_log(
            'RATE_LIMIT',
            $username,
            'Too many failed login attempts.'
        );

        flash("Too many failed login attempts. Please try again in 15 minutes.");

        header('Location: login.php');
        exit;
    }
}


function register_failed_login(PDO $pdo, $username)
{
    $ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $pdo->prepare("
    INSERT INTO login_attempts
    (username, ip_address, attempts)
    VALUES (?, ?, 1)

    ON DUPLICATE KEY UPDATE
    attempts = attempts + 1,
    last_attempt = CURRENT_TIMESTAMP
    ");

    $stmt->execute([$username, $ip]);

    security_log(
        'FAILED_LOGIN',
        $username,
        'Invalid username or password.'
    );
}


function clear_failed_logins(PDO $pdo, $username)
{
    $ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $pdo->prepare("
    DELETE FROM login_attempts
    WHERE username = ?
    AND ip_address = ?
    ");

    $stmt->execute([$username, $ip]);
}


/* ============================================================
 *  Successful Login Logging
 = *=========================================================== */

function log_successful_login($username)
{
    security_log(
        'SUCCESSFUL_LOGIN',
        $username,
        'User authenticated successfully.'
    );
}

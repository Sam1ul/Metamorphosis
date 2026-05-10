<?php
require_once 'config.php';

/* ==========================
   Flash Messaging
========================== */
function flash($msg = null) {
    if ($msg === null) {
        $m = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $m;
    }

    // Limit flash length to prevent abuse
    $_SESSION['flash'] = substr($msg, 0, 300);
}


/* ==========================
   CSRF Protection
========================== */

// Generate token
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Hidden input field
function csrf_field() {
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return "<input type='hidden' name='csrf_token' value='{$t}'>";
}

// Validate and rotate token
function validate_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $sent = $_POST['csrf_token'] ?? '';
        $stored = $_SESSION['csrf_token'] ?? '';

        if (!$stored || !hash_equals($stored, $sent)) {
            http_response_code(400);
            exit("Invalid CSRF token.");
        }

        // Rotate token after successful validation
        unset($_SESSION['csrf_token']);
    }
}


/* ==========================
   Secure Database Access
========================== */

// Always use get_pdo() from config.php
function db() {
    return get_pdo();
}


/* ==========================
   Output Escaping Helper
========================== */

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
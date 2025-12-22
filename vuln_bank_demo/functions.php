<?php
require_once 'config.php';
function flash($msg=null){
    if ($msg === null) { $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m; }
    $_SESSION['flash'] = $msg;
}
function csrf_token(){ if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(20)); } return $_SESSION['csrf_token']; }
function csrf_field(){ $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); return "<input type='hidden' name='csrf_token' value='$t'>"; }
function validate_csrf(){ if ($_SERVER['REQUEST_METHOD'] === 'POST') { $sent = $_POST['csrf_token'] ?? ''; if (!hash_equals($_SESSION['csrf_token'] ?? '', $sent)) { http_response_code(400); die("Invalid CSRF token"); } } }

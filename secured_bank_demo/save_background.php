<?php
require_once 'functions.php';
$u = current_user();
if (!$u) {
    http_response_code(403);
    exit("Login required.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method not allowed.");
}

validate_csrf();

$url = trim($_POST['bg_url'] ?? '');

if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
    die("Invalid URL.");
}

// Allow only HTTP/HTTPS
$scheme = parse_url($url, PHP_URL_SCHEME);
if (!in_array($scheme, ['http', 'https'], true)) {
    die("Only HTTP/HTTPS allowed.");
}

// Optional: domain allowlist (recommended)
$host = parse_url($url, PHP_URL_HOST);
$allowed = [
    'example.com',
    'images.pexels.com'
];

if (!in_array($host, $allowed, true)) {
    die("Domain not allowed.");
}

// Fetch with timeout and size limit
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_MAXREDIRS, 0);

$data = curl_exec($ch);
curl_close($ch);

if ($data === false || strlen($data) > 5 * 1024 * 1024) {
    die("Failed to fetch or file too large.");
}

// Validate image
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->buffer($data);

$allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

if (!in_array($mime, $allowedMime, true)) {
    die("Content is not a supported image.");
}

// Save securely
$folder = __DIR__ . "/uploads/backgrounds";
if (!is_dir($folder)) {
    mkdir($folder, 0755, true);
}

$filename = $folder . "/user_" . $u['id'] . ".jpg";
file_put_contents($filename, $data);

// Update DB
$db = db();
$stmt = $db->prepare("
    UPDATE users
    SET background = ?
    WHERE id = ?
");

$stmt->execute([
    'uploads/backgrounds/user_' . $u['id'] . '.jpg',
    $u['id']
]);

// Remove preview if exists
$preview_file = $folder . "/preview_" . $u['id'] . ".jpg";
if (file_exists($preview_file)) {
    unlink($preview_file);
}

header("Location: dashboard.php");
exit;
?>
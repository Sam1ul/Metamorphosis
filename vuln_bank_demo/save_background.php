<?php
require_once 'functions.php';
$u = current_user();
if (!$u) die("Login required");

if (!isset($_POST['bg_url'])) die("No URL provided");

$url = $_POST['bg_url'];

// Fetch the URL again (SSRF risk!)
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($ch);
curl_close($ch);

if ($data === false) die("Failed to fetch URL");

// Check if content is an image
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->buffer($data);

if (!str_starts_with($mime, "image/")) {
    die("Cannot save: content is not an image.");
}

// Save as permanent background
$folder = __DIR__ . "/uploads/backgrounds";
if (!is_dir($folder)) mkdir($folder, 0777, true);

$filename = $folder . "/user_" . $u['id'] . ".jpg";
file_put_contents($filename, $data);

// Update database
$db = db();
$stmt = $db->prepare("UPDATE users SET background=? WHERE id=?");
$stmt->execute(['uploads/backgrounds/user_' . $u['id'] . '.jpg', $u['id']]);

// ✅ Delete the temporary preview image if it exists
$preview_file = $folder . "/preview_" . $u['id'] . ".jpg";
if (file_exists($preview_file)) {
    unlink($preview_file);
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit;
?>

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
    die("Only HTTP/HTTPS URLs allowed.");
}

// Optional domain allowlist (recommended)
$host = parse_url($url, PHP_URL_HOST);
$allowed = [
    'example.com',
    'images.pexels.com'
];

if (!in_array($host, $allowed, true)) {
    die("Domain not allowed.");
}

// Fetch with limits
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_MAXREDIRS, 0);

$data = curl_exec($ch);
$err  = curl_error($ch);
curl_close($ch);

if ($data === false || strlen($data) > 5 * 1024 * 1024) {
    die("Failed to fetch or file too large.");
}

// Detect MIME type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->buffer($data);

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Background Preview</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen font-sans">

<div class="max-w-4xl mx-auto py-10 px-4">
  <div class="bg-white shadow-lg rounded-lg p-6">

    <h1 class="text-2xl font-bold mb-4 text-blue-900">
      Preview Background Image
    </h1>

    <p class="mb-4 text-gray-700">
      <strong>URL:</strong>
      <span class="text-blue-700">
        <?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>
      </span>
    </p>

<?php
$allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

if (in_array($mime, $allowedMime, true)):
    // Save temporary preview (isolated directory)
    $folder = __DIR__ . "/uploads/backgrounds";
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    $preview_file = $folder . "/preview_" . $u['id'] . ".jpg";
    file_put_contents($preview_file, $data);
?>
    <div class="mb-4">
      <p class="font-semibold text-gray-800 mb-2">Image Preview:</p>
      <img src="uploads/backgrounds/preview_<?php echo $u['id']; ?>.jpg"
           class="border border-gray-300 rounded shadow max-w-full h-auto">
    </div>

    <form method="POST" action="save_background.php" class="mt-4">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="bg_url"
             value="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>">

      <button type="submit"
              class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">
        Save Changes
      </button>
    </form>

<?php else: ?>
    <div class="mb-4">
      <p class="font-semibold text-gray-800 mb-2">Content (not an image):</p>
      <pre class="bg-gray-100 text-gray-800 p-4 rounded overflow-auto max-h-96 border border-gray-300">
<?php echo htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
      </pre>
    </div>
<?php endif; ?>

    <a href="dashboard.php" class="inline-block mt-4 text-blue-600 hover:underline">
      Back to Dashboard
    </a>
  </div>
</div>

</body>
</html>
<?php
// Log SSRF attempt (safe logging)
file_put_contents(
    "ssrf.log",
    date('Y-m-d H:i:s') . " | " . $u['username'] . " -> " . $url . PHP_EOL,
    FILE_APPEND
);
?>
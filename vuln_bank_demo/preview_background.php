<?php
require_once 'functions.php';
$u = current_user();
if(!$u) die("Login required");

if(!isset($_POST['bg_url'])) die("No URL provided");

$url = $_POST['bg_url'];

// SSRF happens here: fetch the content server-side
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if($data === false) die("Failed to fetch URL: $err");

// Detect MIME type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->buffer($data);

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>VulnBank — Background Preview</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen font-sans">

<div class="max-w-4xl mx-auto py-10 px-4">
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4 text-blue-900">Preview Background Image</h1>

        <p class="mb-4 text-gray-700"><strong>URL fetched:</strong> <span class="text-blue-700"><?php echo htmlspecialchars($url); ?></span></p>

<?php
if(str_starts_with($mime, "image/")):
    // Save temporary preview
    $folder = __DIR__ . "/uploads/backgrounds";
    if(!is_dir($folder)) mkdir($folder, 0777, true);

    $preview_file = $folder . "/preview_" . $u['id'] . ".jpg";
    file_put_contents($preview_file, $data);
?>
        <div class="mb-4">
            <p class="font-semibold text-gray-800 mb-2">Image Preview:</p>
            <img src="uploads/backgrounds/preview_<?php echo $u['id']; ?>.jpg" 
                 class="border border-gray-300 rounded shadow max-w-full h-auto">
        </div>
        <form method="POST" action="save_background.php" class="mt-4">
            <input type="hidden" name="bg_url" value="<?php echo htmlspecialchars($url); ?>">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">Save Changes</button>
        </form>
<?php else: ?>
        <div class="mb-4">
            <p class="font-semibold text-gray-800 mb-2">Raw Content Preview (Not an Image):</p>
            <pre class="bg-gray-100 text-gray-800 p-4 rounded overflow-auto max-h-96 border border-gray-300"><?php echo htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE); ?></pre>
        </div>
<?php endif; ?>

        <a href="dashboard.php" class="inline-block mt-4 text-blue-600 hover:underline">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
<?php
// Log SSRF attempt
file_put_contents("ssrf.log", date('Y-m-d H:i:s') . " | " . $u['username'] . " -> " . $url . PHP_EOL, FILE_APPEND);
?>

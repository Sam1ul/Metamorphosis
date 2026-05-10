<?php
require_once 'functions.php';
$u = current_user();

// Default background
$bg = 'bank.jpeg'; // fallback local background

if ($u) {
    $db = db();
    $stmt = $db->prepare("SELECT background FROM users WHERE id=?");
    $stmt->execute([$u['id']]);
    $bg_db = $stmt->fetchColumn();

    if ($bg_db) {
        // Check if it's a valid URL
        if (filter_var($bg_db, FILTER_VALIDATE_URL)) {
            $bg = $bg_db; // use external URL
        } else {
            // Use local file path as stored in the DB
            $bg_path = __DIR__ . '/' . $bg_db; // server path
            if (file_exists($bg_path)) {
                $bg = $bg_db; // already includes 'uploads/'
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>VulnBank — Demo</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Nunito', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }
  </style>
</head>

<body class="relative min-h-screen flex flex-col bg-cover bg-center bg-no-repeat"
      style="background-image:url('<?php echo htmlspecialchars($bg) . "?v=" . time(); ?>');">

  <!-- overlay -->
  <div class="absolute inset-0 bg-black/30"></div>

  <!-- NAV -->
  <nav class="relative z-10 bg-white/80 backdrop-blur shadow-sm">
    <div class="max-w-6xl mx-auto px-4">
      <div class="flex items-center justify-between py-3">
        <a class="flex items-center font-bold" href="index.php">
          <span class="text-xl mr-2">🏦</span>
          <span>VulnBank</span>
        </a>

        <div class="flex items-center">
          <?php if($u): ?>
          <button onclick="document.getElementById('bgModal').classList.toggle('hidden')"
            class="mr-3 text-gray-700 hover:text-blue-900">⚙</button>
          <?php endif; ?>

          <?php if($u): ?>
          <div class="text-blue-900 mr-3 text-sm">
            Signed in as <strong>
              <?php echo htmlspecialchars($u['username']); ?>
            </strong>
          </div>
          <a class="border border-blue-900 text-blue-900 text-sm px-3 py-1 rounded hover:bg-blue-900 hover:text-white"
            href="logout.php">Logout</a>
          <?php else: ?>
          <a class="border border-blue-900 text-blue-900 text-sm px-3 py-1 rounded mr-2 hover:bg-blue-900 hover:text-white"
            href="login.php">Login</a>
          <a class="bg-blue-900 text-white text-sm px-3 py-1 rounded" href="register.php">Register</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <!-- PAGE -->
  <div class="relative z-10 flex-1 max-w-6xl mx-auto w-full my-4 px-4">

    <?php if($m = flash()): ?>
    <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded mb-4">
      <?php echo htmlspecialchars($m); ?>
    </div>
    <?php endif; ?>

    <div class="flex flex-col lg:flex-row gap-4">
      <!-- Sidebar -->
      <aside class="lg:w-1/4">
        <div class="bg-gradient-to-r from-white/80 to-black-100 shadow p-4">
          <ul class="space-y-2">
            <li> <a
                class="flex block px-3 py-2 rounded-full hover:bg-gradient-to-r from-gray-100 to-black-100 <?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo 'bg-gradient-to-r from-gray-100 to-black-100 font-semibold'; ?>"
                href="dashboard.php"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg> &nbsp; Dashboard</a> </li>
            <li> <a
                class="flex block px-3 py-2 rounded-full hover:bg-gradient-to-r from-gray-100 to-black-100 <?php if(basename($_SERVER['PHP_SELF'])=='transfer.php') echo 'bg-gradient-to-r from-gray-100 to-black-100 font-semibold'; ?>"
                href="transfer.php"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12.75 19.5v-.75a7.5 7.5 0 0 0-7.5-7.5H4.5m0-6.75h.75c7.87 0 14.25 6.38 14.25 14.25v.75M6 18.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>&nbsp; Transfer</a> </li>
            <li> <a
                class="flex block px-3 py-2 rounded-full hover:bg-gradient-to-r from-gray-100 to-black-100 <?php if(basename($_SERVER['PHP_SELF'])=='transactions.php') echo 'bg-gradient-to-r from-gray-100 to-black-100 font-semibold'; ?>"
                href="transactions.php"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>&nbsp; Transactions</a> </li>
            <li> <a
                class="flex block px-3 py-2 rounded-full hover:bg-gradient-to-r from-gray-100 to-black-100 <?php if(basename($_SERVER['PHP_SELF'])=='feedback.php') echo 'bg-gradient-to-r from-gray-100 to-black-100 font-semibold'; ?>"
                href="feedback.php"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>&nbsp; Feedback</a> </li>
            <?php if($u && $u['role']==='admin'): ?>
            <li> <a
                class="flex block px-3 py-2 rounded-full hover:bg-gradient-to-r from-gray-100 to-black-100 <?php if(basename($_SERVER['PHP_SELF'])=='admin.php') echo 'bg-gradient-to-r from-gray-100 to-black-100 font-semibold'; ?>"
                href="admin.php"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg> &nbsp;Admin</a> </li>
            <?php endif; ?>
            <li class="pt-2"> <a class="flex block px-3 py-2 rounded-full hover:bg-gradient-to-r from-gray-100 to-black-100 <?php if(basename($_SERVER['PHP_SELF'])=='legacy_vuln.php') echo 'bg-gradient-to-r from-gray-100 to-black-100 font-semibold'; ?>"
                href="legacy_vuln.php"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>&nbsp; Search</a> </li>
          </ul>
        </div>
      </aside>

      <!-- Main content START -->
      <main class="lg:w-3/4">

        <!-- Background Change Modal -->
        <?php if($u): ?>
        <div id="bgModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
          <div class="bg-white rounded p-6 max-w-md w-full">
            <h2 class="text-xl font-bold mb-4">Change Background</h2>
            <form method="POST" action="preview_background.php" class="flex flex-col gap-3">
              <?php echo csrf_field(); ?>
              <input type="text" name="bg_url" placeholder="Enter image URL"
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
              <div class="flex gap-2 justify-end">
                <button type="submit" class="px-4 py-2 rounded bg-blue-700 text-white hover:bg-blue-800 transition">
                  Preview
                </button>
                <button type="button" onclick="document.getElementById('bgModal').classList.add('hidden')"
                  class="px-4 py-2 rounded bg-red-700 text-white hover:bg-red-800 transition">
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>


        <?php endif; ?>
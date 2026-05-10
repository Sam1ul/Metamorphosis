<?php
require_once 'functions.php';

$q = trim($_GET['q'] ?? '');
$rows = [];
$err = null;

$pdo = get_pdo();

try {
    if ($q !== '') {
        // Secure parameterized query
        $stmt = $pdo->prepare("
            SELECT id, username
            FROM users
            WHERE username = ?
        ");
        $stmt->execute([$q]);
        $rows = $stmt->fetchAll();
    }
} catch (Exception $e) {
    $err = "Database error";
    error_log($e->getMessage());
}

require_once 'header.php';
?>

<div class="bg-white/80 rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
  <h4 class="text-lg font-semibold text-gray-900">User Search</h4>

  <p class="mt-2 text-gray-700">
    Search for a user by their exact username.
  </p>

  <form class="mt-4">
    <div class="flex w-full max-w-xl">
      <input
        name="q"
        placeholder="Enter username"
        value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>"
        class="flex-1 rounded-l-lg border border-gray-300 bg-gray-50 px-3 py-2
               focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">

      <button
        class="rounded-r-lg bg-blue-600 text-white px-4 py-2 font-semibold
               hover:bg-blue-700 transition">
        Search
      </button>
    </div>
  </form>

  <?php if ($q !== ''): ?>

    <?php if ($err): ?>
      <div class="mt-4 rounded-lg bg-yellow-100 border border-yellow-300 text-yellow-800 px-3 py-2">
        <?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <div class="mt-5">
      <h6 class="font-semibold text-gray-900 mb-2">
        Results (<?php echo count($rows); ?>)
      </h6>

      <?php if (empty($rows)): ?>
        <div class="text-gray-500 text-sm">
          No users found.
        </div>
      <?php else: ?>
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left font-semibold text-gray-600">ID</th>
                <th class="px-4 py-2 text-left font-semibold text-gray-600">Username</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">
              <?php foreach($rows as $r): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-2">
                    <?php echo (int)$r['id']; ?>
                  </td>
                  <td class="px-4 py-2">
                    <?php echo htmlspecialchars($r['username'], ENT_QUOTES, 'UTF-8'); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
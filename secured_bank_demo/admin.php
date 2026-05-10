<?php
require_once 'functions.php';
require_admin();

$pdo = get_pdo();
$search = trim($_GET['q'] ?? '');

// Secure query with prepared statement
if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT id, username, role, balance
        FROM users
        WHERE username LIKE ?
           OR id = ?
        ORDER BY id
    ");

    $like = '%' . $search . '%';
    $stmt->execute([$like, $search]);
    $rows = $stmt->fetchAll();
} else {
    $rows = $pdo->query("
        SELECT id, username, role, balance
        FROM users
        ORDER BY id
    ")->fetchAll();
}

require_once 'header.php';
?>

<div class="p-6 bg-gray-50 min-h-screen">
  <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-800">Admin Panel</h1>
  </div>

  <form class="mb-4 max-w-xl">
    <div class="flex rounded-lg shadow-sm">
      <input
        name="q"
        value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
        placeholder="Search by username or ID..."
        class="flex-1 rounded-l-lg border border-gray-300 px-4 py-2
               focus:outline-none focus:ring-2 focus:ring-indigo-500"
      />
      <button class="px-4 py-2 bg-indigo-600 text-white rounded-r-lg">
        Search
      </button>
    </div>
  </form>

  <div class="bg-white shadow rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b bg-gray-50 flex justify-between items-center">
      <h2 class="font-medium text-gray-700">Users</h2>
      <span class="text-sm text-gray-500">
        <?php echo count($rows); ?> result(s)
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">ID</th>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Username</th>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Role</th>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Balance</th>
            <th class="px-6 py-3 text-right text-xs font-semibold uppercase">Actions</th>
          </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-100">
        <?php foreach($rows as $r): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-3 text-sm"><?php echo (int)$r['id']; ?></td>

            <td class="px-6 py-3 text-sm font-medium">
              <?php echo htmlspecialchars($r['username'], ENT_QUOTES, 'UTF-8'); ?>
            </td>

            <td class="px-6 py-3 text-sm">
              <?php if($r['role'] === 'admin'): ?>
                <span class="px-2 py-1 text-xs font-semibold bg-purple-100 text-purple-700 rounded-full">
                  Admin
                </span>
              <?php else: ?>
                <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded-full">
                  <?php echo ucfirst($r['role']); ?>
                </span>
              <?php endif; ?>
            </td>

            <td class="px-6 py-3 text-sm">
              $<?php echo number_format((float)$r['balance'], 2); ?>
            </td>

            <td class="px-6 py-3 text-right text-sm">
              <a href="edit.php?id=<?php echo (int)$r['id']; ?>"
                 class="text-indigo-600 hover:text-indigo-900 mr-3">
                Edit
              </a>

              <!-- Use POST for delete (not GET) -->
              <form method="post" action="delete.php" class="inline">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                <button type="submit"
                  class="text-red-600 hover:text-red-800"
                  onclick="return confirm('Delete user?');">
                  Delete
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>
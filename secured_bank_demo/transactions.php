<?php
require_once 'functions.php';
require_login();

$pdo = get_pdo();
$me = current_user();

// View only own transactions (no arbitrary user_id)
$view_user = $me['id'];

// If admin wants to view others (optional feature)
if ($me['role'] === 'admin' && isset($_GET['user_id'])) {
    $view_user = (int)$_GET['user_id'];
}

// Fetch user (validate existence)
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
$stmt->execute([$view_user]);
$view = $stmt->fetch();

if (!$view) {
    die("User not found.");
}

require_once 'header.php';
?>

<div class="bg-white/80 rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
  <h4 class="text-xl font-semibold text-gray-900">
    Transactions for <?php echo htmlspecialchars($view['username'], ENT_QUOTES, 'UTF-8'); ?>
  </h4>

  <?php
  // Secure: transactions only for view_user
  $stmt = $pdo->prepare("
      SELECT t.created_at, t.amount,
             u1.username AS from_name,
             u2.username AS to_name
      FROM transactions t
      LEFT JOIN users u1 ON u1.id = t.from_user
      LEFT JOIN users u2 ON u2.id = t.to_user
      WHERE (t.from_user = ? OR t.to_user = ?)
      ORDER BY t.created_at DESC
  ");

  $stmt->execute([$view_user, $view_user]);
  $rows = $stmt->fetchAll();
  ?>

  <?php if ($rows): ?>
    <div class="overflow-x-auto mt-4">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500 border-b">
            <th class="py-2 font-medium">When</th>
            <th class="py-2 font-medium">From</th>
            <th class="py-2 font-medium">To</th>
            <th class="py-2 font-medium">Amount</th>
          </tr>
        </thead>

        <tbody class="divide-y">
        <?php foreach($rows as $r): ?>
          <tr class="hover:bg-gray-50">
            <td class="py-2 whitespace-nowrap">
              <?php echo htmlspecialchars($r['created_at'], ENT_QUOTES, 'UTF-8'); ?>
            </td>

            <td class="py-2">
              <?php echo htmlspecialchars($r['from_name'], ENT_QUOTES, 'UTF-8'); ?>
            </td>

            <td class="py-2">
              <?php echo htmlspecialchars($r['to_name'], ENT_QUOTES, 'UTF-8'); ?>
            </td>

            <td class="py-2 font-semibold">
              $<?php echo number_format((float)$r['amount'], 2); ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-gray-500 text-sm mt-4">No transactions found.</p>
  <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
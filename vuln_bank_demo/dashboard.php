<?php
require_once 'functions.php';
require_login();
$pdo = get_pdo();
$user = current_user();
$stmt = $pdo->prepare("SELECT id, username, role, balance FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$user = $stmt->fetch();
$_SESSION['user'] = $user;
require_once 'header.php';
?>
<div class="grid md:grid-cols-2 gap-4 mb-3">
  <!-- LEFT -->
  <div>
    <!-- Balance hero -->
    <div class="bg-white/90 rounded-2xl shadow-sm border border-gray-100 p-5 mb-3">
      <div class="flex items-center">
        <div class="mr-4 text-blue-700 text-5xl">
          🪙
        </div>

        <div>
          <div class="text-sm text-gray-500">Available Balance</div>

          <div class="text-5xl font-bold tracking-tight text-gray-900">
            $<?php echo number_format($user['balance'],2); ?>
          </div>

          <div class="mt-3 flex items-center gap-2">
            <a href="transfer.php"
               class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-full shadow-sm transition">
              ➜ Transfer
            </a>

            <a href="transactions.php"
               class="inline-flex items-center gap-1 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm px-4 py-1.5 rounded-full transition">
              🕒 History
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="bg-white/80 rounded-2xl shadow-sm border border-gray-100 p-4">
      <h6 class="font-semibold text-gray-800 mb-3">Quick Actions</h6>

      <div class="flex flex-wrap gap-2">
        <button class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm text-gray-700 transition">
          📄 Pay Bills
        </button>

        <button class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm text-gray-700 transition">
          💳 Cards
        </button>

        <button class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm text-gray-700 transition">
          🐖 Save
        </button>
      </div>
    </div>
  </div>

  <!-- RIGHT -->
  <div>
    <div class="bg-white/80 rounded-2xl shadow-sm border border-gray-100 p-4">
      <h6 class="font-semibold text-gray-800">Recent Transactions</h6>

      <?php
      $stmt = $pdo->prepare("SELECT t.*, u1.username AS from_name, u2.username AS to_name
        FROM transactions t
        LEFT JOIN users u1 ON u1.id = t.from_user
        LEFT JOIN users u2 ON u2.id = t.to_user
        WHERE t.from_user = ? OR t.to_user = ?
        ORDER BY t.created_at DESC LIMIT 6");
      $stmt->execute([$user['id'], $user['id']]);
      $rows = $stmt->fetchAll();
      ?>

      <?php if ($rows): ?>
      <div class="overflow-x-auto mt-3">
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
              <td class="py-2"><?php echo $r['created_at']; ?></td>
              <td class="py-2"><?php echo htmlspecialchars($r['from_name']); ?></td>
              <td class="py-2"><?php echo htmlspecialchars($r['to_name']); ?></td>
              <td class="py-2 font-semibold">$<?php echo number_format($r['amount'],2); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
        <p class="text-gray-500 text-sm mt-3">No recent transactions.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>

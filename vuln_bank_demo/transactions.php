<?php
require_once 'functions.php';
require_login();
$pdo = get_pdo();
$me = current_user();
$view_user = intval($_GET['user_id'] ?? $me['id']);
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
$stmt->execute([$view_user]);
$view = $stmt->fetch();
require_once 'header.php';
?>
<div class="card p-4 mb-4">
  <h4>Transactions for <?php echo htmlspecialchars($view['username'] ?? 'Unknown'); ?></h4>
  <p class="text-warning">IDOR demo: change <code>?user_id=</code> to view other users' transactions.</p>
  <?php
  $stmt = $pdo->prepare("SELECT t.*, u1.username AS from_name, u2.username AS to_name
      FROM transactions t
      LEFT JOIN users u1 ON u1.id = t.from_user
      LEFT JOIN users u2 ON u2.id = t.to_user
      WHERE t.from_user = ? OR t.to_user = ?
      ORDER BY t.created_at DESC");
  $stmt->execute([$view_user, $view_user]);
  $rows = $stmt->fetchAll();
  ?>
  <?php if ($rows): ?>
    <div class="table-responsive mt-2">
      <table class="table table-hover">
        <thead class="table-light"><tr><th>When</th><th>From</th><th>To</th><th>Amount</th></tr></thead>
        <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?php echo $r['created_at']; ?></td>
            <td><?php echo htmlspecialchars($r['from_name']); ?></td>
            <td><?php echo htmlspecialchars($r['to_name']); ?></td>
            <td>$<?php echo number_format($r['amount'],2); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-muted mt-2">No transactions found.</p>
  <?php endif; ?>
</div>
<?php require_once 'footer.php'; ?>

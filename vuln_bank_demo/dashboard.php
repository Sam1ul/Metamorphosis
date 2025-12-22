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
<div class="row mb-3">
  <div class="col-md-6">
    <div class="card card-hero p-4 mb-3">
      <div class="d-flex align-items-center">
        <div class="me-3">
          <i class="bi bi-wallet2" style="font-size:2.6rem;color:#2b6cb0;"></i>
        </div>
        <div>
          <div class="small-muted">Available Balance</div>
          <div class="balance-amount">$<?php echo number_format($user['balance'],2); ?></div>
          <div class="mt-2">
            <a href="transfer.php" class="btn btn-primary btn-sm rounded-pill"><i class="bi bi-arrow-right-square me-1"></i> Transfer</a>
            <a href="transactions.php" class="btn btn-outline-secondary btn-sm rounded-pill ms-2"><i class="bi bi-clock-history me-1"></i> History</a>
          </div>
        </div>
      </div>
    </div>

    <div class="card p-3">
      <h6 class="mb-2">Quick Actions</h6>
      <div class="d-flex gap-2">
        <button class="btn btn-light btn-sm"><i class="bi bi-file-earmark-text me-1"></i> Pay Bills</button>
        <button class="btn btn-light btn-sm"><i class="bi bi-credit-card-2-front me-1"></i> Cards</button>
        <button class="btn btn-light btn-sm"><i class="bi bi-piggy-bank me-1"></i> Save</button>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-3">
      <h6>Recent Transactions</h6>
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
        <p class="text-muted mt-2">No recent transactions.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require_once 'footer.php'; ?>

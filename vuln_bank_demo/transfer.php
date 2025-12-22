<?php
require_once 'functions.php';
require_login();
$pdo = get_pdo();
$user = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // intentionally demonstrating CSRF vulnerability by not calling validate_csrf()
    $to_user = intval($_POST['to_user'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    if ($amount <= 0) {
        flash("Invalid amount.");
    } elseif ($to_user === $user['id']) {
        flash("Cannot transfer to yourself.");
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$user['id']]);
            $balance = $stmt->fetchColumn();
            if ($balance < $amount) throw new Exception("Insufficient funds.");
            $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$amount, $user['id']]);
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$amount, $to_user]);
            $stmt = $pdo->prepare("INSERT INTO transactions (from_user, to_user, amount) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $to_user, $amount]);
            $pdo->commit();
            flash("Transferred $" . number_format($amount,2) . " successfully.");
            header('Location: dashboard.php'); exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            flash("Transfer failed: " . $e->getMessage());
        }
    }
}
$others = $pdo->query("SELECT id, username FROM users WHERE id != " . (int)$user['id'])->fetchAll();
require_once 'header.php';
?>
<div class="card p-4 mb-4">
  <h4>Transfer Funds</h4>
  <p class="text-danger"><strong>Vulnerable demo:</strong> this endpoint intentionally does not validate CSRF tokens server-side.</p>
  <form method="post" class="mt-3" style="max-width:540px;">
    <!-- No csrf_field() here to demonstrate CSRF -->
    <div class="mb-3">
      <label class="form-label">Recipient</label>
      <select name="to_user" class="form-select">
        <?php foreach($others as $o): ?>
          <option value="<?php echo $o['id']; ?>"><?php echo htmlspecialchars($o['username']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Amount</label>
      <input type="number" step="0.01" name="amount" class="form-control">
    </div>
    <button class="btn btn-primary">Transfer</button>
  </form>
</div>

sponsored by <a href="./asolhero.html"><img src="./Generated Image November 20, 2025 - 9_26PM.png" alt="" width="200rem"></a>
<br><b style="color:red;">click on the malicious advertisement for visualize CSRF attack</b>
<?php require_once 'footer.php'; ?>

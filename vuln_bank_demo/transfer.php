<?php
require_once 'functions.php';
require_login();
$pdo = get_pdo();
$user = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // intentionally demonstrating CSRF vulnerability by not calling validate_csrf()
    $to_user = intval($_POST['to_user'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);


    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$user['id']]);
        $balance = $stmt->fetchColumn();

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
$others = $pdo->query("SELECT id, username FROM users")->fetchAll();
require_once 'header.php';
?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

<div class="lg:col-span-2 bg-white/80 rounded-2xl shadow-sm border border-gray-100 p-5">
  <h4 class="text-xl font-semibold text-gray-900">Transfer Funds</h4>

  <form method="post" class="mt-5 max-w-lg">

    <div class="mb-4">
      <label class="block text-sm font-semibold text-gray-700 mb-1">
        Recipient ID
      </label>

      <input type="number" name="to_user" id="to_user"
        class="w-full rounded-lg border border-gray-300 bg-gray-50/80 px-3 py-2.5
               focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200
               outline-none transition"
        placeholder="Enter user ID">
    </div>

    <!-- auto filled name -->
    <div class="mb-4">
      <label class="block text-sm font-semibold text-gray-700 mb-1">
        Recipient Name
      </label>

      <div id="recipientName"
           class="w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2.5 text-gray-600">
        —
      </div>
    </div>

    <div class="mb-4">
      <label class="block text-sm font-semibold text-gray-700 mb-1">
        Amount
      </label>

      <input type="number" step="0.01" name="amount"
        class="w-full rounded-lg border border-gray-300 bg-gray-50/80 px-3 py-2.5
               focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200
               outline-none transition">
    </div>

    <button
      class="bg-blue-600 hover:bg-blue-700 active:scale-[0.99]
             text-white font-semibold px-5 py-2.5 rounded-lg
             shadow-sm transition">
      Transfer
    </button>
  </form>
</div>
<script>
const users = {
<?php foreach($others as $o): ?>
  <?php echo $o['id']; ?>: "<?php echo htmlspecialchars($o['username']); ?>",
<?php endforeach; ?>
};

const input = document.getElementById('to_user');
const nameBox = document.getElementById('recipientName');

input.addEventListener('input', () => {
  const id = input.value;
  if (users[id]) {
    nameBox.textContent = users[id];
  } else {
    nameBox.textContent = 'User not found';
  }
});
</script>


  <!-- Sponsored -->
  <div>
    <div class="bg-white/70 rounded-2xl shadow-sm border border-gray-100 p-5">
      <div class="text-sm text-gray-500 mb-3">Sponsored</div>

      <a href="./asolhero.html" class="inline-block">
        <img src="./Generated Image November 20, 2025 - 9_26PM.png"
             alt="Advertisement"
             class="w-full rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition">
      </a>

      <div class="mt-3 text-gray-600 font-semibold text-sm">
        Drink milk, from our excellent cow. <br><br> -Asolhero Dairy CO.
      </div>
    </div>
  </div>

</div>


<?php require_once 'footer.php'; ?>

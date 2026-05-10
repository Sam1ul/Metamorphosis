<?php
require_once 'functions.php';
require_login();

$pdo = get_pdo();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validate_csrf();

    $to_user = (int)($_POST['to_user'] ?? 0);
    $amount  = (float)($_POST['amount'] ?? 0);

    if ($amount <= 0) {
        flash("Invalid amount.");
    }
    elseif ($to_user === (int)$user['id']) {
        flash("Cannot transfer to yourself.");
    }
    else {
        try {
            $pdo->beginTransaction();

            // Lock sender balance
            $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([(int)$user['id']]);
            $balance = (float)$stmt->fetchColumn();

            if ($balance < $amount) {
                throw new Exception("Insufficient funds.");
            }

            // Debit sender
            $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$amount, (int)$user['id']]);

            // Credit receiver
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$amount, $to_user]);

            // Record transaction
            $stmt = $pdo->prepare("
                INSERT INTO transactions (from_user, to_user, amount)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([(int)$user['id'], $to_user, $amount]);

            $pdo->commit();

            flash("Transferred $" . number_format($amount, 2) . " successfully.");
            header('Location: dashboard.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            flash("Transfer failed.");
            error_log($e->getMessage());
        }
    }
}

// Secure recipient list
$others = $pdo->prepare("SELECT id, username FROM users WHERE id != ?");
$others->execute([(int)$user['id']]);
$rows = $others->fetchAll();

// Build JS map safely
$usersMap = [];
foreach ($rows as $o) {
    $usersMap[$o['id']] = $o['username'];
}

require_once 'header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <div class="lg:col-span-2 bg-white/80 rounded-2xl shadow-sm border border-gray-100 p-5">
    <h4 class="text-xl font-semibold text-gray-900">Transfer Funds</h4>

    <form method="post" class="mt-5 max-w-lg">
      <?php echo csrf_field(); ?>

      <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1">
          Recipient ID
        </label>

        <input type="number" name="to_user" id="to_user"
          class="w-full rounded-lg border border-gray-300 bg-gray-50/80 px-3 py-2.5
                 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200
                 outline-none transition"
          placeholder="Enter user ID"
          required>
      </div>

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
          required
          class="w-full rounded-lg border border-gray-300 bg-gray-50/80 px-3 py-2.5
                 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200
                 outline-none transition">
      </div>

      <button
        class="bg-blue-600 hover:bg-blue-700 active:scale-[0.99]
               text-white font-semibold px-5 py-2.5 rounded-lg shadow-sm transition">
        Transfer
      </button>
    </form>
  </div>

  <!-- Sponsored (demo content - fine for lab) -->
  <div>
    <div class="bg-white/70 rounded-2xl shadow-sm border border-gray-100 p-5">
      <div class="text-sm text-gray-500 mb-3">Sponsored</div>

      <a href="./asolhero.html" target="_blank" class="inline-block">
        <img src="./Generated Image November 20, 2025 - 9_26PM.png"
             alt="Advertisement"
             class="w-full rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition">
      </a>

      <div class="mt-3 text-gray-600 font-semibold text-sm">
        Drink milk, from our excellent cow.<br><br>- Asolhero Dairy Co.
      </div>
    </div>
  </div>

</div>

<script>
const users = <?php echo json_encode($usersMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

const input = document.getElementById('to_user');
const nameBox = document.getElementById('recipientName');

input.addEventListener('input', () => {
  const id = input.value;
  nameBox.textContent = users[id] || 'User not found';
});
</script>

<?php require_once 'footer.php'; ?>
<?php
require_once 'functions.php';
require_admin();

$pdo = get_pdo();

// Validate ID
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die("Invalid user.");
}

// Fetch user safely
$stmt = $pdo->prepare("SELECT id, username, role, balance FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

// Update on POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validate_csrf();

    $username = trim($_POST['username'] ?? '');
    $role     = $_POST['role'] ?? 'user';
    $balance  = (float)($_POST['balance'] ?? 0);

    if ($username === '') {
        flash("Username required.");
        header("Location: edit.php?id={$id}");
        exit;
    }

    // Secure update
    $stmt = $pdo->prepare("
        UPDATE users
        SET username = ?,
            role = ?,
            balance = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $username,
        $role === 'admin' ? 'admin' : 'user',
        $balance,
        $id
    ]);

    flash("User updated.");
    header("Location: admin.php");
    exit;
}

require_once 'header.php';
?>

<div class="p-6 bg-white min-h-screen max-w-xl mx-auto">
  <h1 class="text-2xl font-semibold mb-4">Edit User</h1>

  <form method="POST" class="bg-white shadow rounded-xl p-6 space-y-4">
    <?php echo csrf_field(); ?>

    <div>
      <label class="block text-sm font-medium mb-1">Username</label>
      <input name="username"
             value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>"
             class="w-full border rounded-lg px-3 py-2"
             required>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Role</label>
      <select name="role" class="w-full border rounded-lg px-3 py-2">
        <option value="user" <?php if($user['role']=='user') echo 'selected'; ?>>User</option>
        <option value="admin" <?php if($user['role']=='admin') echo 'selected'; ?>>Admin</option>
      </select>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Balance</label>
      <input name="balance"
             value="<?php echo htmlspecialchars((string)$user['balance'], ENT_QUOTES, 'UTF-8'); ?>"
             class="w-full border rounded-lg px-3 py-2"
             type="number"
             step="0.01">
    </div>

    <div class="flex justify-end gap-2">
      <a href="admin.php" class="px-4 py-2 bg-gray-200 rounded-lg">Cancel</a>
      <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">
        Save
      </button>
    </div>
  </form>
</div>

<?php require_once 'footer.php'; ?>
<?php
require_once 'functions.php';
require_admin();

$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validate_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'user';
    $balance  = (float)($_POST['balance'] ?? 0);

    if ($username === '' || $password === '') {
        flash("Username and password required.");
        header("Location: add.php");
        exit;
    }

    // Hash password
    $hash = password_hash($password, PASSWORD_BCRYPT);

    // Secure insert
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, role, balance)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $username,
        $hash,
        $role === 'admin' ? 'admin' : 'user',
        $balance
    ]);

    flash("User created.");
    header("Location: admin.php");
    exit;
}

require_once 'header.php';
?>

<div class="p-6 bg-white min-h-screen max-w-xl mx-auto">
  <h1 class="text-2xl font-semibold mb-4">Add User</h1>

  <form method="POST" class="bg-white shadow rounded-xl p-6 space-y-4">
    <?php echo csrf_field(); ?>

    <div>
      <label class="block text-sm font-medium mb-1">Username</label>
      <input name="username"
             required
             class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Password</label>
      <input type="password"
             name="password"
             required
             class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Role</label>
      <select name="role" class="w-full border rounded-lg px-3 py-2">
        <option value="user">User</option>
        <option value="admin">Admin</option>
      </select>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Balance</label>
      <input name="balance"
             type="number"
             step="0.01"
             value="0"
             class="w-full border rounded-lg px-3 py-2">
    </div>

    <div class="flex justify-end gap-2">
      <a href="admin.php" class="px-4 py-2 bg-gray-200 rounded-lg">Cancel</a>
      <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">
        Create
      </button>
    </div>
  </form>
</div>

<?php require_once 'footer.php'; ?>
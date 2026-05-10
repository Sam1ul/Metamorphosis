<?php
require_once 'functions.php';
$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? ''; // added password
    $role     = $_POST['role'] ?? 'user';
    $balance  = $_POST['balance'] ?? 0;

    // intentionally vulnerable: raw SQL, no escaping, no validation
    $sql = "INSERT INTO users (username, password, role, balance)
            VALUES ('$username', '$password', '$role', $balance)";
    $pdo->exec($sql);

    header("Location: admin.php");
    exit;
}

require_once 'header.php';
?>

<div class="p-6 bg-white/80 backdrop-blur-[3px] min-h-screen max-w-xl mx-auto">
  <h1 class="text-2xl font-semibold mb-4">Add User</h1>

  <form method="POST" class="bg-white shadow rounded-xl p-6 space-y-4">
    <div>
      <label class="block text-sm font-medium mb-1">Username</label>
      <input name="username" class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Password</label>
      <input type="password" name="password" class="w-full border rounded-lg px-3 py-2">
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
      <input name="balance" value="0" class="w-full border rounded-lg px-3 py-2">
    </div>

    <div class="flex justify-end gap-2">
      <a href="admin.php" class="px-4 py-2 bg-gray-200 rounded-lg">Cancel</a>
      <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
        Create
      </button>
    </div>
  </form>
</div>

<?php require_once 'footer.php'; ?>

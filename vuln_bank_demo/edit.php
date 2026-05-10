<?php
require_once 'functions.php';
$pdo = get_pdo();

$id = $_GET['id'] ?? '';

// fetch current user (vulnerable)
$sql = "SELECT * FROM users WHERE id = $id";
$user = $pdo->query($sql)->fetch();

if (!$user) {
    die("User not found");
}

// update (also vulnerable)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $role = $_POST['role'] ?? '';
    $balance = $_POST['balance'] ?? 0;

    $sql = "UPDATE users
    SET username = '$username',
    role = '$role',
    balance = $balance
    WHERE id = $id";

    $pdo->exec($sql);

    header("Location: admin.php");
    exit;
}

require_once 'header.php';
?>

<div class="p-6 bg-white/80 min-h-screen max-w-xl mx-auto">
<h1 class="text-2xl font-semibold mb-4">Edit User</h1>

<form method="POST" class="bg-white/80 backdrop-blur-[3px] shadow rounded-xl p-6 space-y-4">
<div>
<label class="block text-sm font-medium mb-1">Username</label>
<input name="username"
value="<?php echo htmlspecialchars($user['username']); ?>"
class="w-full border rounded-lg px-3 py-2">
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
value="<?php echo $user['balance']; ?>"
class="w-full border rounded-lg px-3 py-2">
</div>

<div class="flex justify-end gap-2">
<a href="admin.php" class="px-4 py-2 bg-gray-200 rounded-lg">Cancel</a>
<button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
Save
</button>
</div>
</form>
</div>

<?php require_once 'footer.php'; ?>

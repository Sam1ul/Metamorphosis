<?php
require_once 'functions.php';
$pdo = get_pdo();

$search = $_GET['q'] ?? '';

// intentionally vulnerable
$sql = "SELECT id, username, role, balance FROM users";
if ($search !== '') {
  $sql .= " WHERE username LIKE '%" . $search . "%' OR id LIKE '%" . $search . "%'";
}
$sql .= " ORDER BY id";

$rows = $pdo->query($sql)->fetchAll();

require_once 'header.php';
?>

<div class="p-6 bg-gray-50 min-h-screen bg-white/80 backdrop-blur-[3px]">
<!-- Header -->
<div class="mb-6 flex items-center justify-between">
<div>
<h1 class="text-2xl font-semibold text-gray-800">Admin Panel</h1>

</div>

<div class="flex gap-2">
<a href="add.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">
Add User
</a>

<a href="export.php" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
Export
</a>

</div>
</div>

<!-- Search -->
<form class="mb-4 max-w-xl">
<div class="flex rounded-lg shadow-sm">
<input
name="q"
value="<?php echo htmlspecialchars($search); ?>"
placeholder="Search by username or ID..."
class="flex-1 rounded-l-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
/>
<button class="px-4 py-2 bg-indigo-600 text-white rounded-r-lg hover:bg-indigo-700">
Search
</button>
</div>
</form>

<!-- Table -->
<div class="bg-white shadow rounded-xl overflow-hidden">
<div class="px-4 py-3 border-b bg-gray-50 flex justify-between items-center">
<h2 class="font-medium text-gray-700">Users</h2>
<span class="text-sm text-gray-500">
<?php echo count($rows); ?> result(s)
</span>
</div>

<div class="overflow-x-auto ">
<table class="min-w-full divide-y divide-gray-200 ">
<thead class="bg-gray-100">
<tr>
<th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
<th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Username</th>
<th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Role</th>
<th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Balance</th>
<th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
</tr>
</thead>

<tbody class="bg-white divide-y divide-gray-100">
<?php foreach($rows as $r): ?>
<tr class="hover:bg-gray-50">
<td class="px-6 py-3 text-sm text-gray-700">
<?php echo $r['id']; ?>
</td>

<td class="px-6 py-3 text-sm font-medium text-gray-900">
<?php echo htmlspecialchars($r['username']); ?>
</td>

<td class="px-6 py-3 text-sm">
<?php if($r['role'] === 'admin'): ?>
<span class="px-2 py-1 text-xs font-semibold bg-purple-100 text-purple-700 rounded-full">
Admin
</span>
<?php else: ?>
<span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded-full">
<?php echo ucfirst($r['role']); ?>
</span>
<?php endif; ?>
</td>

<td class="px-6 py-3 text-sm text-gray-700">
$<?php echo number_format($r['balance'],2); ?>
</td>

<td class="px-6 py-3 text-right text-sm">
<a href="edit.php?id=<?php echo $r['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
Edit
</a>
<a href="delete.php?id=<?php echo $r['id']; ?>" class="text-red-600 hover:text-red-800">
Delete
</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</div>

<?php require_once 'footer.php'; ?>

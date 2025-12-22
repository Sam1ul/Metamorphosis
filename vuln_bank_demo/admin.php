<?php
require_once 'functions.php';
require_admin();
$pdo = get_pdo();
$search = $_GET['q'] ?? '';
$sql = "SELECT id, username, role, balance FROM users";
if ($search !== '') {
    $sql .= " WHERE username LIKE '%" . $search . "%'"; // intentionally vulnerable
}
$sql .= " ORDER BY id";
$rows = $pdo->query($sql)->fetchAll();
require_once 'header.php';
?>
<div class="card p-4 mb-4">
  <h4>Admin Panel</h4>
  <p class="text-danger">This admin search is intentionally vulnerable to SQL injection for demonstration. Do not expose publicly.</p>
  <form class="mb-3">
    <div class="input-group" style="max-width:540px;">
      <input name="q" class="form-control" placeholder="Search username" value="<?php echo htmlspecialchars($search); ?>">
      <button class="btn btn-outline-primary">Search</button>
    </div>
  </form>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead class="table-light"><tr><th>ID</th><th>Username</th><th>Role</th><th>Balance</th></tr></thead>
      <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><?php echo htmlspecialchars($r['username']); ?></td>
          <td><?php echo $r['role']; ?></td>
          <td>$<?php echo number_format($r['balance'],2); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once 'footer.php'; ?>

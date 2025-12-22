<?php
require_once 'functions.php';
$msg = $_GET['msg'] ?? '';
$q = $_GET['q'] ?? '';
$sql = "SELECT id, username FROM users";
if ($q !== '') {
    $sql .= " WHERE username = '" . $q . "'";
}
$pdo = get_pdo();
try {
    $rows = $pdo->query($sql)->fetchAll();
} catch (Exception $e) {
    $rows = [];
    $err = $e->getMessage();
}
require_once 'header.php';
?>
<div class="card p-4 mb-4">
  <h4 class="text-danger">Legacy Vulnerable Examples</h4>
  <p>This page demonstrates reflected XSS and SQL injection vulnerabilities.</p>
  <h6>Reflected XSS</h6>
  <p>Try <code>?msg=&lt;script&gt;alert("Step 1");
alert("Step 2");
alert("Step 3");
alert("Step 4");
alert("Step 5");
alert("Step 6");
&lt;/script&gt;</code> at the end of the url</p>
  <p class="border p-2 bg-white"><?php echo $msg; /* intentionally unescaped */ ?></p>
  <h6 class="mt-4">SQL Injection (unsafe search)</h6>
  <form class="mb-3">
    <div class="input-group" style="max-width:540px;">
      <input name="q" class="form-control" placeholder="Exact username" value="<?php echo htmlspecialchars($q); ?>">
      <button class="btn btn-danger">Search (vulnerable)</button>
    </div>
  </form>
  <?php if (!empty($err)): ?><div class="alert alert-warning"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
  <h6>Results (<?php echo count($rows); ?>)</h6>
  <ul>
  <?php foreach($rows as $r): ?>
    <li><?php echo htmlspecialchars($r['username']); ?> (id: <?php echo $r['id']; ?>)</li>
  <?php endforeach; ?>
  </ul>
</div>
<?php require_once 'footer.php'; ?>

<?php
require_once 'functions.php';
validate_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        flash("Please enter username and password.");
    } else {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->prepare("INSERT INTO users (username, password, balance) VALUES (?, ?, 0)");
            $stmt->execute([$username, $password]); // plaintext intentionally
            flash("Registered. You can now log in.");
            header('Location: login.php'); exit;
        } catch (PDOException $e) {
            flash("Error: " . htmlspecialchars($e->getMessage()));
        }
    }
}
require_once 'header.php';
?>
<div class="card p-4 mb-4">
  <h4>Register</h4>
  <form method="post" class="mt-3">
    <?php echo csrf_field(); ?>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input class="form-control" name="username" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password (plaintext stored for demo)</label>
      <input class="form-control" name="password" type="password" required>
    </div>
    <button class="btn btn-primary">Register</button>
  </form>
</div>
<?php require_once 'footer.php'; ?>

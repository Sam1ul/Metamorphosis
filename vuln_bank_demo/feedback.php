<?php
require_once 'functions.php';
require_login();
$pdo = get_pdo();
$user = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();
    $msg = $_POST['message'] ?? '';
    $stmt = $pdo->prepare("INSERT INTO feedbacks (user_id, message) VALUES (?, ?)");
    $stmt->execute([$user['id'], $msg]); // stored without sanitization intentionally
    flash("Feedback submitted.");
    header('Location: feedback.php'); exit;
}
$stmt = $pdo->query("SELECT f.*, u.username FROM feedbacks f LEFT JOIN users u ON u.id = f.user_id ORDER BY f.created_at DESC");
$rows = $stmt->fetchAll();
require_once 'header.php';
?>
<div class="card p-4 mb-4">
  <h4>Feedback</h4>
  <p class="text-danger">Stored XSS demo: messages are stored and displayed without escaping.</p>
  <form method="post" class="mb-3" style="max-width:720px;">
    <?php echo csrf_field(); ?>
    <div class="mb-3">
      <textarea name="message" class="form-control" rows="3" placeholder="Leave feedback (HTML allowed)"></textarea>
    </div>
    <button class="btn btn-primary">Submit</button>
  </form>
  <h5>Messages</h5>
  <?php if ($rows): ?>
    <ul class="list-group">
    <?php foreach($rows as $r): ?>
      <li class="list-group-item">
        <div><strong><?php echo htmlspecialchars($r['username']); ?></strong> <span class="small text-muted"><?php echo $r['created_at']; ?></span></div>
        <div class="mt-2"><?php echo $r['message']; /* intentionally unescaped */ ?></div>
      </li>
    <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="text-muted">No messages yet.</p>
  <?php endif; ?>
</div>
<?php require_once 'footer.php'; ?>

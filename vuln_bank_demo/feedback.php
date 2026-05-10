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
<div class="bg-dark/80 backdrop-blur-[3px] rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
  <h4 class="text-xl font-semibold text-white">Feedback</h4>

  

  <div class="grid lg:grid-cols-2 gap-6 mt-6">
    <!-- RIGHT: Messages -->
    <div>

      <?php if ($rows): ?>
        <ul class="space-y-3 max-h-[420px] overflow-y-auto pr-1">
        <?php foreach($rows as $r): ?>
          <li class="border border-gray-200 rounded-xl p-4 bg-white/80 backdrop-blur-[3px]">
            <div class="flex items-center justify-between text-sm">
              <strong class="text-gray-900 ">
                <?php echo htmlspecialchars($r['username']); ?>
              </strong>

              <span class="text-light-500">
                <?php echo $r['created_at']; ?>
              </span>
            </div>

            <!-- intentionally unescaped -->
            <div class="mt-2 text-gray-800 ">
              <?php echo $r['message']; ?>
            </div>
          </li>
        <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="text-gray-500">No messages yet.</p>
      <?php endif; ?>
    </div>
    <!-- LEFT: Send message -->
    <div  class="bg-white/80 backdrop-blur-[3px] p-3 rounded-xl">
      <h5 class="text-lg font-semibold text-gray-800 mb-3">Leave a message</h5>

      <form method="post" class="space-y-3">
        <?php echo csrf_field(); ?>

        <textarea
          name="message"
          rows="4"
          placeholder="Leave feedback (HTML allowed)"
          class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-3 py-2.5
                 focus:bg-white/70 focus:border-blue-500 focus:ring-2 focus:ring-blue-200
                 outline-none transition bg-dark/80 backdrop-blur-[3px]"></textarea>

        <button
          class="w-full bg-blue-600 hover:bg-blue-700 active:scale-[0.99]
                 text-white font-semibold py-2.5 rounded-lg
                 shadow-sm transition">
          Submit
        </button>
      </form>
    </div>

    
  </div>
</div>

<?php require_once 'footer.php'; ?>

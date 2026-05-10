<?php
require_once 'functions.php';
require_login();

$pdo = get_pdo();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validate_csrf();

    $msg = trim($_POST['message'] ?? '');

    // ===== INPUT VALIDATION =====
    if (empty($msg)) {
        flash("Message cannot be empty.");
        header('Location: feedback.php');
        exit;
    }

    if (strlen($msg) > 500) {
        flash("Message too long (max 500 characters).");
        header('Location: feedback.php');
        exit;
    }

    // Store raw (safe approach: escape on output, not input)
    $stmt = $pdo->prepare("INSERT INTO feedbacks (user_id, message) VALUES (?, ?)");
    $stmt->execute([(int)$user['id'], $msg]);

    flash("Feedback submitted securely.");
    header('Location: feedback.php');
    exit;
}

// Only select required columns
$stmt = $pdo->query("
    SELECT f.message, f.created_at, u.username
    FROM feedbacks f
    LEFT JOIN users u ON u.id = f.user_id
    ORDER BY f.created_at DESC
");

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
              <strong class="text-gray-900">
                <?php echo htmlspecialchars($r['username'], ENT_QUOTES, 'UTF-8'); ?>
              </strong>

              <span class="text-gray-500">
                <?php echo htmlspecialchars($r['created_at'], ENT_QUOTES, 'UTF-8'); ?>
              </span>
            </div>

            <div class="mt-2 text-gray-800">
              <?php echo nl2br(htmlspecialchars($r['message'], ENT_QUOTES, 'UTF-8')); ?>
            </div>

          </li>
        <?php endforeach; ?>

        </ul>
      <?php else: ?>
        <p class="text-gray-500">No messages yet.</p>
      <?php endif; ?>
    </div>

    <!-- LEFT: Send message -->
    <div class="bg-white/80 backdrop-blur-[3px] p-3 rounded-xl">
      <h5 class="text-lg font-semibold text-gray-800 mb-3">Leave a message</h5>

      <form method="post" class="space-y-3">
        <?php echo csrf_field(); ?>

        <textarea
          name="message"
          rows="4"
          maxlength="500"
          required
          placeholder="Leave feedback (No HTML allowed)"
          class="w-full rounded-lg border border-gray-300 px-3 py-2.5
                 focus:border-blue-500 focus:ring-2 focus:ring-blue-200
                 outline-none transition"></textarea>

        <button
          class="w-full bg-blue-600 hover:bg-blue-700
                 text-white font-semibold py-2.5 rounded-lg
                 shadow-sm transition">
          Submit
        </button>
      </form>
    </div>

  </div>
</div>

<?php require_once 'footer.php'; ?>
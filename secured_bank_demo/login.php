<?php
require_once 'functions.php';

$submitted_user = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validate_csrf();

    $submitted_user = trim($_POST['username'] ?? '');
    $password        = $_POST['password'] ?? '';

    if ($submitted_user === '' || $password === '') {
        flash("Username and password are required.");
        header('Location: login.php');
        exit;
    }

    $pdo = get_pdo();

    // Secure query using prepared statement
    $stmt = $pdo->prepare("
        SELECT id, username, password, role, balance
        FROM users
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->execute([$submitted_user]);
    $user = $stmt->fetch();

    // Verify password (if using password_hash in DB)
    if ($user && password_verify($password, $user['password'])) {

        unset($user['password']);
        $_SESSION['user'] = $user;

        flash("Welcome back, " . $user['username']);
        header('Location: dashboard.php');
        exit;
    }

    // Generic error (no username reflection)
    flash("Invalid username or password.");
}

require_once 'header.php';
?>

<div class="max-w-md ms-9">
  <div class="bg-white/70 shadow-xl rounded-2xl p-6 mb-4 border border-gray-100 pb-9">
    <div class="text-center text-8xl mb-3">🏦</div>

    <h4 class="text-center text-2xl font-bold tracking-tight text-gray-800">
      Secure Login
    </h4>

    <form method="post" class="mt-3 space-y-5 p-3">
      <?php echo csrf_field(); ?>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">
          Username
        </label>

        <input
          name="username"
          value="<?php echo htmlspecialchars($submitted_user, ENT_QUOTES, 'UTF-8'); ?>"
          autocomplete="username"
          class="w-full rounded-lg border border-gray-300 bg-gray-50/70 px-3 py-2.5
                 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200
                 outline-none transition"
        >
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">
          Password
        </label>

        <input
          name="password"
          type="password"
          autocomplete="current-password"
          class="w-full rounded-lg border border-gray-300 bg-gray-50/70 px-3 py-2.5
                 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200
                 outline-none transition"
        >
      </div>

      <button
        class="w-full bg-blue-600 hover:bg-blue-700 active:scale-[0.99]
               text-white font-semibold py-2.5 rounded-lg shadow-sm transition">
        Login
      </button>
    </form>
  </div>
</div>

<?php require_once 'footer.php'; ?>
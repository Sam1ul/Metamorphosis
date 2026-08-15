<?php
require_once 'functions.php';

$submitted_user = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Validate CSRF token
  validate_csrf();

  $submitted_user = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  // Validate required fields
  if ($submitted_user === '' || $password === '') {
    flash("Username and password are required.");
    header('Location: login.php');
    exit;
  }

  // Get database connection
  $pdo = db();

  // Check login rate limit
  check_rate_limit($pdo, $submitted_user);

  // Secure query using prepared statement
  $stmt = $pdo->prepare("
  SELECT id, username, password, role, balance
  FROM users
  WHERE username = ?
  LIMIT 1
  ");

  $stmt->execute([$submitted_user]);

  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  // Verify password
  if ($user && password_verify($password, $user['password'])) {

    // Prevent session fixation
    session_regenerate_id(true);

    // Clear failed login attempts
    clear_failed_logins($pdo, $user['username']);

    // Log successful login
    log_successful_login($user['username']);

    // Never store password hash in session
    unset($user['password']);

    $_SESSION['user'] = $user;

    flash("Welcome back, {$user['username']}.");

    header('Location: dashboard.php');
    exit;
  }

  // Record failed login attempt
  register_failed_login($pdo, $submitted_user);

  // Generic authentication error
  flash("Invalid username or password.");

  // Prevent form resubmission (PRG Pattern)
  header('Location: login.php');
  exit;
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
type="text"
name="username"
value="<?php echo e($submitted_user); ?>"
autocomplete="username"
required
class="w-full rounded-lg border border-gray-300 bg-gray-50/70 px-3 py-2.5
focus:bg-white focus:border-blue-500 focus:ring-2
focus:ring-blue-200 outline-none transition">
</div>

<div>
<label class="block text-sm font-semibold text-gray-700 mb-1">
Password
</label>

<input
type="password"
name="password"
autocomplete="current-password"
required
class="w-full rounded-lg border border-gray-300 bg-gray-50/70 px-3 py-2.5
focus:bg-white focus:border-blue-500 focus:ring-2
focus:ring-blue-200 outline-none transition">
</div>

<button
type="submit"
class="w-full bg-blue-600 hover:bg-blue-700 active:scale-[0.99]
text-white font-semibold py-2.5 rounded-lg shadow-sm transition">
Login
</button>

</form>

</div>
</div>

<?php require_once 'footer.php'; ?>

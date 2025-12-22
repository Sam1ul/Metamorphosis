<?php
// login.php - INTENTIONALLY VULNERABLE
//  - No CSRF protection
//  - SQL Injection (unsafe concatenation + query())
//  - Reflected XSS (username echoed unescaped)
//
// USE ONLY IN A LOCAL LAB. DELETE/RESTORE AFTER DEMO.

require_once 'functions.php';

// NOTE: We intentionally do NOT call validate_csrf() here.
// This makes the POST endpoint vulnerable to CSRF demonstrations.

$submitted_user = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // get raw inputs (no trimming or sanitization)
    $submitted_user = $_POST['username'] ?? '';
    $password       = $_POST['password'] ?? '';

    $pdo = get_pdo();

    // --- VULNERABLE: build SQL by concatenation (DO NOT DO THIS IN REAL APPS) ---
    // This concatenates both username and password into SQL, enabling SQL injection.
    $sql = "SELECT id, username, password, role, balance FROM users
            WHERE username = '" . $submitted_user . "'
              AND password = '" . $password . "';";

    try {
        // Using query() here executes the raw SQL string (intentionally dangerous).
        $stmt = $pdo->query($sql);
        $user = $stmt->fetch();
    } catch (Exception $e) {
        // Show DB error for teaching (do NOT do this in production)
        flash("Database error: " . $e->getMessage());
        $user = false;
    }

    if ($user) {
        // Intentionally store user in session (password left out for display safety)
        unset($user['password']);
        $_SESSION['user'] = $user;
        flash("Welcome back, " . $user['username']);
        header('Location: dashboard.php');
        exit;
    } else {
        // Intentionally echo submitted username *without escaping* to demonstrate reflected XSS
        // (this is the XSS vulnerability)
        echo "Invalid username or password. You entered: " . $submitted_user;
    }
}

require_once 'header.php';
?>

<div class="card p-4 mb-4" style="max-width:640px;">
  <h4>Login (INTENTIONALLY VULNERABLE)</h4>

  <div class="alert alert-warning">
    <strong>For teaching only:</strong> this page intentionally has <em>no</em> CSRF, is vulnerable to SQL injection,
    and reflects the username unsafely (reflected XSS). Run only on localhost.
  </div>

  <form method="post" class="mt-3">
    <!-- NOTE: no csrf_field() here on purpose -->
    <div class="mb-3">
      <label class="form-label">Username</label>
      <!-- reflected XSS: echoing submitted username without escaping -->
      <input class="form-control" name="username" value="<?php echo $submitted_user; ?>" autocomplete="username">
      <div class="form-text">Try a script like <code>&lt;script&gt;alert('xss')&lt;/script&gt;</code> to see reflected XSS.</div>
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <input class="form-control" name="password" type="password" autocomplete="current-password">
    </div>

    <button class="btn btn-danger">Login (vulnerable)</button>
  </form>
</div>

<?php require_once 'footer.php'; ?>

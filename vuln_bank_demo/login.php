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
        echo '<div class="p-3 text-red-700 text-xl text-center bg-white" >Invalid username or password.'. 'You entered: ' . $submitted_user .'</div>';
    }
}

require_once 'header.php';
?>
<div class="max-w-md ms-9">
  <div class="bg-white/70 shadow-xl rounded-2xl p-6 mb-4 border border-gray-100 pb-9">
    <div class="text-center text-8xl mb-3">🏦</div>
    <h4 class="text-center text-2xl font-bold tracking-tight text-gray-800">
      VulnBank Login 
    </h4>

    
    <form method="post" class="mt-3 space-y-5 p-3">
      <!-- NOTE: no csrf_field() here on purpose -->

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">
          Username
        </label>

        <!-- reflected XSS: echoing submitted username without escaping -->
        <input
          name="username"
          value="<?php echo $submitted_user; ?>"
          autocomplete="username"
          placeholder=""
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
          class="mb-4 w-full rounded-lg border border-gray-300 bg-gray-50/70 px-3 py-2.5
                 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200
                 outline-none transition"
        >
      </div>

      <button
        class="w-full bg-red-600 hover:bg-red-700 active:scale-[0.99]
               text-white font-semibold py-2.5 rounded-lg
               shadow-sm transition mb-5">
        Login
      </button>
      <p class="text-center">Do not have an account? <a href="./register.php"><b>Register</b></a></p>
    </form>
  </div>
</div>

<?php require_once 'footer.php'; ?>

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
<div class="max-w-md ms-9">
  <div class="bg-white/70 shadow-xl rounded-2xl p-6 mb-4 border border-gray-100 pb-9">
    <div class="text-center text-8xl mb-3">📝</div>
    <h4 class="text-center text-2xl font-bold tracking-tight text-gray-800">
      Register
    </h4>

    <form method="post" class="mt-1 space-y-5 p-3">
      <?php echo csrf_field(); ?>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">
          Username
        </label>
        <input
          name="username"
          autocomplete="username"
          placeholder=""
          required
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
          autocomplete="new-password"
          required
          class="mb-3 w-full rounded-lg border border-gray-300 bg-gray-50/70 px-3 py-2.5
                 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200
                 outline-none transition"
        >
      </div>

      <button
        type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 active:scale-[0.99]
               text-white font-semibold py-2.5 rounded-lg
               shadow-sm transition mb-5"
      >
        Register
      </button>
      <p class="text-center">Already have an account? <a href="./login.php"><b>Login</b></a></p>
    </form>
  </div>
</div>

<?php require_once 'footer.php'; ?>

#!/usr/bin/env bash
# create_vuln_bank_attractive.sh
# Creates an attractive vulnerable bank demo for LOCAL testing only.
set -euo pipefail

TARGET_DIR="${1:-/opt/lampp/htdocs/vuln_bank_demo}"
echo "Target: $TARGET_DIR"
mkdir -p "$TARGET_DIR"

# 1) init_sql.sql
cat > "$TARGET_DIR/init_sql.sql" <<'SQL'
CREATE DATABASE IF NOT EXISTS bank_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bank_demo;

DROP TABLE IF EXISTS feedbacks;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  balance DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  from_user INT,
  to_user INT,
  amount DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (from_user) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (to_user) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE feedbacks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
SQL

# 2) run_init.php
cat > "$TARGET_DIR/run_init.php" <<'PHP'
<?php
// run_init.php - initialize DB for demo (RUN LOCALLY ONCE), then delete this file!
require_once 'config.php';
try {
    $pdoRoot = new PDO("mysql:host=127.0.0.1;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS bank_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo = get_pdo();
    $pdo->exec("DROP TABLE IF EXISTS feedbacks;");
    $pdo->exec("DROP TABLE IF EXISTS transactions;");
    $pdo->exec("DROP TABLE IF EXISTS users;");
    $schema = file_get_contents(__DIR__ . '/init_sql.sql');
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    foreach ($statements as $s) {
        if ($s === '') continue;
        $pdo->exec($s . ';');
    }
    $users = [
        ['admin','admin123','admin',10000.00],
        ['alice','alice123','user',5000.00],
        ['bob','bob123','user',3000.00]
    ];
    $stmt = $pdo->prepare("INSERT INTO users (username,password,role,balance) VALUES (?, ?, ?, ?)");
    foreach($users as $u){
        $stmt->execute([$u[0], $u[1], $u[2], $u[3]]);
    }
    echo "<h3>Bank DB initialized.</h3>";
    echo "<p>Users created: <strong>admin / alice / bob</strong></p>";
    echo "<p>Passwords (plaintext for demo): <strong>admin123 / alice123 / bob123</strong></p>";
    echo "<p><strong>IMPORTANT:</strong> Delete run_init.php after use.</p>";
} catch(Exception $e){
    echo "<pre>Error: " . htmlspecialchars($e->getMessage()) . "</pre>";
}
PHP

# 3) config.php
cat > "$TARGET_DIR/config.php" <<'PHP'
<?php
session_start();
$DB_HOST = '127.0.0.1';
$DB_NAME = 'bank_demo';
$DB_USER = 'root';
$DB_PASS = '';

function get_pdo(){
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;
    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
    try {
        return new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    } catch (PDOException $e) {
        die("DB connection failed: " . htmlspecialchars($e->getMessage()));
    }
}
function current_user(){ return $_SESSION['user'] ?? null; }
function require_login(){ if(!current_user()){ header('Location: login.php'); exit; } }
function require_admin(){ $u=current_user(); if(!$u || $u['role']!=='admin'){ http_response_code(403); echo "403 Forbidden"; exit; } }
PHP

# 4) functions.php (CSRF, flash)
cat > "$TARGET_DIR/functions.php" <<'PHP'
<?php
require_once 'config.php';
function flash($msg=null){
    if ($msg === null) { $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m; }
    $_SESSION['flash'] = $msg;
}
function csrf_token(){ if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(20)); } return $_SESSION['csrf_token']; }
function csrf_field(){ $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); return "<input type='hidden' name='csrf_token' value='$t'>"; }
function validate_csrf(){ if ($_SERVER['REQUEST_METHOD'] === 'POST') { $sent = $_POST['csrf_token'] ?? ''; if (!hash_equals($_SESSION['csrf_token'] ?? '', $sent)) { http_response_code(400); die("Invalid CSRF token"); } } }
PHP

# 5) header.php (attractive)
cat > "$TARGET_DIR/header.php" <<'PHP'
<?php
require_once 'functions.php';
$u = current_user();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>VulnBank — Demo</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body { font-family: 'Nunito', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
  .bg-primary-gradient { background: linear-gradient(90deg, #2b6cb0 0%, #2c5282 100%); }
  .card-hero { border-radius: 14px; box-shadow: 0 6px 20px rgba(20,20,50,0.08); }
  .sidebar { min-height: 70vh; }
  .nav-link.active { background: rgba(255,255,255,0.06); border-radius: 8px; }
  .balance-amount { font-weight:700; font-size:1.9rem; }
  .small-muted { color: #6c757d; font-size:0.9rem; }
</style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary-gradient shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <i class="bi bi-bank2 me-2" style="font-size:1.4rem;"></i>
      <span style="font-weight:700;">VulnBank</span>
    </a>
    <div class="d-flex align-items-center">
      <?php if($u): ?>
        <div class="text-white me-3 small-muted">Signed in as <strong><?php echo htmlspecialchars($u['username']); ?></strong></div>
        <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="btn btn-outline-light btn-sm me-2" href="login.php">Login</a>
        <a class="btn btn-light btn-sm" href="register.php">Register</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container my-4">
<?php if($m=flash()): ?><div class="alert alert-info rounded-3"><?php echo htmlspecialchars($m); ?></div><?php endif; ?>
<div class="row">
  <div class="col-lg-3 mb-3">
    <div class="card sidebar p-3">
      <ul class="nav flex-column">
        <li class="nav-item mb-2"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo 'active'; ?>" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
        <li class="nav-item mb-2"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='transfer.php') echo 'active'; ?>" href="transfer.php"><i class="bi bi-arrow-right-square me-2"></i> Transfer</a></li>
        <li class="nav-item mb-2"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='transactions.php') echo 'active'; ?>" href="transactions.php"><i class="bi bi-clock-history me-2"></i> Transactions</a></li>
        <li class="nav-item mb-2"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='feedback.php') echo 'active'; ?>" href="feedback.php"><i class="bi bi-chat-left-text me-2"></i> Feedback</a></li>
        <?php if($u && $u['role']==='admin'): ?>
        <li class="nav-item mb-2"><a class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='admin.php') echo 'active'; ?>" href="admin.php"><i class="bi bi-person-gear me-2"></i> Admin</a></li>
        <?php endif; ?>
        <li class="nav-item mt-3"><a class="nav-link text-danger" href="legacy_vuln.php"><i class="bi bi-exclamation-triangle me-2"></i> Legacy Vulnerable</a></li>
      </ul>
      <div class="mt-4 small text-muted">
        <div>Demo only — local use</div>
      </div>
    </div>
  </div>
  <div class="col-lg-9">
PHP

# 6) footer.php (attractive)
cat > "$TARGET_DIR/footer.php" <<'PHP'
  </div> <!-- col -->
</div> <!-- row -->
</div> <!-- container -->
<footer class="mt-5 py-4 bg-white shadow-sm">
  <div class="container text-center small text-muted">
    VulnBank — educational demo. Remove after use. &middot; Do not expose to the internet.
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
PHP

# 7) index.php (hero)
cat > "$TARGET_DIR/index.php" <<'PHP'
<?php
require_once 'header.php';
?>
<div class="card card-hero p-4 mb-4">
  <div class="d-flex align-items-center">
    <div class="me-4">
      <i class="bi bi-bank2" style="font-size:3.2rem;color:#2b6cb0;"></i>
    </div>
    <div>
      <h2 style="margin-bottom:0;">VulnBank — Security training demo</h2>
      <p class="small-muted mb-0">Realistic banking UI with intentional vulnerabilities for local OWASP training. Use only in a controlled environment.</p>
    </div>
    <div class="ms-auto text-end">
      <a class="btn btn-outline-primary" href="run_init.php">Initialize DB</a>
    </div>
  </div>
</div>

<div class="row gy-3">
  <div class="col-md-6">
    <div class="card p-3 h-100">
      <h5><i class="bi bi-shield-lock me-2 text-primary"></i> Teaching Goals</h5>
      <ul>
        <li>Demonstrate OWASP Top 10 vulnerabilities</li>
        <li>Show attacks safely on localhost</li>
        <li>Explain mitigation and secure coding</li>
      </ul>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3 h-100">
      <h5><i class="bi bi-gear me-2 text-primary"></i> Quick Tips</h5>
      <p class="mb-0 small-muted">After you run <strong>run_init.php</strong>, delete it. Do not leave legacy vulnerable pages exposed.</p>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>
PHP

# 8) register.php (same logic as before, styled)
cat > "$TARGET_DIR/register.php" <<'PHP'
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
PHP

# 9) login.php (styled)
cat > "$TARGET_DIR/login.php" <<'PHP'
<?php
require_once 'functions.php';
validate_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT id, username, password, role, balance FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && $password === $user['password']) {
        unset($user['password']);
        $_SESSION['user'] = $user;
        flash("Welcome back, " . $user['username']);
        header('Location: dashboard.php'); exit;
    } else {
        flash("Invalid username or password.");
    }
}
require_once 'header.php';
?>
<div class="card p-4 mb-4">
  <h4>Login</h4>
  <form method="post" class="mt-3" style="max-width:480px;">
    <?php echo csrf_field(); ?>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input class="form-control" name="username" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input class="form-control" name="password" type="password" required>
    </div>
    <button class="btn btn-primary">Login</button>
  </form>
</div>
<?php require_once 'footer.php'; ?>
PHP

# 10) logout.php
cat > "$TARGET_DIR/logout.php" <<'PHP'
<?php
require_once 'config.php';
session_unset();
session_destroy();
session_start();
flash("You have been logged out.");
header('Location: index.php'); exit;
PHP

# 11) dashboard.php (attractive)
cat > "$TARGET_DIR/dashboard.php" <<'PHP'
<?php
require_once 'functions.php';
require_login();
$pdo = get_pdo();
$user = current_user();
$stmt = $pdo->prepare("SELECT id, username, role, balance FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$user = $stmt->fetch();
$_SESSION['user'] = $user;
require_once 'header.php';
?>
<div class="row mb-3">
  <div class="col-md-6">
    <div class="card card-hero p-4 mb-3">
      <div class="d-flex align-items-center">
        <div class="me-3">
          <i class="bi bi-wallet2" style="font-size:2.6rem;color:#2b6cb0;"></i>
        </div>
        <div>
          <div class="small-muted">Available Balance</div>
          <div class="balance-amount">$<?php echo number_format($user['balance'],2); ?></div>
          <div class="mt-2">
            <a href="transfer.php" class="btn btn-primary btn-sm rounded-pill"><i class="bi bi-arrow-right-square me-1"></i> Transfer</a>
            <a href="transactions.php" class="btn btn-outline-secondary btn-sm rounded-pill ms-2"><i class="bi bi-clock-history me-1"></i> History</a>
          </div>
        </div>
      </div>
    </div>

    <div class="card p-3">
      <h6 class="mb-2">Quick Actions</h6>
      <div class="d-flex gap-2">
        <button class="btn btn-light btn-sm"><i class="bi bi-file-earmark-text me-1"></i> Pay Bills</button>
        <button class="btn btn-light btn-sm"><i class="bi bi-credit-card-2-front me-1"></i> Cards</button>
        <button class="btn btn-light btn-sm"><i class="bi bi-piggy-bank me-1"></i> Save</button>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-3">
      <h6>Recent Transactions</h6>
      <?php
      $stmt = $pdo->prepare("SELECT t.*, u1.username AS from_name, u2.username AS to_name
        FROM transactions t
        LEFT JOIN users u1 ON u1.id = t.from_user
        LEFT JOIN users u2 ON u2.id = t.to_user
        WHERE t.from_user = ? OR t.to_user = ?
        ORDER BY t.created_at DESC LIMIT 6");
      $stmt->execute([$user['id'], $user['id']]);
      $rows = $stmt->fetchAll();
      ?>
      <?php if ($rows): ?>
      <div class="table-responsive mt-2">
        <table class="table table-hover">
          <thead class="table-light"><tr><th>When</th><th>From</th><th>To</th><th>Amount</th></tr></thead>
          <tbody>
          <?php foreach($rows as $r): ?>
            <tr>
              <td><?php echo $r['created_at']; ?></td>
              <td><?php echo htmlspecialchars($r['from_name']); ?></td>
              <td><?php echo htmlspecialchars($r['to_name']); ?></td>
              <td>$<?php echo number_format($r['amount'],2); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
        <p class="text-muted mt-2">No recent transactions.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require_once 'footer.php'; ?>
PHP

# 12) transfer.php (left visually same but styled)
cat > "$TARGET_DIR/transfer.php" <<'PHP'
<?php
require_once 'functions.php';
require_login();
$pdo = get_pdo();
$user = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // intentionally demonstrating CSRF vulnerability by not calling validate_csrf()
    $to_user = intval($_POST['to_user'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    if ($amount <= 0) {
        flash("Invalid amount.");
    } elseif ($to_user === $user['id']) {
        flash("Cannot transfer to yourself.");
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$user['id']]);
            $balance = $stmt->fetchColumn();
            if ($balance < $amount) throw new Exception("Insufficient funds.");
            $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$amount, $user['id']]);
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$amount, $to_user]);
            $stmt = $pdo->prepare("INSERT INTO transactions (from_user, to_user, amount) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $to_user, $amount]);
            $pdo->commit();
            flash("Transferred $" . number_format($amount,2) . " successfully.");
            header('Location: dashboard.php'); exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            flash("Transfer failed: " . $e->getMessage());
        }
    }
}
$others = $pdo->query("SELECT id, username FROM users WHERE id != " . (int)$user['id'])->fetchAll();
require_once 'header.php';
?>
<div class="card p-4 mb-4">
  <h4>Transfer Funds</h4>
  <p class="text-danger"><strong>Vulnerable demo:</strong> this endpoint intentionally does not validate CSRF tokens server-side.</p>
  <form method="post" class="mt-3" style="max-width:540px;">
    <!-- No csrf_field() here to demonstrate CSRF -->
    <div class="mb-3">
      <label class="form-label">Recipient</label>
      <select name="to_user" class="form-select">
        <?php foreach($others as $o): ?>
          <option value="<?php echo $o['id']; ?>"><?php echo htmlspecialchars($o['username']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Amount</label>
      <input type="number" step="0.01" name="amount" class="form-control">
    </div>
    <button class="btn btn-primary">Transfer</button>
  </form>
</div>
<?php require_once 'footer.php'; ?>
PHP

# 13) transactions.php (IDOR demo)
cat > "$TARGET_DIR/transactions.php" <<'PHP'
<?php
require_once 'functions.php';
require_login();
$pdo = get_pdo();
$me = current_user();
$view_user = intval($_GET['user_id'] ?? $me['id']);
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
$stmt->execute([$view_user]);
$view = $stmt->fetch();
require_once 'header.php';
?>
<div class="card p-4 mb-4">
  <h4>Transactions for <?php echo htmlspecialchars($view['username'] ?? 'Unknown'); ?></h4>
  <p class="text-warning">IDOR demo: change <code>?user_id=</code> to view other users' transactions.</p>
  <?php
  $stmt = $pdo->prepare("SELECT t.*, u1.username AS from_name, u2.username AS to_name
      FROM transactions t
      LEFT JOIN users u1 ON u1.id = t.from_user
      LEFT JOIN users u2 ON u2.id = t.to_user
      WHERE t.from_user = ? OR t.to_user = ?
      ORDER BY t.created_at DESC");
  $stmt->execute([$view_user, $view_user]);
  $rows = $stmt->fetchAll();
  ?>
  <?php if ($rows): ?>
    <div class="table-responsive mt-2">
      <table class="table table-hover">
        <thead class="table-light"><tr><th>When</th><th>From</th><th>To</th><th>Amount</th></tr></thead>
        <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?php echo $r['created_at']; ?></td>
            <td><?php echo htmlspecialchars($r['from_name']); ?></td>
            <td><?php echo htmlspecialchars($r['to_name']); ?></td>
            <td>$<?php echo number_format($r['amount'],2); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-muted mt-2">No transactions found.</p>
  <?php endif; ?>
</div>
<?php require_once 'footer.php'; ?>
PHP

# 14) admin.php (SQLi demo, styled)
cat > "$TARGET_DIR/admin.php" <<'PHP'
<?php
require_once 'functions.php';
require_admin();
$pdo = get_pdo();
$search = $_GET['q'] ?? '';
$sql = "SELECT id, username, role, balance FROM users";
if ($search !== '') {
    $sql .= " WHERE username LIKE '%" . $search . "%'"; // intentionally vulnerable
}
$sql .= " ORDER BY id";
$rows = $pdo->query($sql)->fetchAll();
require_once 'header.php';
?>
<div class="card p-4 mb-4">
  <h4>Admin Panel</h4>
  <p class="text-danger">This admin search is intentionally vulnerable to SQL injection for demonstration. Do not expose publicly.</p>
  <form class="mb-3">
    <div class="input-group" style="max-width:540px;">
      <input name="q" class="form-control" placeholder="Search username" value="<?php echo htmlspecialchars($search); ?>">
      <button class="btn btn-outline-primary">Search</button>
    </div>
  </form>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead class="table-light"><tr><th>ID</th><th>Username</th><th>Role</th><th>Balance</th></tr></thead>
      <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><?php echo htmlspecialchars($r['username']); ?></td>
          <td><?php echo $r['role']; ?></td>
          <td>$<?php echo number_format($r['balance'],2); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once 'footer.php'; ?>
PHP

# 15) legacy_vuln.php (reflected XSS + SQLi demo)
cat > "$TARGET_DIR/legacy_vuln.php" <<'PHP'
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
  <p>Try <code>?msg=&lt;script&gt;alert('xss')&lt;/script&gt;</code></p>
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
PHP

# 16) feedback.php (stored XSS demo, styled)
cat > "$TARGET_DIR/feedback.php" <<'PHP'
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
PHP

# finalize permissions
chmod -R 644 "$TARGET_DIR"/*.php "$TARGET_DIR"/*.sql || true
chmod 755 "$TARGET_DIR" || true

cat <<'MSG'

Done — attractive vulnerable bank demo created at:
  TARGET = '"$TARGET_DIR"'

Next steps:
1) Move to your XAMPP htdocs if needed (or run script with target inside htdocs).
2) Start Apache & MySQL.
3) In your browser open:
   http://localhost/vuln_bank_demo/run_init.php
   (Run it ONCE to seed DB. Then DELETE run_init.php!)
4) Open:
   http://localhost/vuln_bank_demo/index.php

Seeded accounts:
  admin / admin123
  alice / alice123
  bob / bob123

VERY IMPORTANT:
- This site is intentionally insecure. Do NOT expose to the internet.
- After your demo, remove the folder:
  sudo rm -rf '"$TARGET_DIR"'

MSG

echo "Script complete."

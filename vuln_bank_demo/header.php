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
        <li class="nav-item mt-3"><a class="nav-link text-danger" href="legacy_vuln.php"><i class="bi bi-search me-2"></i>Search</a></li>
      </ul>
      <div class="mt-4 small text-muted">
        <div>Demo only — local use</div>
      </div>
    </div>
  </div>
  <div class="col-lg-9">

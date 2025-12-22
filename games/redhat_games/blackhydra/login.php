<?php
session_start();

$host = "185.27.134.219";
$user = "if0_40458442";
$pass = "RDiPd0EClzD3H"; $db="undercover_game";
$conn = new mysqli($host,$user,$pass,$db);

if(isset($_POST['login'])){
    $u = $_POST['username'];
    $p = $_POST['password'];

    $result = $conn->query("SELECT * FROM admin WHERE username='$u' AND password='$p'");
    if($result->num_rows > 0){
        $_SESSION['admin'] = true;
        header("Location: admin_panel.php");
        exit;
    }
    $error = "Invalid credentials.";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{ background:#0d0d0d; color:#dcdcdc; font-family:Consolas; }
.box{ background:#111; width:400px; margin:100px auto; padding:25px; border:1px solid #333; border-radius:8px; }
h2{ color:#00ff99; text-shadow:0 0 8px #00ff99; }
.btn-custom{ background:#00cc66; color:#000; }
.btn-custom:hover{ background:#00ff88; }
</style>
</head>

<body>
<div class="box">
<h2 class="text-center">Admin Login</h2>

<?php if(isset($error)): ?>
<p class="text-danger"><?= $error ?></p>
<?php endif; ?>

<form method="POST">
<input class="form-control mt-3" name="username" placeholder="Username">
<input class="form-control mt-3" type="password" name="password" placeholder="Password">
<button class="btn btn-custom w-100 mt-3" name="login">Login</button>
</form>

<p class="mt-3 text-center">
<a href="index.php" class="text-secondary">← Back to Story</a>
</p>
</div>
</body>
</html>

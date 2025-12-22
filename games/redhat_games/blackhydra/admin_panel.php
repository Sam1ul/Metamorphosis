<?php
session_start();
if(!isset($_SESSION['admin'])) { die("ACCESS DENIED"); }

$host = "185.27.134.219";
$user = "if0_40458442";
$pass = "RDiPd0EClzD3H"; $db="undercover_game";
$conn = new mysqli($host,$user,$pass,$db);

/* ---------------- ADD / UPDATE DEALER ---------------- */
if(isset($_POST['save_dealer'])){
    $codename = $_POST['codename'];
    $real_name = $_POST['real_name'];
    $phone = $_POST['phone'];
    $region = $_POST['region'];
    $rank = $_POST['rank'];

    if(isset($_POST['id']) && $_POST['id']!=""){
        $id = $_POST['id'];
        $conn->query("UPDATE dealers SET codename='$codename', real_name='$real_name', phone='$phone', region='$region', rank='$rank' WHERE id=$id");
    } else {
        $conn->query("INSERT INTO dealers (codename, real_name, phone, region, rank) VALUES ('$codename','$real_name','$phone','$region','$rank')");
    }
}

/* ---------------- ADD / UPDATE EVENT ---------------- */
if(isset($_POST['save_event'])){
    $dealer_id = $_POST['dealer_id'];
    $desc = $_POST['event_description'];
    $amount = $_POST['amount'];

    if(isset($_POST['id']) && $_POST['id']!=""){
        $id = $_POST['id'];
        $conn->query("UPDATE events SET dealer_id='$dealer_id', event_description='$desc', amount='$amount' WHERE id=$id");
    } else {
        $conn->query("INSERT INTO events (dealer_id,event_description,amount) VALUES ('$dealer_id','$desc','$amount')");
    }
}

/* ---------------- DELETE ---------------- */
if(isset($_GET['delete'])){
    $type = $_GET['type'];
    $id = $_GET['id'];
    if($type=="dealer"){
        $conn->query("DELETE FROM dealers WHERE id=$id");
    }
    if($type=="event"){
        $conn->query("DELETE FROM events WHERE id=$id");
    }
}

/* ---------------- FETCH DATA ---------------- */
$dealers = $conn->query("SELECT * FROM dealers ORDER BY rank DESC");
$events = $conn->query("SELECT events.*, dealers.codename FROM events JOIN dealers ON dealers.id=events.dealer_id ORDER BY events.event_date DESC");

/* ---------------- FETCH SINGLE RECORDS FOR EDIT ---------------- */
$edit_dealer = null;
$edit_event = null;
if(isset($_GET['edit_dealer'])){
    $id = $_GET['edit_dealer'];
    $edit_dealer = $conn->query("SELECT * FROM dealers WHERE id=$id")->fetch_assoc();
}
if(isset($_GET['edit_event'])){
    $id = $_GET['edit_event'];
    $edit_event = $conn->query("SELECT * FROM events WHERE id=$id")->fetch_assoc();
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Black Hydra Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#0d0d0d; color:#dcdcdc; font-family:Consolas; }
.box { background:#111; border:1px solid #333; padding:20px; border-radius:8px; margin-top:20px; }
table td, table th { color:#ccc; }
h2 { color:#00ff99; text-shadow:0 0 8px #00ff99; }
.btn-custom { background:#00cc66; color:#000; font-weight:bold; }
.btn-custom:hover { background:#00ff88; }
a.btn-sm { padding:3px 8px !important; }
</style>
</head>
<body>
<div class="container">
<h2 class="text-center">BLACK HYDRA – ADMIN PANEL</h2>

<!-- ADD / EDIT DEALER -->
<div class="box">
<h4><?= $edit_dealer ? "Edit Dealer" : "Add Dealer" ?></h4>
<form method="POST">
<input type="hidden" name="id" value="<?= $edit_dealer['id'] ?? "" ?>">
<div class="row">
<div class="col-md-2"><input class="form-control" name="codename" placeholder="Codename" value="<?= $edit_dealer['codename'] ?? "" ?>" required></div>
<div class="col-md-3"><input class="form-control" name="real_name" placeholder="Real Name" value="<?= $edit_dealer['real_name'] ?? "" ?>" required></div>
<div class="col-md-2"><input class="form-control" name="phone" placeholder="Phone" value="<?= $edit_dealer['phone'] ?? "" ?>" required></div>
<div class="col-md-3"><input class="form-control" name="region" placeholder="Region" value="<?= $edit_dealer['region'] ?? "" ?>" required></div>
<div class="col-md-2"><input class="form-control" name="rank" placeholder="Rank" value="<?= $edit_dealer['rank'] ?? "" ?>" required></div>
</div>
<button class="btn btn-custom mt-3" name="save_dealer"><?= $edit_dealer ? "Update Dealer" : "Add Dealer" ?></button>
<?php if($edit_dealer): ?><a href="admin_panel.php" class="btn btn-secondary mt-3">Cancel</a><?php endif; ?>
</form>
</div>

<!-- DEALER LIST -->
<div class="box">
<h4>Dealer List</h4>
<table class="table table-dark table-bordered table-sm">
<tr><th>ID</th><th>Codename</th><th>Real Name</th><th>Phone</th><th>Region</th><th>Rank</th><th>Actions</th></tr>
<?php while($d=$dealers->fetch_assoc()): ?>
<tr>
<td><?= $d['id'] ?></td>
<td><?= $d['codename'] ?></td>
<td><?= $d['real_name'] ?></td>
<td><?= $d['phone'] ?></td>
<td><?= $d['region'] ?></td>
<td><?= $d['rank'] ?></td>
<td>
<a href="?edit_dealer=<?= $d['id'] ?>" class="btn btn-info btn-sm">Edit</a>
<a href="?delete&type=dealer&id=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this dealer and all related events?');">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

<!-- ADD / EDIT EVENT -->
<div class="box">
<h4><?= $edit_event ? "Edit Event" : "Add Event" ?></h4>
<form method="POST">
<input type="hidden" name="id" value="<?= $edit_event['id'] ?? "" ?>">
<div class="row">
<div class="col-md-3">
<select class="form-control" name="dealer_id" required>
<option value="">Select Dealer</option>
<?php
$res = $conn->query("SELECT * FROM dealers");
while($d=$res->fetch_assoc()): ?>
    <option value="<?= $d['id'] ?>" <?= ($edit_event && $edit_event['dealer_id']==$d['id'])?"selected":"" ?>>
    <?= $d['codename'] ?>
    </option>
    <?php endwhile; ?>
    </select>
    </div>
    <div class="col-md-6"><input class="form-control" name="event_description" placeholder="Event Description" value="<?= $edit_event['event_description'] ?? "" ?>" required></div>
    <div class="col-md-3"><input class="form-control" name="amount" placeholder="Amount" type="number" value="<?= $edit_event['amount'] ?? "" ?>" required></div>
    </div>
    <button class="btn btn-custom mt-3" name="save_event"><?= $edit_event ? "Update Event" : "Add Event" ?></button>
    <?php if($edit_event): ?><a href="admin_panel.php" class="btn btn-secondary mt-3">Cancel</a><?php endif; ?>
    </form>
    </div>

    <!-- EVENTS TABLE -->
    <div class="box">
    <h4>Event Log</h4>
    <table class="table table-dark table-bordered table-sm">
    <tr><th>ID</th><th>Dealer</th><th>Description</th><th>Amount</th><th>Date</th><th>Actions</th></tr>
    <?php while($e=$events->fetch_assoc()): ?>
    <tr>
    <td><?= $e['id'] ?></td>
    <td><?= $e['codename'] ?></td>
    <td><?= $e['event_description'] ?></td>
    <td>$<?= $e['amount'] ?></td>
    <td><?= $e['event_date'] ?></td>
    <td>
    <a href="?edit_event=<?= $e['id'] ?>" class="btn btn-info btn-sm">Edit</a>
    <a href="?delete&type=event&id=<?= $e['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this event?');">Delete</a>
    </td>
    </tr>
    <?php endwhile; ?>
    </table>
    </div>

    </div>
    </body>
    </html>

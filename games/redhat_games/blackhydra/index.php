<?php
/* -------------------------
 *   DATABASE INITIALIZATION
 * ---------------------------*/

$host = "185.27.134.219";
$user = "if0_40458442";
$pass = "RDiPd0EClzD3H";

// Connect to MySQL (no DB yet)
$conn = new mysqli($host, $user, $pass);

// Check DB exists
$db_exists = $conn->query("SHOW DATABASES LIKE 'undercover_game'");

if($db_exists->num_rows === 0) {

    /* CREATE DATABASE */
    $conn->query("CREATE DATABASE undercover_game");
    $conn->select_db("undercover_game");

    /* CREATE TABLES */
    $conn->query("
    CREATE TABLE admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50),
                 password VARCHAR(50)
    )
    ");

    $conn->query("INSERT INTO admin (username, password) VALUES ('admin','1234')");

    /* DEALERS WITH REAL NAMES + PHONE */
    $conn->query("
    CREATE TABLE dealers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codename VARCHAR(50),
                 real_name VARCHAR(100),
                 phone VARCHAR(30),
                 region VARCHAR(50),
                 rank VARCHAR(50)
    )
    ");

    /* SAMPLE DEALERS */
    $conn->query("
    INSERT INTO dealers (codename, real_name, phone, region, rank) VALUES
    ('Ghost Wolf', 'Marcus Hale', '+1 202-555-0198', 'East Coast', 'Dealer'),
                 ('Razor Fang', 'Adrian Pike', '+1 312-555-0174', 'Midwest', 'Dealer'),
                 ('Iron Serpent', 'Victor Lang', '+1 415-555-0132', 'West Coast', 'Lieutenant'),
                 ('Black Hydra', 'Unknown', '+1 000-000-0000', 'Unknown', 'Ring Leader')
    ");

    /* EVENTS */
    $conn->query("
    CREATE TABLE events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dealer_id INT,
        event_description VARCHAR(255),
                 amount INT,
                 event_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                 FOREIGN KEY (dealer_id) REFERENCES dealers(id) ON DELETE CASCADE
    )
    ");

    /* SAMPLE EVENTS */
    $conn->query("
    INSERT INTO events (dealer_id, event_description, amount)
    VALUES
    (1, 'Ghost Wolf completed a covert delivery run.', 12000),
                 (2, 'Razor Fang exchanged goods with a local contact.', 8000),
                 (3, 'Iron Serpent oversaw a regional network meeting.', 25000),
                 (4, 'Black Hydra issued new encrypted directives.', 0)
    ");

    $db_status = "Database Created Successfully with sample dealers and events.";
}
else {
    $db_status = "Database Already Exists — Ready.";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Operation Black Hydra — Story</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#0d0d0d; color:#dcdcdc; font-family:Consolas; }
.box { background:#111; padding:20px; margin-top:50px; border-radius:10px; border:1px solid #333; }
h1 { color:#00ff99; text-shadow:0 0 10px #00ff99; }
.btn-custom { background:#00cc66; color:#000; font-weight:bold; }
.btn-custom:hover { background:#00ff88; }
</style>
</head>
<body>
<div class="container">
<div class="box">
<h1 class="text-center">OPERATION BLACK HYDRA</h1>
<p class="text-center text-secondary">Cyber‑Intelligence Simulation</p>

<h4 class="text-info">System Status:</h4>
<p><?= $db_status ?></p>

<hr>

<h4 class="text-warning">Mission Briefing</h4>
<p>
You are <strong>Agent Zero</strong>, a newly deployed operative in the Cyber‑Operations Unit.
Your mission is to infiltrate the digital command center of the criminal syndicate known only as <strong>Black Hydra</strong>.
</p>

<blockquote>
<em>“Agent Zero, intel suggests their admin panel has extremely weak security.
This is your chance to infiltrate and expose their hierarchy.”</em> — Agent K
</blockquote>

<hr>

<div class="text-center">
<a href="login.php" class="btn btn-custom btn-lg">▶ Start Mission</a>
</div>
</div>
</div>
</body>
</html>

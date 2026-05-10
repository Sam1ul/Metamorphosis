<?php
// pretend internal only
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
    die("internal only");
}

$mysqli = new mysqli("localhost", "root", "", "bank_demo");

if ($mysqli->connect_error) {
    die("DB connection failed");
}


echo "Welcome Server Admin!\n";
echo "Top Secret Info: Apple is good for health.\n\n";
echo "Top Secret Function Executing in 3...2.......1...Done."
?>

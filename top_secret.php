<?php
// pretend internal only
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
    die("internal only");
}

$mysqli = new mysqli("localhost", "root", "", "bank_demo");

if ($mysqli->connect_error) {
    die("DB connection failed");
}


echo "<pre>";
echo "Internal Operations Dashboard\n";
echo "====================================\n";
echo "Environment        : Production\n";
echo "Application        : Banking Portal\n";
echo "Version            : v2.3.4\n";
echo "Server Hostname    : APP-SRV-01\n";
echo "Internal IP        : 10.0.1.15\n";
echo "Database           : MySQL (Connected)\n";
echo "Cache Service      : Running\n";
echo "Backup Status      : Successful\n";
echo "Build Date         : 2026-07-10\n";
echo "\n";
echo "Restricted: Internal network access only.\n";
echo "</pre>";
?>

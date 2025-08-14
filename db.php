<?php
// db.php
// Database connection settings.

// --- Database Config ---
$servername = "172.31.96.1";
$username = "agentry2";
$password = "";
$dbname = "maru2";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>

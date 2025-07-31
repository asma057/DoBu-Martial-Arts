<?php
$host = 'localhost';      // usually localhost
$user = 'root';           // your DB username
$pass = '';               // your DB password (empty if using XAMPP default)
$dbname = 'dobu_db';      // your database name

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

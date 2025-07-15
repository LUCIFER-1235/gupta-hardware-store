<?php
$host = "localhost";
$user = "root";
$pass = ""; // or your MariaDB password
$db = "gupta_store";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
require_once '../includes/config.php';

?>

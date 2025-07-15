<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect based on user or admin
if (isset($_GET['admin'])) {
    header("Location: ../auth/login.php");
} else {
    header("Location: ../auth/login.php");
}
exit();

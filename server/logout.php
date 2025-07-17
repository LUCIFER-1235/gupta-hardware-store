<?php
session_start();
require_once '../includes/db.php';

// Optional: Sync current cart to DB before logout
if (isset($_SESSION['user_id'], $_SESSION['cart'])) {
    $userId = $_SESSION['user_id'];
    $conn->query("DELETE FROM user_cart WHERE user_id = $userId");

    foreach ($_SESSION['cart'] as $pid => $qty) {
        $conn->query("INSERT INTO user_cart (user_id, product_id, quantity) VALUES ($userId, $pid, $qty)");
    }
}

// Clear session
session_unset();
session_destroy();
header('Location: login.php');
exit();

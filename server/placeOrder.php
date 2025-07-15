<?php
include '../includes/db.php';
include '../includes/csrf.php';

if (!checkCSRF($_POST['csrf_token'])) {
    die("❌ CSRF token mismatch.");
}

session_start();

// Check login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['cart'])) {
    header("Location: ../user/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$cart = $_SESSION['cart'];
$total = 0;
$items = "";

// Fetch product info to build item list
foreach ($cart as $productId => $qty) {
    $sql = "SELECT name, price FROM products WHERE id = $productId";
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        $subtotal = $row['price'] * $qty;
        $total += $subtotal;
        $items .= $row['name'] . " (x$qty) - ₹$subtotal\n";
    }
}

// Insert order
$sql = "INSERT INTO orders (user_id, items, total) VALUES ($userId, '".mysqli_real_escape_string($conn, $items)."', $total)";
if ($conn->query($sql)) {
    unset($_SESSION['cart']);
    $_SESSION['msg'] = "✅ Order placed successfully!";
} else {
    $_SESSION['msg'] = "❌ Order failed: " . $conn->error;
}

header("Location: ../user/cart.php");
exit();

<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "unauthenticated";
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;
$quantity = $_POST['quantity'] ?? 1;

if (!$product_id) {
    echo "invalid";
    exit;
}

// Check if item already in cart
$stmt = $conn->prepare("SELECT id, quantity FROM user_cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update quantity
    $row = $result->fetch_assoc();
    $newQty = $row['quantity'] + $quantity;

    $updateStmt = $conn->prepare("UPDATE user_cart SET quantity = ? WHERE id = ?");
    $updateStmt->bind_param("ii", $newQty, $row['id']);
    $updateStmt->execute();
} else {
    // Insert new item
    $insertStmt = $conn->prepare("INSERT INTO user_cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $insertStmt->bind_param("iii", $user_id, $product_id, $quantity);
    $insertStmt->execute();
}

echo "success";
?>

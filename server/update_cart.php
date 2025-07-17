<?php
session_start();
require '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$userId = $_SESSION['user_id'];
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($productId <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Check if product exists in user's cart
$checkQuery = $conn->prepare("SELECT * FROM user_cart WHERE user_id = ? AND product_id = ?");
$checkQuery->bind_param("ii", $userId, $productId);
$checkQuery->execute();
$result = $checkQuery->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not in cart']);
    exit;
}

// Update quantity
$updateQuery = $conn->prepare("UPDATE user_cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
$updateQuery->bind_param("iii", $quantity, $userId, $productId);
$updateQuery->execute();

// Get updated subtotal and total
$priceQuery = $conn->prepare("
    SELECT 
        (uc.quantity * p.price) AS subtotal,
        (
            SELECT SUM(uc2.quantity * p2.price)
            FROM user_cart uc2
            JOIN products p2 ON uc2.product_id = p2.id
            WHERE uc2.user_id = ?
        ) AS total
    FROM user_cart uc
    JOIN products p ON uc.product_id = p.id
    WHERE uc.user_id = ? AND uc.product_id = ?
");
$priceQuery->bind_param("iii", $userId, $userId, $productId);
$priceQuery->execute();
$priceResult = $priceQuery->get_result();
$row = $priceResult->fetch_assoc();

echo json_encode([
    'success' => true,
    'subtotal' => $row['subtotal'],
    'total' => $row['total']
]);

<?php
session_start();
include '../includes/db.php';
header('Content-Type: application/json');
// ...

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

if ($productId <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// ✅ Use correct table: user_cart
$check = $conn->prepare("SELECT id, quantity FROM user_cart WHERE user_id = ? AND product_id = ?");
$check->bind_param("ii", $userId, $productId);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    // Update existing quantity
    $row = $res->fetch_assoc();
    $newQty = $row['quantity'] + $quantity;

    $update = $conn->prepare("UPDATE user_cart SET quantity = ? WHERE id = ?");
    $update->bind_param("ii", $newQty, $row['id']);
    $update->execute();
} else {
    // Insert new cart item
    $insert = $conn->prepare("INSERT INTO user_cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $insert->bind_param("iii", $userId, $productId, $quantity);
    $insert->execute();
}

echo json_encode([
  "success" => true,
  "message" => "Product added to cart"
]);
exit;
?>
<?php
session_start();
include '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => '❌ Login required']);
  exit;
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
  echo json_encode(['success' => false, 'message' => '❌ CSRF token mismatch']);
  exit;
}

$userId = $_SESSION['user_id'];
$productId = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($productId <= 0 || $quantity <= 0) {
  echo json_encode(['success' => false, 'message' => '❌ Invalid input']);
  exit;
}

// Get product price
$stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
  echo json_encode(['success' => false, 'message' => '❌ Product not found']);
  exit;
}

$totalAmount = $product['price'] * $quantity;

// Insert into orders
$stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, total_amount, status, created_at) VALUES (?, ?, ?, ?, 'Pending', NOW())");
$stmt->bind_param("iiid", $userId, $productId, $quantity, $totalAmount);
$stmt->execute();

echo json_encode(['success' => true]);
exit;
?>

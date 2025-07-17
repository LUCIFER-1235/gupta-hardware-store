<?php
session_start();
require '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Not logged in']);
  exit;
}

$userId = $_SESSION['user_id'];
$productId = $_POST['product_id'] ?? 0;

if (!$productId) {
  echo json_encode(['success' => false, 'message' => 'Product ID missing']);
  exit;
}

$stmt = $conn->prepare("DELETE FROM user_cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();

// Fetch new total after deletion
$totalStmt = $conn->prepare("
  SELECT SUM(c.quantity * p.price) AS total
  FROM user_cart c
  JOIN products p ON c.product_id = p.id
  WHERE c.user_id = ?
");
$totalStmt->bind_param("i", $userId);
$totalStmt->execute();
$totalResult = $totalStmt->get_result()->fetch_assoc();
$total = $totalResult['total'] ?? 0;

echo json_encode(['success' => true, 'total' => $total]);
?>

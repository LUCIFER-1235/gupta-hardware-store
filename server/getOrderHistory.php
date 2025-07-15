<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');
checkAuth();

$userId = $_SESSION['user_id'];

$sql = "SELECT o.id, o.total, o.created_at, GROUP_CONCAT(p.name SEPARATOR ', ') AS items
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = $userId
        GROUP BY o.id
        ORDER BY o.created_at DESC";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'order_id' => $row['id'],
            'items' => $row['items'],
            'total' => $row['total'],
            'date' => $row['created_at']
        ];
    }
}

echo json_encode($data);

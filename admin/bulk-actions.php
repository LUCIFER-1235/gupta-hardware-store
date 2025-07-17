<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

$action = $_POST['action'] ?? '';
$ids = $_POST['product_ids'] ?? [];

if ($action === 'delete_products' && is_array($ids) && count($ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("DELETE FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);

    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Selected products deleted successfully.";
    } else {
        $_SESSION['error'] = "❌ Failed to delete selected products.";
    }

    $stmt->close();
}

header("Location: view-products.php");
exit();
?>
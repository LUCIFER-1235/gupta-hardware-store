<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['product_id'];
    $stock = (int) $_POST['stock'];

    $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
    $stmt->bind_param("ii", $stock, $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Stock updated.";
    } else {
        $_SESSION['error'] = "❌ Failed to update stock.";
    }
}

header("Location: view-products.php");
exit();
?>

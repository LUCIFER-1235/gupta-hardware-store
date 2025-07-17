<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

// Allow only POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ CSRF token check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "⚠ Invalid request (CSRF token mismatch).";
        header("Location: ../admin/view-products.php");
        exit();
    }

    // ✅ Validate inputs
    $id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    $stock = isset($_POST['stock']) ? (int) $_POST['stock'] : -1;

    if ($id <= 0 || $stock < 0) {
        $_SESSION['error'] = "⚠ Invalid product ID or stock quantity.";
        header("Location: ../admin/view-products.php");
        exit();
    }

    // ✅ Perform update
    $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
    $stmt->bind_param("ii", $stock, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Stock updated successfully.";
    } else {
        $_SESSION['error'] = "❌ Failed to update stock.";
    }
} else {
    $_SESSION['error'] = "⚠ Invalid request method.";
}

header("Location: ../admin/view-products.php");
exit();
?>

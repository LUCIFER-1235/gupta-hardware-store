<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// ✅ Ensure admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// ✅ CSRF token validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = "❌ Invalid CSRF token.";
        header("Location: ../admin/view-orders.php");
        exit();
    }

    // ✅ Validate inputs
    $orderId = intval($_POST['order_id']);
    $status = trim($_POST['status']);

    $validStatuses = ['Pending', 'Shipped', 'Delivered', 'Cancelled'];
    if (!in_array($status, $validStatuses)) {
        $_SESSION['message'] = "❌ Invalid status value.";
        header("Location: ../admin/view-orders.php");
        exit();
    }

    // ✅ Prepare and execute update
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $orderId);

    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Order status updated.";
    } else {
        $_SESSION['message'] = "❌ Failed to update order status. Error: " . $stmt->error;
    }

    $stmt->close();
    header("Location: ../admin/view-orders.php");
    exit();
} else {
    header("Location: ../admin/view-orders.php");
    exit();
}
?>

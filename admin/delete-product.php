<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    // ✅ Step 1: Fetch product & image
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($image);
        $stmt->fetch();

        // ✅ Step 2: Delete image file
        $imagePath = "../assets/images/$image";
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }

        // ✅ Step 3: Delete product from DB
        $delStmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $delStmt->bind_param("i", $id);

        if ($delStmt->execute()) {
            $_SESSION['message'] = "✅ Product deleted successfully.";
        } else {
            $_SESSION['error'] = "❌ Failed to delete product.";
        }
        $delStmt->close();
    } else {
        $_SESSION['error'] = "⚠ Product not found.";
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "❌ Invalid product ID.";
}

header("Location: view-products.php");
exit();

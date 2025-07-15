<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';

checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!checkCSRF($_POST['csrf_token'])) {
        die("❌ CSRF token mismatch.");
    }

    $id = intval($_POST['id']);

    // Optional: Delete associated image from /assets/images (if needed)
    $imgResult = $conn->query("SELECT image FROM products WHERE id = $id");
    if ($imgResult && $imgResult->num_rows > 0) {
        $imgRow = $imgResult->fetch_assoc();
        $imgPath = '../assets/images/' . $imgRow['image'];
        if (file_exists($imgPath)) {
            unlink($imgPath); // Delete image file
        }
    }

    $delete = $conn->query("DELETE FROM products WHERE id = $id");

    if ($delete) {
        header("Location: ../admin/dashboard.php?msg=Product+deleted");
    } else {
        die("❌ Failed to delete product: " . $conn->error);
    }
}

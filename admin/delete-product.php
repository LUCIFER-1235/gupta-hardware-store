<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Delete image too if needed (optional step)
    $res = $conn->query("SELECT image FROM products WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $img = $res->fetch_assoc()['image'];
        @unlink("../assets/images/$img"); // Delete image file
    }

    $conn->query("DELETE FROM products WHERE id = $id");
    $_SESSION['message'] = "Product deleted successfully.";
}

header("Location: view-products.php");
exit();

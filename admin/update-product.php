<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

// ✅ CSRF token check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "❌ Invalid CSRF token.";
    header("Location: view-products.php");
    exit();
}


$id = (int) $_POST['id'];
$name = trim($_POST['name']);
$desc = trim($_POST['description']);
$price = floatval($_POST['price']);
$category = trim($_POST['category']);
$trending = isset($_POST['trending']) ? 1 : 0;

// Handle new image upload (if provided)
$imageUpdated = false;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imgName = basename($_FILES['image']['name']);
    $imgTmp = $_FILES['image']['tmp_name'];
    $imgPath = "../assets/images/" . $imgName;

    // Move image
    if (move_uploaded_file($imgTmp, $imgPath)) {
        $imageUpdated = true;
    } else {
        $_SESSION['error'] = "❌ Failed to upload new image.";
        header("Location: edit-product.php?id=$id");
        exit();
    }
}

// Update query
if ($imageUpdated) {
    // If new image uploaded, update image field too
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ?, trending = ?, image = ? WHERE id = ?");
    $stmt->bind_param("ssdsssi", $name, $desc, $price, $category, $trending, $imgName, $id);
} else {
    // Without changing image
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ?, trending = ? WHERE id = ?");
    $stmt->bind_param("ssdssi", $name, $desc, $price, $category, $trending, $id);
}

// Execute update
if ($stmt->execute()) {
    $_SESSION['message'] = "✅ Product updated successfully.";
} else {
    $_SESSION['error'] = "❌ Failed to update product.";
}

$stmt->close();
header("Location: view-products.php");
exit();
?>

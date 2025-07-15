<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';
require_once '../includes/config.php';

checkAdmin();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!checkCSRF($_POST['csrf_token'])) {
        die("❌ CSRF token mismatch.");
    }

    $id = intval($_POST['id']);
    $name = $conn->real_escape_string(trim($_POST['name']));
    $desc = $conn->real_escape_string(trim($_POST['description']));
    $price = floatval($_POST['price']);
    $category = $conn->real_escape_string(trim($_POST['category']));
    $trending = isset($_POST['trending']) ? 1 : 0;

    // Handle optional new image
    $imgSQL = '';
    if (!empty($_FILES['image']['name'])) {
        $img = $_FILES['image'];
        $type = mime_content_type($img['tmp_name']);
        $sizeMB = $img['size'] / (1024 * 1024);

        if (!in_array($type, ALLOWED_IMAGE_TYPES)) {
            die("❌ Invalid image type.");
        }
        if ($sizeMB > MAX_IMAGE_SIZE_MB) {
            die("❌ Image exceeds size limit.");
        }

        $ext = pathinfo($img['name'], PATHINFO_EXTENSION);
        $newName = uniqid() . "." . $ext;
        move_uploaded_file($img['tmp_name'], "../assets/images/$newName");
        $imgSQL = ", image='$newName'";
    }

    $sql = "UPDATE products SET 
        name='$name',
        description='$desc',
        price=$price,
        category='$category',
        trending=$trending
        $imgSQL
        WHERE id=$id";

    if ($conn->query($sql)) {
        header("Location: ../admin/dashboard.php?msg=Product+updated");
    } else {
        die("❌ Failed to update product: " . $conn->error);
    }
}

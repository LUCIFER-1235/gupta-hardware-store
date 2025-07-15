<?php
include '../includes/db.php';
include '../includes/csrf.php';

if (!checkCSRF($_POST['csrf_token'])) {
    die("❌ CSRF token mismatch.");
}

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $cat = mysqli_real_escape_string($conn, $_POST['category']);
    $trend = isset($_POST['trending']) ? 1 : 0;

    $img = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    $path = "../assets/images/" . basename($img);

    if (move_uploaded_file($tmp, $path)) {
        $sql = "INSERT INTO products (name, description, price, image, category, trending)
                VALUES ('$name', '$desc', $price, '$img', '$cat', $trend)";
        if ($conn->query($sql)) {
            $_SESSION['msg'] = "✅ Product added.";
        } else {
            $_SESSION['msg'] = "❌ DB Error: " . $conn->error;
        }
    } else {
        $_SESSION['msg'] = "❌ Image upload failed.";
    }
    header("Location: ../admin/add-product.php");
    exit();
}

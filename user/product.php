<?php
include '../includes/db.php';
session_start();
require_once '../includes/config.php';
if (!isset($_GET['id'])) {
    echo "Product not found.";
    exit();
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM products WHERE id = $id LIMIT 1";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    echo "Product not found.";
    exit();
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($product['name']) ?> – Gupta Store</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/main.js" defer></script>
</head>
<body>

<div class="product-card">
    <img src="../assets/images/<?= $product['image'] ?>" alt="<?= $product['name'] ?>" style="max-width:300px;" />
    <h2><?= htmlspecialchars($product['name']) ?></h2>
    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
    <p><strong>Price:</strong> ₹<?= number_format($product['price'], 2) ?></p>

    <button onclick="addToCart(<?= $product['id'] ?>)">Add to Cart</button>
    <button onclick="buyNow(<?= $product['id'] ?>)">Buy Now</button>
</div>

</body>
</html>

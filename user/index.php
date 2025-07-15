<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Redirect to login if user not logged in or not a regular user
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch trending products
$trending = $conn->query("SELECT * FROM products ORDER BY views DESC LIMIT 10");

// Fetch all products
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #0c0f1a;
      color: #f1f1f1;
      font-family: 'Segoe UI', sans-serif;
    }

    .navbar {
      background-color: #10152b;
    }

    .navbar-brand {
      display: flex;
      align-items: center;
      font-weight: bold;
      font-size: 22px;
      color: #ffffff !important;
    }

    .navbar-brand img {
      height: 40px;
      margin-right: 10px;
    }

    .search-bar input {
      width: 100%;
      padding: 12px;
      border-radius: 6px;
      border: none;
      background-color: #1e2333;
      color: #fff;
    }

    .search-bar input::placeholder {
      color: #aaa;
    }

    h2 {
      color: #00d4ff;
      margin-top: 30px;
    }

    .trending-bar {
      display: flex;
      overflow-x: auto;
      gap: 12px;
      padding: 15px 0;
    }

    .trending-item {
      min-width: 160px;
      background: #1b2136;
      border: 1px solid #2a2f4c;
      padding: 10px;
      border-radius: 8px;
      text-align: center;
      color: #f1f1f1;
    }

    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
    }

    .product-card {
      background: #1b2136;
      border: 1px solid #2a2f4c;
      border-radius: 8px;
      padding: 15px;
      text-align: center;
      color: #f1f1f1;
    }

    .product-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 6px;
    }

    .product-card h3 {
      font-size: 18px;
      margin: 10px 0;
    }

    .product-card .price {
      color: #00ff9d;
      font-weight: bold;
      font-size: 16px;
    }

    .product-card p {
      color: #ccc;
      font-size: 14px;
    }

    .product-card button {
      margin: 5px;
      background-color: #0076b8;
      color: white;
      border: none;
      padding: 8px 14px;
      border-radius: 5px;
      transition: 0.2s;
    }

    .product-card button:hover {
      background-color: #005e94;
    }

    .no-products {
      color: #999;
      text-align: center;
      margin-top: 40px;
    }
  </style>
</head>
<body>

<nav class="navbar px-4 d-flex justify-content-between align-items-center">
  <a class="navbar-brand" href="#">
    <img src="../assets/images/GSH Logo.jpg" alt="Logo">
    <?= APP_NAME ?>
  </a>

  <?php if (isset($_SESSION['user_name'])): ?>
    <div>
      <span class="text-light me-3">Welcome, <?= $_SESSION['user_name'] ?></span>
      <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  <?php endif; ?>
</nav>

<div class="container py-4">
  <div class="search-bar mb-4">
    <input type="text" placeholder="Search products...">
  </div>

  <?php if ($trending && $trending->num_rows > 0): ?>
    <h2>🔥 Trending Products</h2>
    <div class="trending-bar">
      <?php while ($t = $trending->fetch_assoc()): ?>
        <div class="trending-item">
          <strong><?= htmlspecialchars($t['name']) ?></strong><br>
          ₹<?= number_format($t['price']) ?>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>

  <h2>🛒 All Products</h2>
  <?php if ($products && $products->num_rows > 0): ?>
    <div class="product-grid">
      <?php while ($p = $products->fetch_assoc()): ?>
        <div class="product-card">
          <img src="../assets/images/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
          <h3><?= htmlspecialchars($p['name']) ?></h3>
          <div class="price">₹<?= number_format($p['price']) ?></div>
          <p><?= htmlspecialchars(substr($p['description'], 0, 60)) ?>...</p>

          <form method="POST" action="../server/addToCart.php" style="display:inline;">
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
            <button type="submit">Add to Cart</button>
          </form>

          <form method="POST" action="../server/placeOrder.php" style="display:inline;">
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
            <button type="submit">Buy Now</button>
          </form>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="no-products">🚫 No products available at the moment.</div>
  <?php endif; ?>
</div>

</body>
</html>

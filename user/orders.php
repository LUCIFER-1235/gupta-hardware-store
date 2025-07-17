<?php
include '../includes/db.php';
include '../includes/auth.php';
checkAuth();

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT 
        o.id AS order_id,
        o.total,
        o.created_at,
        p.name AS product_name,
        oi.quantity
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC, o.id, p.name
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[$row['order_id']]['items'][] = [
        'product' => $row['product_name'],
        'quantity' => $row['quantity']
    ];
    $orders[$row['order_id']]['total'] = $row['total'];
    $orders[$row['order_id']]['created_at'] = $row['created_at'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Orders – Gupta Sanitary & Hardware Store</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
body {
  background-color: #0c0f1a;
  color: #f1f1f1;
  font-family: 'Segoe UI', sans-serif;
}

nav {
  background-color: #10152b;
  border-bottom: 1px solid #2a2f4c;
}

nav .nav-link, nav .navbar-brand {
  color: #ffffff !important;
}
nav .nav-link:hover {
  color: #00d4ff !important;
}

h2 {
  color: #00d4ff;
  margin: 30px 0;
}

.order-card {
  background-color: #151c30;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 25px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.5);
  border-left: 5px solid #00d4ff;
  transition: transform 0.2s ease;
}
.order-card:hover {
  transform: scale(1.01);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  border-bottom: 1px solid #2c334d;
  padding-bottom: 10px;
}

.order-products {
  margin-top: 10px;
}
.order-product {
  display: flex;
  justify-content: space-between;
  padding: 6px 0;
  border-bottom: 1px dashed #2a2f4c;
}
.order-product:last-child {
  border-bottom: none;
}

.order-total {
  text-align: right;
  font-size: 1.1rem;
  margin-top: 10px;
  color: #00ffa1;
}

.no-orders {
  background-color: #1b2136;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
  color: #ccc;
  font-style: italic;
}
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container-fluid px-4 py-2">
    <a class="navbar-brand d-flex align-items-center fw-bold" href="index.php">
      <img src="../assets/images/GSH Logo.jpg" alt="Logo" height="40" class="me-2">
      Gupta Sanitary & Hardware Store
    </a>
    <div class="collapse navbar-collapse justify-content-end">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item"><a class="nav-link" href="index.php">🏠 Home</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">🛒 Cart</a></li>
        <li class="nav-item"><a class="nav-link" href="orders.php">📦 My Orders</a></li>
        <li class="nav-item"><a class="nav-link text-danger fw-bold" href="../auth/logout.php">🚪 Logout</a></li>
        <li class="nav-item ms-3">
          <span class="text-light">👋 Welcome, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h2>📦 Your Order History</h2>

  <?php if (!empty($orders)): ?>
    <?php foreach ($orders as $orderId => $order): ?>
      <div class="order-card">
        <div class="order-header">
          <div><strong>Order #<?= $orderId ?></strong></div>
          <div><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
        </div>

        <div class="order-products">
          <?php foreach ($order['items'] as $item): ?>
            <div class="order-product">
              <div><?= htmlspecialchars($item['product']) ?></div>
              <div>Qty: <?= $item['quantity'] ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="order-total">Total: ₹<?= number_format($order['total'], 2) ?></div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="no-orders">🚫 You haven't placed any orders yet.</div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

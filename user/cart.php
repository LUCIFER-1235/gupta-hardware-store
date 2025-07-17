<?php
session_start();
require '../includes/db.php';
require '../includes/csrf.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$userId = $_SESSION['user_id'];
$cartItems = [];
$total = 0;

$sql = "SELECT c.product_id, c.quantity AS qty, p.name, p.price, (c.quantity * p.price) AS subtotal
        FROM user_cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
  $cartItems[] = $row;
  $total += $row['subtotal'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>🛒 My Cart - Gupta Store</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>.fade-transition { transition: all 0.3s ease-in-out; }
      body { background-color: #0c0f1a; color: #f1f1f1; font-family: 'Segoe UI', sans-serif; }
.navbar { background-color: #10152b; padding: 1rem; }
    .navbar-brand img { height: 40px; margin-right: 10px; }
   .navbar {
  background-color: #10152b !important;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}
.nav-link {
  color: #e2e2e2 !important;
  transition: color 0.3s;
}
.nav-link:hover {
  color: #00e6ff !important;
}
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg" style="background-color: #10152b;">
  <div class="container-fluid px-4 py-2">
    <a class="navbar-brand d-flex align-items-center text-white fw-bold" href="index.php">
      <img src="../assets/images/GSH Logo.jpg" alt="Logo" height="40" class="me-2">
      Gupta Sanitary & Hardware Store
    </a>
    <div class="collapse navbar-collapse justify-content-end">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item"><a class="nav-link text-white" href="index.php">🏠 Home</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="cart.php">🛒 Cart</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="orders.php">📦 My Orders</a></li>
        <li class="nav-item"><a class="nav-link text-danger fw-bold" href="../auth/logout.php">🚪 Logout</a></li>
        <li class="nav-item ms-3">
          <span class="text-light">👋 Welcome, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">
  <?php if (count($cartItems) === 0): ?>
    <div class="alert alert-warning text-center">🛒 Your cart is empty.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered text-white align-middle fade-transition">
        <thead class="table-dark">
          <tr>
            <th>Product</th>
            <th width="120px">Quantity</th>
            <th>Update</th>
            <th>Price</th>
            <th>Subtotal</th>
            <th>Remove</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cartItems as $item): ?>
            <tr id="row-<?= $item['product_id'] ?>">
              <td><?= htmlspecialchars($item['name']) ?></td>
              <td>
                <input
                  type="number"
                  class="qty-input form-control text-dark"
                  data-id="<?= $item['product_id'] ?>"
                  value="<?= $item['qty'] ?>"
                  min="1"
                />
              </td>
              <td>
                <button class="btn btn-sm btn-success update-btn" data-id="<?= $item['product_id'] ?>">⟳</button>
              </td>
              <td>₹<?= number_format($item['price'], 2) ?></td>
              <td id="subtotal-<?= $item['product_id'] ?>">₹<?= number_format($item['subtotal'], 2) ?></td>
              <td>
                <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $item['product_id'] ?>">❌</button>
              </td>
            </tr>
          <?php endforeach; ?>
          <tr class="fw-bold table-info text-dark">
            <td colspan="4" class="text-end">Total</td>
            <td colspan="2" id="total-box">₹<?= number_format($total, 2) ?></td>
          </tr>
        </tbody>
      </table>

      <!-- ✅ Submit Order Button + CSRF -->
      <div class="mt-4 text-end">
        <input type="hidden" name="csrf_token" id="csrf_token" value="<?= getCSRFToken() ?>">
        <button id="submitOrderBtn" class="btn btn-primary px-4">🛍️ Place Order</button>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
document.getElementById('submitOrderBtn')?.addEventListener('click', function () {
  if (!confirm('Are you sure you want to place the order?')) return;
  const csrfToken = document.getElementById('csrf_token')?.value || '';

  fetch('../server/placeOrder.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ csrf_token: csrfToken })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('🎉 Order placed successfully!');
      window.location.href = 'orders.php';
    } else {
      alert('⚠️ ' + (data.message || 'Order failed.'));
    }
  })
  .catch(err => {
    console.error(err);
    alert('❌ Something went wrong while placing the order.');
  });
});

document.querySelectorAll('.update-btn').forEach(button => {
  button.addEventListener('click', function () {
    const productId = this.dataset.id;
    const input = document.querySelector(`.qty-input[data-id="${productId}"]`);
    const quantity = parseInt(input.value);

    if (isNaN(quantity) || quantity < 1) {
      input.value = 1;
      alert("⚠️ Quantity must be at least 1.");
      return;
    }

    fetch('../server/update_cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ product_id: productId, quantity: quantity })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        document.getElementById(`subtotal-${productId}`).textContent = `₹${parseFloat(data.subtotal).toFixed(2)}`;
        document.getElementById('total-box').textContent = `₹${parseFloat(data.total).toFixed(2)}`;
      } else {
        alert("❌ " + (data.message || "Failed to update cart."));
      }
    })
    .catch(err => {
      console.error("Error updating cart:", err);
      alert("❌ Something went wrong.");
    });
  });
});

document.querySelectorAll('.delete-btn').forEach(button => {
  button.addEventListener('click', function () {
    const productId = this.dataset.id;
    if (!confirm("🗑️ Are you sure you want to remove this item?")) return;

    fetch('../server/deleteFromCart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ product_id: productId })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const row = document.getElementById(`row-${productId}`);
        if (row) row.remove();
        document.getElementById('total-box').textContent = `₹${parseFloat(data.total).toFixed(2)}`;
        if (parseFloat(data.total) === 0) location.reload();
      } else {
        alert("❌ " + (data.message || "Could not delete product."));
      }
    })
    .catch(() => alert("❌ Something went wrong while deleting the item."));
  });
});
</script>

</body>
</html>

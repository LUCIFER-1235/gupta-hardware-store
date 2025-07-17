<?php
include '../includes/db.php';
include '../includes/auth.php';
checkAuth();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid product ID.";
    exit;
}

$productId = intval($_GET['id']);

$stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Product not found in database for ID: $productId";
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($product['name']) ?> – Product Details</title>
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
} .product-card {
      background-color: #10152b;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.5);
    }
    .product-img {
      border-radius: 10px;
      max-height: 300px;
      object-fit: contain;
    }
    .btn-buy {
      background-color: #00d4ff;
      border: none;
      color: #0c0f1a;
      font-weight: bold;
    }
    .btn-buy:hover {
      background-color: #00b3d4;
    }
    .badge-stock {
      background-color: <?= $product['stock'] > 0 ? '#198754' : '#dc3545' ?>;
    }
  </style>
</head>
<body>

<!-- Navbar -->
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

<!-- Product Detail -->
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-10 product-card d-flex flex-wrap">
      <div class="col-md-5 text-center mb-4">
        <img src="../assets/images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid product-img">
      </div>
      <div class="col-md-7">
        <h2 class="text-info"><?= htmlspecialchars($product['name']) ?></h2>
        <h4 class="text-warning mb-3">₹<?= number_format($product['price'], 2) ?></h4>
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($product['category_name']) ?></p>
        <p><strong>Stock:</strong> <span class="badge badge-stock px-3 py-1"><?= $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?></span></p>

      <!-- Quantity input shared by both buttons -->
<div class="my-3">
  <label for="sharedQty" class="form-label">Quantity:</label>
  <input type="number" id="sharedQty" value="1" min="1" max="<?= $product['stock'] ?>" class="form-control w-25">
</div>
<!-- Add to Cart Form -->
<form id="addToCartForm" action="../server/addToCart.php" method="POST" class="d-inline">
  <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
  <input type="hidden" name="quantity" id="cartQty">
  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
  <button type="submit" class="btn btn-buy">Add to Cart 🛒</button>
</form>

<!-- Buy Now Form -->
<form id="buyNowForm" action="../server/placeOrder.php" method="POST" class="d-inline">
  <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
  <input type="hidden" name="quantity" id="buyQty">
  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
  <button type="submit" class="btn btn-outline-info">Buy Now ⚡</button>
</form>

      </div>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
  <div id="toastCart" class="toast text-bg-success border-0" role="alert" data-bs-delay="3000">
    <div class="d-flex">
      <div class="toast-body">
        Product added to cart!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const sharedQty = document.getElementById('sharedQty');
  const cartForm = document.getElementById('addToCartForm');
  const buyForm = document.getElementById('buyNowForm');

  // ✅ BUY NOW (keep this same)
  buyForm.addEventListener('submit', function (e) {
    document.getElementById('buyQty').value = sharedQty.value;
  });

  // ✅ FIXED: ADD TO CART AJAX
  cartForm.addEventListener('submit', function (e) {
    e.preventDefault();
    document.getElementById('cartQty').value = sharedQty.value;

    const formData = new FormData(cartForm);

    fetch("../server/addToCart.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const toast = new bootstrap.Toast(document.getElementById('toastCart'));
        toast.show();
      } else {
        alert(data.message || "❌ Failed to add to cart.");
      }
    })
    .catch(err => {
      console.error("Error:", err);
      alert("❌ Error adding product to cart.");
    });
  });
</script>


</body>
</html>

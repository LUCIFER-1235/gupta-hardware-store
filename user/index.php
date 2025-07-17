<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$categoryList = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
$categoryArr = [];
while ($c = $categoryList->fetch_assoc()) {
    $categoryArr[] = $c['category'];
}

$trending = $conn->query("SELECT * FROM products ORDER BY views DESC LIMIT 6");

$sql = "SELECT * FROM products WHERE 1";
if (!empty($search)) {
    $esc = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (name LIKE '%$esc%' OR category LIKE '%$esc%' OR brand LIKE '%$esc%')";
}
if (!empty($category)) {
    $escCat = mysqli_real_escape_string($conn, $category);
    $sql .= " AND category = '$escCat'";
}
$sql .= " ORDER BY id DESC";
$products = $conn->query($sql);

$groupedProducts = [];
if ($products && $products->num_rows > 0) {
    while ($row = $products->fetch_assoc()) {
        $groupedProducts[$row['category']][] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
  <title><?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body { background-color: #0c0f1a; color: #f1f1f1; font-family: 'Segoe UI', sans-serif; }
    .navbar { background-color: #10152b; padding: 1rem; }
    .navbar-brand img { height: 40px; margin-right: 10px; }
    .form-control, .form-select { background-color: #1e2333; color: white; border: 1px solid #444; }
    .form-control::placeholder { color: #aaa; }
    .card { background: #1b2136; border: 1px solid #2a2f4c; border-radius: 8px; color: white; }
    .card img { width: 100%; height: 180px; object-fit: cover; border-radius: 6px; }
    .card-title { font-size: 18px; margin: 10px 0; }
    .price { color: #00ff9d; font-weight: bold; }
    .section-title { color: #00d4ff; margin: 40px 0 20px; }
    .tooltip-inner { max-width: 300px; text-align: left; }
    .slider { overflow-x: auto; white-space: nowrap; }
    .slider .card { display: inline-block; width: 260px; margin-right: 15px; }
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

    <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarUser" aria-controls="navbarUser" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarUser">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item">
          <a class="nav-link text-white" href="index.php">🏠 Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="cart.php">🛒 Cart</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="orders.php">📦 My Orders</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-danger fw-bold" href="../auth/logout.php">🚪 Logout</a>
        </li>
        <li class="nav-item ms-3">
          <span class="text-light">👋 Welcome, <strong><?= $_SESSION['user_name'] ?></strong></span>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">
  <form method="GET" id="searchForm" class="row g-3 mb-4">
    <div class="col-md-6">
      <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-4">
      <select name="category" class="form-select">
        <option value="">All Categories</option>
        <?php foreach ($categoryArr as $cat): ?>
          <option value="<?= htmlspecialchars($cat) ?>" <?= $cat === $category ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Search</button>
    </div>
  </form>

  <?php if ($trending && $trending->num_rows > 0): ?>
    <h2 class="section-title">🔥 Trending Products</h2>
    <div class="slider mb-4">
      <?php while ($t = $trending->fetch_assoc()): ?>
        <div class="card" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($t['description']) ?>">
          <img src="../assets/images/<?= htmlspecialchars($t['image']) ?>" alt="<?= htmlspecialchars($t['name']) ?>">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($t['name']) ?></h5>
            <p class="price">₹<?= number_format($t['price']) ?></p>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>

  <?php foreach ($groupedProducts as $cat => $items): ?>
  <h2 class="section-title">📦 <?= htmlspecialchars($cat) ?></h2>
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
    <?php foreach ($items as $p): ?>
      <div class="col">
        <div class="card h-100" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($p['description']) ?>">
          <img src="../assets/images/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="card-img-top" style="object-fit: cover; height: 200px;">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
            <p class="price fw-bold">₹<?= number_format($p['price']) ?></p>
            
<!-- Add to Cart with Quantity (AJAX version) -->
<form action="../server/addToCart.php" method="POST" class="add-to-cart-form">
  <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
  
  <div class="d-flex align-items-center mb-2">
    <label class="me-2 mb-0">Qty:</label>
    <input type="number" name="quantity" value="1" class="form-control qty-input" min="1" >
  </div>

  <button type="submit" class="btn btn-outline-info w-100 mb-2">➕ Add to Cart</button>
<input type="hidden" id="csrf-<?= $product['id'] ?>" value="<?= $_SESSION['csrf_token'] ?>">

  <button type="button" class="btn btn-success w-100 buy-now-btn">💰 Buy Now</button>
  
  <span class="cart-msg text-success fw-bold d-none mt-2"></span>
</form>

            <!-- View Details -->
            <a href="product-detail.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-light mt-2 w-100">View Details</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>

</div>
<script>
  // ✅ Add to Cart Interceptor (No Reload)
  document.addEventListener('submit', function (e) {
    const form = e.target;

    if (form.classList.contains('add-to-cart-form')) {
      e.preventDefault(); // 🔒 Prevent full reload

      const formData = new FormData(form);
      const msgEl = form.querySelector('.cart-msg');

      fetch(form.action, {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        msgEl.textContent = data.success ? '✔️ Added to cart' : '❌ ' + data.message;
        msgEl.classList.remove('d-none', 'text-danger', 'text-success');
        msgEl.classList.add(data.success ? 'text-success' : 'text-danger');

        setTimeout(() => {
          msgEl.classList.add('d-none');
        }, 2000);
      })
      .catch(() => {
        msgEl.textContent = '❌ Server error.';
        msgEl.classList.remove('d-none', 'text-success');
        msgEl.classList.add('text-danger');
      });
    }
  });

  // ✅ Buy Now Handler
  document.querySelectorAll('.buy-now-btn').forEach(button => {
    button.addEventListener('click', function () {
      const productId = this.dataset.id;
      const qtyInput = document.querySelector(`#qty-${productId}`);
      const quantity = parseInt(qtyInput?.value || "1");

      const csrfInput = document.querySelector(`#csrf-${productId}`);
      const csrfToken = csrfInput?.value || '';

      // Debug logs
      console.log("🚀 Buy Now Clicked:", {
        productId,
        quantity,
        csrfToken
      });

      if (!productId || !csrfToken) {
        alert("Missing product or CSRF token.");
        return;
      }

      const formData = new FormData();
      formData.append("product_id", productId);
      formData.append("quantity", quantity);
      formData.append("csrf_token", csrfToken);

      fetch("../server/buyNow.php", {
        method: "POST",
        body: formData,
      })
      .then(res => res.json())
      .then(data => {
        console.log("✅ Buy Now Response:", data);

        if (data.success) {
          window.location.href = "../user/orders.php";
        } else {
          alert("❌ " + data.message);
        }
      })
      .catch(err => {
        console.error("🔥 Buy Now Error:", err);
        alert("Server error. Please try again.");
      });
    });
  });
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


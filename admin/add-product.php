<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $cat = trim($_POST['category']);
    $trend = isset($_POST['trending']) ? 1 : 0;

    $img = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    $path = "../assets/images/" . basename($img);

    if (move_uploaded_file($tmp, $path)) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, category, trending) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssi", $name, $desc, $price, $img, $cat, $trend);

        $success = $stmt->execute() ? "✅ Product added successfully." : "❌ Database error: " . $conn->error;
    } else {
        $error = "❌ Image upload failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product – <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0e1018] text-white min-h-screen">

<!-- ✅ Navbar -->
<nav class="bg-[#10152b] text-white px-6 py-4 shadow flex items-center justify-between sticky top-0 z-50">
  <div class="flex items-center gap-3">
    <img src="../assets/images/GSH Logo.jpg" alt="Logo" class="h-10 rounded-full" />
    <span class="text-xl font-semibold">Gupta Sanitary & Hardware Store <span class="text-cyan-400">Admin</span></span>
  </div>
  <div class="flex gap-6 text-sm font-medium items-center">
    <a href="dashboard.php" class="hover:text-cyan-400">🏠 Dashboard</a>
    <a href="add-product.php" class="hover:text-cyan-400">➕ Add Product</a>
    <a href="view-products.php" class="hover:text-cyan-400">📚 Products</a>
    <a href="view-orders.php" class="hover:text-cyan-400">📦 Orders</a>
    <a href="view-users.php" class="hover:text-cyan-400">👥 Users</a>
    <a href="../auth/logout.php" class="hover:text-red-400">🚪 Logout</a>
  </div>
</nav>

<!-- ✅ Content -->
<div class="max-w-2xl mx-auto px-4 py-10">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">➕ Add New Product</h2>

    <!-- ✅ Alerts -->
    <?php if (isset($success)): ?>
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-4"><?= $error ?></div>
    <?php endif; ?>

    <!-- ✅ Form -->
    <form method="POST" enctype="multipart/form-data" class="bg-[#1a1d2e] p-6 rounded-lg shadow space-y-5">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div>
            <label class="block text-sm font-medium mb-1">Product Name</label>
            <input type="text" name="name" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2" rows="3" required></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Price (₹)</label>
            <input type="number" step="0.01" name="price" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Category</label>
            <input type="text" name="category" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2" required>
        </div>
<form method="POST" action="../server/update-stock.php" class="d-flex">
  <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
  <input type="number" name="stock" value="<?= $p['stock'] ?>" class="form-control me-2" style="width: 80px;">
  <button type="submit" class="btn btn-sm btn-primary">Update</button>
</form>

        <div class="flex items-center">
            <input type="checkbox" name="trending" id="trending" class="mr-2 accent-cyan-400">
            <label for="trending" class="text-sm">Mark as Trending</label>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Upload Image</label>
            <input type="file" name="image" accept="image/*" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2" required>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">Add Product</button>
    </form>
</div>

</body>
</html>

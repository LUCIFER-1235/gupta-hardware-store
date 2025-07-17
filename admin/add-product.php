<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // ✅ CSRF Protection
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $error = "⚠ Invalid CSRF token.";
  } else {
    // ✅ Validate and sanitize input
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);
    $trending = isset($_POST['trending']) ? 1 : 0;
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;

    // ✅ Image validation
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
      $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
      $fileType = mime_content_type($_FILES['image']['tmp_name']);
      $fileName = basename($_FILES['image']['name']);
      $fileName = preg_replace("/[^a-zA-Z0-9_\-\.]/", "_", $fileName);
      $filePath = "../assets/images/" . uniqid() . "_" . $fileName;

      if (!in_array($fileType, $allowedTypes)) {
        $error = "❌ Only JPG, PNG, and WebP files allowed.";
      } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
        $imgName = basename($filePath);

        // ✅ Insert product
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, category, trending, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssii", $name, $desc, $price, $imgName, $category, $trending, $stock);

        if ($stmt->execute()) {
          $success = "✅ Product added successfully.";
        } else {
          $error = "❌ Database error: " . $conn->error;
        }
      } else {
        $error = "❌ Failed to upload image.";
      }
    } else {
      $error = "❌ Image upload is required.";
    }
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
 <div class="flex flex-wrap gap-4 text-sm font-medium justify-center md:justify-end">
  <a href="dashboard.php" class="hover:text-cyan-400 whitespace-nowrap">🏠 Dashboard</a>
  <a href="add-product.php" class="hover:text-cyan-400 whitespace-nowrap">➕ Add Product</a>
  <a href="view-products.php" class="hover:text-cyan-400 whitespace-nowrap">📚 Products</a>
  <a href="view-orders.php" class="hover:text-cyan-400 whitespace-nowrap">📦 Orders</a>
  <a href="view-users.php" class="hover:text-cyan-400 whitespace-nowrap">👥 Users</a>
  
  <a href="../auth/logout.php" class="hover:text-red-400 whitespace-nowrap">🚪 Logout</a>
</div>
</nav>

<!-- ✅ Content -->
<div class="max-w-2xl mx-auto px-4 py-10">
  <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">➕ Add New Product</h2>
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

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Price (₹)</label>
        <input type="number" step="0.01" name="price" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Stock Quantity</label>
        <input type="number" name="stock" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2" min="0" required>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Category</label>
      <input type="text" name="category" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2" required>
    </div>

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

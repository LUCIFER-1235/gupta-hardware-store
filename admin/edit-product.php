<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

// ✅ CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ✅ Validate product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Product ID.");
}

$id = intval($_GET['id']);

// ✅ Get product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Product not found.");
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Product – <?= APP_NAME ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0e1018] text-white">

<!-- ✅ Navbar -->
<nav class="bg-[#10152b] px-6 py-4 shadow flex justify-between items-center sticky top-0 z-50">
  <div class="flex items-center gap-3">
    <img src="../assets/images/GSH Logo.jpg" alt="Logo" class="h-10 w-10 rounded-full">
    <h1 class="text-xl font-bold">Gupta Sanitary & Hardware Store <span class="text-cyan-400">Admin</span></h1>
  </div>
  <div class="flex flex-wrap gap-4 text-sm font-medium">
    <a href="dashboard.php" class="hover:text-cyan-400">🏠 Dashboard</a>
    <a href="add-product.php" class="hover:text-cyan-400">➕ Add Product</a>
    <a href="view-products.php" class="hover:text-cyan-400">📚 Products</a>
    <a href="view-orders.php" class="hover:text-cyan-400">📦 Orders</a>
    <a href="view-users.php" class="hover:text-cyan-400">👥 Users</a>
    <a href="../auth/logout.php" class="hover:text-red-400">🚪 Logout</a>
  </div>
</nav>

<!-- ✅ Form -->
<div class="max-w-xl mx-auto bg-[#181d2f] mt-10 p-8 rounded-lg shadow-lg">
  <h2 class="text-2xl font-bold mb-6 text-cyan-300 flex items-center gap-2">✏️ Edit Product</h2>

  <form action="update-product.php" method="POST" enctype="multipart/form-data" class="space-y-5">
    <input type="hidden" name="id" value="<?= $product['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    <div>
      <label class="block text-sm mb-1">Product Name</label>
      <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required
        class="w-full px-4 py-2 rounded bg-[#0e1525] text-white border border-gray-600 focus:outline-none focus:ring focus:ring-cyan-400">
    </div>

    <div>
      <label class="block text-sm mb-1">Description</label>
      <textarea name="description" rows="3" required
        class="w-full px-4 py-2 rounded bg-[#0e1525] text-white border border-gray-600"><?= htmlspecialchars($product['description']) ?></textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Price (₹)</label>
        <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" required
          class="w-full px-4 py-2 rounded bg-[#0e1525] text-white border border-gray-600">
      </div>
      <div>
        <label class="block text-sm mb-1">Stock</label>
        <input type="number" name="stock" value="<?= htmlspecialchars($product['stock']) ?>" min="0" required
          class="w-full px-4 py-2 rounded bg-[#0e1525] text-white border border-gray-600">
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Category</label>
      <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required
        class="w-full px-4 py-2 rounded bg-[#0e1525] text-white border border-gray-600">
    </div>

    <div class="flex items-center gap-2">
      <input type="checkbox" id="trending" name="trending" <?= $product['trending'] ? 'checked' : '' ?>>
      <label for="trending" class="text-sm text-gray-300">Trending</label>
    </div>

    <div>
      <label class="block text-sm mb-1">Change Image (optional)</label>
      <input type="file" name="image" accept="image/*"
        class="block w-full text-sm text-white file:bg-cyan-700 file:text-white file:rounded file:px-4 file:py-2 file:border-none file:cursor-pointer">
    </div>

    <?php if (!empty($product['image']) && file_exists('../assets/images/' . $product['image'])): ?>
      <div class="mt-4">
        <p class="text-sm text-gray-400 mb-1">Current Image:</p>
        <img src="../assets/images/<?= htmlspecialchars($product['image']) ?>" alt="Product Image"
          class="w-40 rounded border border-gray-700 shadow">
      </div>
    <?php endif; ?>

    <button type="submit"
      class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded transition">Update</button>
  </form>
</div>

</body>
</html>

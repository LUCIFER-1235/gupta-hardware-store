<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Check if product ID exists
if (!isset($_GET['id'])) {
    echo "Invalid Product ID.";
    exit();
}

$id = intval($_GET['id']);

// Get product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Product not found.";
    exit();
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
<nav class="bg-[#10152b] text-white px-6 py-4 shadow flex items-center justify-between sticky top-0 z-50">
  <div class="flex items-center gap-3">
    <img src="../assets/images/GSH Logo.jpg" alt="Logo" class="h-10 w-10 rounded-full">
    <h1 class="text-xl font-bold">Gupta Sanitary & Hardware Store <span class="text-cyan-400">Admin</span></h1>
  </div>
  <div class="flex gap-6 text-sm font-medium">
    <a href="dashboard.php" class="hover:text-cyan-400 flex items-center gap-1">🏠 Dashboard</a>
    <a href="add-product.php" class="hover:text-cyan-400 flex items-center gap-1">➕ Add Product</a>
    <a href="view-products.php" class="hover:text-cyan-400 flex items-center gap-1">📚 View Products</a>
    <a href="view-orders.php" class="hover:text-cyan-400 flex items-center gap-1">📦 Orders</a>
    <a href="view-users.php" class="hover:text-cyan-400 flex items-center gap-1">👥 Users</a>
    <a href="../auth/logout.php" class="hover:text-red-400 flex items-center gap-1">🚪 Logout</a>
  </div>
</nav>

<!-- ✅ Edit Form -->
<div class="max-w-xl mx-auto bg-[#181d2f] mt-10 p-8 rounded-lg shadow-lg">
  <h2 class="text-2xl font-bold mb-6 flex items-center gap-2 text-cyan-300">✏️ Edit Product</h2>

  <form action="update-product.php" method="POST" enctype="multipart/form-data" class="space-y-4">
    <input type="hidden" name="id" value="<?= $product['id'] ?>">

    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" placeholder="Product Name"
           class="w-full px-4 py-2 rounded bg-[#0e1525] text-white border border-gray-600 focus:outline-none focus:ring focus:ring-cyan-400" required>

    <textarea name="description" placeholder="Description"
              class="w-full px-4 py-2 rounded bg-[#0e1525] text-white border border-gray-600 focus:outline-none focus:ring focus:ring-cyan-400"
              rows="3"><?= htmlspecialchars($product['description']) ?></textarea>

    <input type="number" name="price" value="<?= htmlspecialchars($product['price']) ?>" placeholder="Price"
           class="w-full px-4 py-2 rounded bg-[#0e1525] text-white border border-gray-600 focus:outline-none focus:ring focus:ring-cyan-400" required step="0.01">

    <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" placeholder="Category"
           class="w-full px-4 py-2 rounded bg-[#0e1525] text-white border border-gray-600 focus:outline-none focus:ring focus:ring-cyan-400">

    <div class="flex items-center gap-2">
      <input type="checkbox" id="trending" name="trending" <?= $product['trending'] ? 'checked' : '' ?>>
      <label for="trending" class="text-gray-300">Trending</label>
    </div>

    <div>
      <label for="image" class="block text-sm mb-1 text-gray-300">Change Product Image (optional)</label>
      <input type="file" name="image" accept="image/*" class="block w-full text-sm text-white file:bg-cyan-700 file:text-white file:rounded file:px-4 file:py-2 file:border-none file:cursor-pointer">
    </div>

    <?php if (!empty($product['image']) && file_exists('../assets/uploads/' . $product['image'])): ?>
      <div class="mt-4">
        <p class="text-sm text-gray-400 mb-1">Current Image:</p>
        <img src="../assets/uploads/<?= $product['image'] ?>" alt="Product Image" class="w-40 rounded shadow border border-gray-700">
      </div>
    <?php endif; ?>

    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded mt-4">
      Update
    </button>
  </form>
</div>

</body>
</html>

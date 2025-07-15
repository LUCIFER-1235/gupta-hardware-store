<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// ✅ Fetch products
$products = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Products – <?= APP_NAME ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0c0f1a] text-white">

<!-- ✅ Navbar (Direct HTML instead of include) -->
<nav class="bg-[#10152b] text-white px-6 py-4 shadow flex items-center justify-between sticky top-0 z-50">
  <div class="flex items-center gap-3">
    <img src="../assets/images/GSH Logo.jpg" alt="Logo" class="h-10 w-10 rounded-full">
    <h1 class="text-xl font-bold">Gupta Sanitary & Hardware Store <span class="text-cyan-400">Admin</span></h1>
  </div>
  <div class="flex gap-6 text-sm font-medium">
    <a href="dashboard.php" class="hover:text-cyan-400 flex items-center gap-1">🏠 Dashboard</a>
    <a href="add-product.php" class="hover:text-cyan-400 flex items-center gap-1">➕ Add Product</a>
   <a href="view-products.php" class="hover:text-cyan-400">📚 Products</a>
     <a href="view-orders.php" class="hover:text-cyan-400 flex items-center gap-1">📦 Orders</a>
    <a href="view-users.php" class="hover:text-cyan-400 flex items-center gap-1">👥 Users</a>
    <a href="../auth/logout.php" class="hover:text-red-400 flex items-center gap-1">🚪 Logout</a>
  </div>
</nav>

<!-- ✅ Page Content -->
<div class="max-w-7xl mx-auto px-6 py-10">
  <h2 class="text-2xl font-bold text-cyan-300 mb-6 flex items-center gap-2">📦 Manage Products</h2>

  <div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-300">
      <thead class="text-xs uppercase bg-[#1b2136] text-gray-300">
        <tr>
          <th class="px-6 py-3">ID</th>
          <th class="px-6 py-3">Name</th>
          <th class="px-6 py-3">Price</th>
          <th class="px-6 py-3">Category</th>
          <th class="px-6 py-3 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($products->num_rows > 0): ?>
          <?php while ($row = $products->fetch_assoc()): ?>
            <tr class="border-b border-[#2a2f4c] hover:bg-[#131929] transition">
              <td class="px-6 py-4"><?= $row['id'] ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['name']) ?></td>
              <td class="px-6 py-4">₹<?= number_format($row['price'], 2) ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['category']) ?></td>
              <td class="px-6 py-4 text-center flex gap-3 justify-center">
                <a href="edit-product.php?id=<?= $row['id'] ?>" class="bg-yellow-400 hover:bg-yellow-500 text-black px-4 py-1 rounded text-sm font-semibold">Edit</a>
                <a href="delete-product.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this product?')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-1 rounded text-sm font-semibold">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="text-center py-6 text-gray-400">No products found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>

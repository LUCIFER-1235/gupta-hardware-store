<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

// Fetch stats
$productCount = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$orderCount = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];

// Then render dashboard
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard – <?= APP_NAME ?></title>
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
    <a href="view-products.php" class="hover:text-cyan-400 flex items-center gap-1">📚 Products</a>
    <a href="view-orders.php" class="hover:text-cyan-400 flex items-center gap-1">📦 Orders</a>
    <a href="view-users.php" class="hover:text-cyan-400 flex items-center gap-1">👥 Users</a>
    <a href="../auth/logout.php" class="hover:text-red-400 flex items-center gap-1">🚪 Logout</a>
  </div>
</nav>

<!-- ✅ Main Content -->
<div class="max-w-6xl mx-auto p-6 space-y-10">

  <!-- ✅ Stats Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div class="bg-[#181d2f] rounded-lg p-6 text-center shadow-md">
      <h2 class="text-lg font-semibold text-cyan-300 flex items-center justify-center gap-2">📦 Total Products</h2>
      <p class="text-4xl font-bold text-white mt-2"><?= $productCount ?></p>
    </div>

    <div class="bg-[#181d2f] rounded-lg p-6 text-center shadow-md">
      <h2 class="text-lg font-semibold text-yellow-400 flex items-center justify-center gap-2">🧾 Total Orders</h2>
      <p class="text-4xl font-bold text-white mt-2"><?= $orderCount ?></p>
    </div>

    <div class="bg-[#181d2f] rounded-lg p-6 text-center shadow-md">
      <h2 class="text-lg font-semibold text-purple-400 flex items-center justify-center gap-2">👥 Total Users</h2>
      <p class="text-4xl font-bold text-white mt-2"><?= $userCount ?></p>
    </div>
  </div>

  <!-- ✅ Action Buttons -->
  <div class="flex flex-wrap justify-center gap-4 mt-10">
    <a href="add-product.php" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded shadow transition">➕ Add Product</a>
    <a href="view-products.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded shadow transition">📚 View Products</a>
    <a href="view-orders.php" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 px-6 rounded shadow transition">📦 View Orders</a>
    <a href="view-users.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded shadow transition">👥 View Users</a>
  </div>

</div>

</body>
</html>

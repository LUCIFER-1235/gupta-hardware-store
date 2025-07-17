<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!defined('APP_NAME')) define('APP_NAME', 'GSH Store');

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle search and filter
$search = $_GET['search'] ?? '';
$stock_filter = $_GET['stock_filter'] ?? '';

// Base query
$sql = "SELECT * FROM products WHERE 1";

// Search
if (!empty($search)) {
    $search_esc = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (name LIKE '%$search_esc%' OR category LIKE '%$search_esc%' OR brand LIKE '%$search_esc%')";
}

// Filter by stock
if ($stock_filter === 'low') {
    $sql .= " AND stock < 10";
} elseif ($stock_filter === 'out') {
    $sql .= " AND stock = 0";
} elseif ($stock_filter === 'in') {
    $sql .= " AND stock >= 10";
}

$sql .= " ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Products – <?= APP_NAME ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .hover-preview { position: relative; }
    .hover-preview:hover .preview-img { display: block; }
    .preview-img {
      display: none;
      position: absolute;
      top: 100%;
      left: 0;
      z-index: 10;
      padding: 5px;
      background: #0e1325;
      border: 1px solid #444;
      border-radius: 6px;
      width: 150px;
    }
  </style>
</head>
<body class="bg-[#0c0f1a] text-white min-h-screen">

<!-- Navbar -->
<nav class="bg-[#10152b] px-6 py-4 shadow flex items-center justify-between sticky top-0 z-50">
  <div class="flex items-center gap-3">
    <img src="../assets/images/GSH Logo.jpg" alt="Logo" class="h-10 w-10 rounded-full">
    <h1 class="text-xl font-bold">Gupta Sanitary & Hardware Store <span class="text-cyan-400">Admin</span></h1>
  </div>
  <div class="flex gap-6 text-sm font-medium">
    <a href="dashboard.php" class="hover:text-cyan-400">🏠 Dashboard</a>
    <a href="add-product.php" class="hover:text-cyan-400">➕ Add Product</a>
    <a href="view-products.php" class="hover:text-cyan-400">📚 Products</a>
    <a href="view-orders.php" class="hover:text-cyan-400">📦 Orders</a>
    <a href="view-users.php" class="hover:text-cyan-400">👥 Users</a>
    <a href="../auth/logout.php" class="hover:text-red-400">🚪 Logout</a>
  </div>
</nav>

<!-- Page Content -->
<div class="max-w-7xl mx-auto px-6 py-10">
  <h2 class="text-2xl font-bold text-cyan-300 mb-6">📦 Manage Products</h2>

  <!-- Search & Filter -->
  <form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search name/category/brand"
      class="p-2 w-64 bg-gray-800 border border-gray-600 rounded text-white" />
    <select name="stock_filter" class="p-2 bg-gray-800 border border-gray-600 rounded text-white">
      <option value="">All Stock</option>
      <option value="in" <?= $stock_filter === 'in' ? 'selected' : '' ?>>In Stock (≥10)</option>
      <option value="low" <?= $stock_filter === 'low' ? 'selected' : '' ?>>Low Stock (&lt;10)</option>
      <option value="out" <?= $stock_filter === 'out' ? 'selected' : '' ?>>Out of Stock</option>
    </select>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Search</button>
  </form>

  <!-- Product Table -->
  <form method="POST" action="bulk-actions.php" onsubmit="return confirm('Are you sure you want to delete selected products?')">
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-300">
        <thead class="text-xs uppercase bg-[#1b2136] text-gray-300">
          <tr>
            <th class="px-4 py-3"><input type="checkbox" id="selectAll"></th>
            <th class="px-6 py-3">Name</th>
            <th class="px-6 py-3">Price</th>
            <th class="px-6 py-3">Stock</th>
            <th class="px-6 py-3">Category</th>
            <th class="px-6 py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr class="border-b <?= $row['stock'] < 10 ? 'bg-red-950/30' : 'hover:bg-[#131929]' ?> transition">
                <td class="px-4 py-4">
                  <input type="checkbox" name="product_ids[]" value="<?= $row['id'] ?>">
                </td>

                <!-- Name with preview -->
                <td class="px-6 py-4 hover-preview">
                  <?= htmlspecialchars($row['name']) ?>
                  <?php if (!empty($row['image'])): ?>
                    <div class="preview-img">
                      <img src="../assets/images/<?= htmlspecialchars($row['image']) ?>" alt="Image" class="rounded">
                    </div>
                  <?php endif; ?>
                </td>

                <td class="px-6 py-4">₹<?= number_format($row['price'], 2) ?></td>

                <!-- Stock update -->
                <td class="px-6 py-4">
                  <form action="../server/updatestock.php" method="POST" class="flex items-center gap-2">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                    <input type="number" name="stock" value="<?= $row['stock'] ?>" min="0"
                      class="w-20 px-2 py-1 bg-gray-900 border <?= $row['stock'] < 10 ? 'border-red-500 text-red-400' : 'border-gray-600 text-white' ?> rounded text-sm text-center">
                    <button type="submit" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Update</button>
                  </form>
                  <?php if ($row['stock'] < 10): ?>
                    <div class="text-red-400 text-xs mt-1 font-semibold">⚠ Low Stock</div>
                  <?php endif; ?>
                </td>

                <td class="px-6 py-4"><?= htmlspecialchars($row['category']) ?></td>

                <!-- Actions -->
                <td class="px-6 py-4 text-center flex gap-3 justify-center">
                  <a href="edit-product.php?id=<?= $row['id'] ?>" class="bg-yellow-400 hover:bg-yellow-500 text-black px-4 py-1 rounded text-sm font-semibold">Edit</a>
                  <a href="delete-product.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this product?')"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-1 rounded text-sm font-semibold">Delete</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center py-6 text-gray-400">No products found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Bulk Delete Button -->
    <div class="mt-4 text-right">
      <input type="hidden" name="action" value="delete_products">
      <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded">
        🗑️ Delete Selected
      </button>
    </div>
  </form>
</div>

<!-- Select All Script -->
<script>
  document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('input[name="product_ids[]"]').forEach(cb => cb.checked = this.checked);
  });
</script>

</body>
</html>

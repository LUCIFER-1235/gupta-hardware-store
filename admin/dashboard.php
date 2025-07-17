<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

$totalProducts = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$totalRevenue = $conn->query("SELECT IFNULL(SUM(total), 0) FROM orders")->fetch_row()[0];
// Monthly orders (last 6 months)
$monthlyOrders = [];
$monthlyRevenue = [];

for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $start = "$month-01";
    $end = date("Y-m-t", strtotime($start));

    $ordersRes = $conn->query("SELECT COUNT(*) FROM orders WHERE created_at BETWEEN '$start' AND '$end'");
    $revenueRes = $conn->query("SELECT IFNULL(SUM(total), 0) FROM orders WHERE created_at BETWEEN '$start' AND '$end'");

    $monthlyOrders[] = (int) $ordersRes->fetch_row()[0];
    $monthlyRevenue[] = (float) $revenueRes->fetch_row()[0];
    $months[] = date('M', strtotime($month));
}

// Top 5 products
$topProducts = [];
$productRes = $conn->query("  
    SELECT p.name, SUM(oi.quantity) as qty 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    GROUP BY p.id 
    ORDER BY qty DESC 
    LIMIT 5
");

if (!$productRes) {
    die("Query failed: " . $conn->error);
}

while ($row = $productRes->fetch_assoc()) {
    $topProducts[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard – <?= APP_NAME ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-[#0e1018] text-white min-h-screen">

<!-- ✅ Navbar -->
<nav class="bg-[#10152b] text-white px-6 py-4 shadow flex items-center justify-between sticky top-0 z-50">
  <div class="flex items-center gap-3">
    <img src="../assets/images/GSH Logo.jpg" alt="Logo" class="h-10 w-10 rounded-full">
    <h1 class="text-xl font-bold">Gupta Sanitary & Hardware Store <span class="text-cyan-400">Admin</span></h1>
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

<!-- ✅ Main Content -->
<div class="max-w-6xl mx-auto p-6 space-y-10">

  <!-- ✅ Dashboard Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-[#181d2f] rounded-lg p-6 text-center shadow-md">
      <h2 class="text-lg font-semibold text-cyan-300 flex items-center justify-center gap-2">📦 Total Products</h2>
      <p class="text-4xl font-bold text-white mt-2"><?= $totalProducts ?></p>
    </div>

    <div class="bg-[#181d2f] rounded-lg p-6 text-center shadow-md">
      <h2 class="text-lg font-semibold text-yellow-400 flex items-center justify-center gap-2">🧾 Total Orders</h2>
      <p class="text-4xl font-bold text-white mt-2"><?= $totalOrders ?></p>
    </div>

    <div class="bg-[#181d2f] rounded-lg p-6 text-center shadow-md">
      <h2 class="text-lg font-semibold text-purple-400 flex items-center justify-center gap-2">👥 Total Users</h2>
      <p class="text-4xl font-bold text-white mt-2"><?= $totalUsers ?></p>
    </div>

    <div class="bg-[#181d2f] rounded-lg p-6 text-center shadow-md">
      <h2 class="text-lg font-semibold text-green-400 flex items-center justify-center gap-2">💰 Total Revenue</h2>
      <p class="text-4xl font-bold text-white mt-2">₹<?= number_format($totalRevenue, 2) ?></p>
    </div>
  </div>

  <!-- ✅ Action Buttons -->
  <div class="flex flex-wrap justify-center gap-4 mt-10">
    <a href="add-product.php" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded shadow transition">➕ Add Product</a>
    <a href="view-products.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded shadow transition">📚 View Products</a>
    <a href="view-orders.php" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 px-6 rounded shadow transition">📦 View Orders</a>
    <a href="view-users.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded shadow transition">👥 View Users</a>
  </div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-12">

  <!-- Orders Chart -->
  <div class="bg-[#181d2f] rounded-lg p-6 shadow-md">
    <h3 class="text-lg font-semibold mb-4 text-cyan-400">📦 Orders (Last 6 Months)</h3>
    <canvas id="ordersChart" height="150"></canvas>
  </div>

  <!-- Revenue Chart -->
  <div class="bg-[#181d2f] rounded-lg p-6 shadow-md">
    <h3 class="text-lg font-semibold mb-4 text-green-400">💰 Revenue (Last 6 Months)</h3>
    <canvas id="revenueChart" height="150"></canvas>
  </div>

  <!-- Top Products -->
  <div class="bg-[#181d2f] rounded-lg p-6 shadow-md col-span-1 md:col-span-2">
    <h3 class="text-lg font-semibold mb-4 text-yellow-400">🏆 Top 5 Products Sold</h3>
    <canvas id="topProductsChart" height="150"></canvas>
  </div>

</div>

</div>
<script>
const months = <?= json_encode($months) ?>;
const orders = <?= json_encode($monthlyOrders) ?>;
const revenue = <?= json_encode($monthlyRevenue) ?>;
const topProducts = <?= json_encode(array_column($topProducts, 'name')) ?>;
const topQuantities = <?= json_encode(array_column($topProducts, 'qty')) ?>;

// Orders
new Chart(document.getElementById('ordersChart'), {
  type: 'line',
  data: {
    labels: months,
    datasets: [{
      label: 'Orders',
      data: orders,
      backgroundColor: 'rgba(6, 182, 212, 0.3)',
      borderColor: '#06b6d4',
      fill: true,
      tension: 0.4
    }]
  }
});

// Revenue
new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: {
    labels: months,
    datasets: [{
      label: 'Revenue (₹)',
      data: revenue,
      backgroundColor: 'rgba(34,197,94,0.5)',
      borderColor: '#22c55e',
      borderWidth: 1
    }]
  }
});

// Top Products
new Chart(document.getElementById('topProductsChart'), {
  type: 'bar',
  data: {
    labels: topProducts,
    datasets: [{
      label: 'Units Sold',
      data: topQuantities,
      backgroundColor: 'rgba(250,204,21,0.6)',
      borderColor: '#facc15',
      borderWidth: 1
    }]
  }
});

</script>

</body>
</html>
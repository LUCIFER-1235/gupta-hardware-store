<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Build SQL with filters
$sql = "SELECT orders.id, users.name, users.email, orders.items, orders.total, orders.order_date, orders.status
        FROM orders
        JOIN users ON orders.user_id = users.id
        WHERE 1";

if (!empty($search)) {
  $safe = mysqli_real_escape_string($conn, $search);
  $sql .= " AND (
    orders.id LIKE '%$safe%' OR
    users.name LIKE '%$safe%' OR
    users.email LIKE '%$safe%'
  )";
}

if (!empty($status_filter) && $status_filter !== 'all') {
  $status_safe = mysqli_real_escape_string($conn, $status_filter);
  $sql .= " AND orders.status = '$status_safe'";
}

if ($date_filter === 'today') {
  $sql .= " AND DATE(orders.order_date) = CURDATE()";
} elseif ($date_filter === 'week') {
  $sql .= " AND YEARWEEK(orders.order_date, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($date_filter === 'month') {
  $sql .= " AND MONTH(orders.order_date) = MONTH(CURDATE()) AND YEAR(orders.order_date) = YEAR(CURDATE())";
}

$sql .= " ORDER BY orders.order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Orders – <?= APP_NAME ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background-color: #0c0f1a; color: #f1f1f1; font-family: 'Segoe UI', sans-serif; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px 15px; border: 1px solid #2a2f4c; text-align: left; }
    th { background-color: #10152b; color: #00d4ff; font-weight: 600; }
    tr:nth-child(even) { background-color: #12172c; }
    tr:hover { background-color: #232c45; }
  </style>
</head>
<body class="min-h-screen">

<!-- ✅ Navbar -->
<nav class="bg-[#10152b] text-white px-6 py-3 shadow-md flex justify-between items-center sticky top-0 z-50">
  <div class="flex items-center space-x-3">
    <img src="../assets/images/GSH Logo.jpg" alt="Logo" class="h-10 w-10 rounded-full">
    <span class="font-bold text-xl"><?= APP_NAME ?> Admin</span>
  </div>
  <div class="flex flex-wrap gap-4 text-sm font-medium justify-center md:justify-end">
    <a href="dashboard.php" class="hover:text-cyan-400">🏠 Dashboard</a>
    <a href="add-product.php" class="hover:text-cyan-400">➕ Add Product</a>
    <a href="view-products.php" class="hover:text-cyan-400">📚 Products</a>
    <a href="view-orders.php" class="hover:text-cyan-400">📦 Orders</a>
    <a href="view-users.php" class="hover:text-cyan-400">👥 Users</a>
    
    <a href="../auth/logout.php" class="hover:text-red-400">🚪 Logout</a>
  </div>
</nav>

<!-- ✅ Search + Filter Bar -->
<div class="px-6 mt-8">
  <form method="GET" class="flex flex-wrap gap-4 items-center justify-center">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="🔍 Search Order ID, Name, Email"
      class="px-4 py-2 rounded bg-gray-800 border border-gray-600 text-white text-sm w-64">

    <select name="status" class="px-4 py-2 rounded bg-gray-800 border border-gray-600 text-white text-sm">
      <option value="all">All Statuses</option>
      <?php
        $statuses = ['Pending', 'Shipped', 'Delivered', 'Cancelled'];
        foreach ($statuses as $s):
      ?>
      <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>

    <select name="date" class="px-4 py-2 rounded bg-gray-800 border border-gray-600 text-white text-sm">
      <option value="">All Dates</option>
      <option value="today" <?= $date_filter === 'today' ? 'selected' : '' ?>>Today</option>
      <option value="week" <?= $date_filter === 'week' ? 'selected' : '' ?>>This Week</option>
      <option value="month" <?= $date_filter === 'month' ? 'selected' : '' ?>>This Month</option>
    </select>

    <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 px-4 py-2 text-white text-sm rounded">Apply</button>
    <a href="view-orders.php" class="text-sm px-4 py-2 rounded border border-gray-500 text-gray-300 hover:bg-red-900">Reset</a>
  </form>
</div>

<!-- ✅ Order Table -->
<div class="overflow-x-auto px-4">
  <table class="text-sm text-left text-gray-300 w-full">
    <thead class="text-xs uppercase bg-[#1b2136] text-gray-300">
      <tr>
        <th>#</th>
        <th>Customer</th>
        <th>Email</th>
        <th>Items</th>
        <th>Amount (₹)</th>
        <th>Date</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
        <tr class="border-b border-[#2a2f4c] hover:bg-[#131929]">
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= nl2br(htmlspecialchars($row['items'])) ?></td>
          <td>₹<?= number_format($row['total'], 2) ?></td>
          <td><?= date('d M Y, h:i A', strtotime($row['order_date'])) ?></td>
          <td>
            <form action="../server/update-order-status.php" method="POST" class="flex items-center gap-2">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
              <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
              <select name="status" class="px-2 py-1 text-sm rounded bg-gray-900 border border-gray-600 text-white">
                <?php foreach ($statuses as $status): ?>
                  <option value="<?= $status ?>" <?= $row['status'] === $status ? 'selected' : '' ?>>
                    <?= $status ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Update</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

</body>
</html>

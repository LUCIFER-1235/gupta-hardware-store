<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

$sql = "SELECT orders.id, users.name, users.email, orders.items, orders.total, orders.order_date
        FROM orders
        JOIN users ON orders.user_id = users.id
        ORDER BY orders.order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>

    <meta charset="UTF-8">
    <title>All Orders – <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background-color: #0c0f1a;
            color: #f1f1f1;
            font-family: 'Segoe UI', sans-serif;
        }
        h2 {
            color: #00d4ff;
            text-align: center;
            margin: 30px 0 20px;
        }
        table {
            width: 95%;
            margin: 0 auto;
            border-collapse: collapse;
            background-color: #1b2136;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #2a2f4c;
            text-align: left;
        }
        th {
            background-color: #10152b;
            color: #00d4ff;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background-color: #12172c;
        }
        tr:hover {
            background-color: #232c45;
        }
        td {
            color: #e0e0e0;
            vertical-align: top;
        }
    </style>
</head>
<body class="bg-[#0c0f1a] text-white min-h-screen">
    <nav class="bg-[#10152b] text-white px-6 py-3 shadow-md flex justify-between items-center sticky top-0 z-50">
  <div class="flex items-center space-x-3">
    <img src="../assets/images/GSH Logo.jpg" alt="Logo" class="h-10 w-10 rounded-full">
    <span class="font-bold text-xl"><?= APP_NAME ?> Admin</span>
  </div>
  <div class="space-x-4 text-sm">
    <a href="dashboard.php" class="hover:text-cyan-400">🏠 Dashboard</a>
    <a href="add-product.php" class="hover:text-cyan-400">➕ Add Product</a>
    <a href="view-products.php" class="hover:text-cyan-400">📚 Products</a>
    <a href="view-orders.php" class="hover:text-cyan-400">📦 Orders</a>
    <a href="view-users.php" class="hover:text-cyan-400">👥 Users</a>
    <a href="../server/logout.php" class="bg-red-600 px-3 py-1 rounded hover:bg-red-700">Logout</a>
  </div>
</nav>


    <h2>📦 All Orders</h2>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Items Ordered</th>
                <th>Bill Amount (₹)</th>
                <th>Order Date</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['items'])) ?></td>
                    <td><?= number_format($row['total'], 2) ?></td>
                    <td><?= $row['order_date'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>

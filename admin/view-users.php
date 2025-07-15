<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

// Fetch all users
$sql = "SELECT id, name, email, is_admin, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Users – Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background-color: #0c0f1a;
            color: #f1f1f1;
            font-family: 'Segoe UI', sans-serif;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background: #1b2136;
        }
        th, td {
            padding: 12px;
            border: 1px solid #2a2f4c;
            text-align: left;
        }
        th {
            background-color: #10152b;
            color: #00d4ff;
        }
        tr:nth-child(even) {
            background-color: #12172c;
        }
        h2 {
            color: #00d4ff;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body class="bg-[#0c0f1a] text-white min-h-screen">
<!-- admin-navbar.php -->
<!-- admin-navbar.php -->
<!-- Replace your current navbar with this -->
<nav class="bg-[#10152b] text-white px-6 py-3 flex items-center justify-between shadow">
  <!-- Left: Logo + Store name -->
  <div class="flex items-center space-x-3">
    <img src="../assets/images/GSH Logo.jpg" alt="Logo" class="h-10 w-10 rounded-full" />
    <h1 class="text-lg font-semibold text-white">Gupta Sanitary & Hardware Store <span class="text-cyan-400">Admin</span></h1>
  </div>

  <!-- Right: Navigation links -->
  <div class="flex items-center space-x-6 text-sm font-medium">
    <a href="dashboard.php" class="hover:text-cyan-400 flex items-center gap-1">🏠 Dashboard</a>
    <a href="add-product.php" class="hover:text-cyan-400 flex items-center gap-1">➕ Add Product</a>
   <a href="view-products.php" class="hover:text-cyan-400">📚 Products</a>
     <a href="view-orders.php" class="hover:text-cyan-400 flex items-center gap-1">📦 Orders</a>
    <a href="view-users.php" class="hover:text-cyan-400 flex items-center gap-1">👥 Users</a>
    <a href="../auth/logout.php" class="hover:text-red-400 flex items-center gap-1">🚪 Logout</a>
  </div>
</nav>


<h2>Registered Users</h2>
<?php if (isset($_SESSION['message'])): ?>
    <div style="color: lime; text-align: center; margin: 10px;">
        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<table>
    <tr>
        <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Admin?</th>
        <th>Registered On</th>
        <th>Actions</th>
    </tr>
    <?php $i = 1; while ($user = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= ((int)$user['is_admin'] === 1) ? 'Yes' : 'No' ?></td>
            <td><?= $user['created_at'] ?></td>
            <td>
                <form method="POST" action="userActions.php" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <input type="hidden" name="action" value="toggle">
                    <button type="submit" style="padding: 4px 10px; background: #0077ff; color: white; border: none; border-radius: 4px;">Toggle Role</button>
                </form>

                <form method="POST" action="userActions.php" style="display:inline;" onsubmit="return confirm('Are you sure to delete this user?');">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" style="padding: 4px 10px; background: #ff4d4d; color: white; border: none; border-radius: 4px;">Delete</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>


</body>
</html>

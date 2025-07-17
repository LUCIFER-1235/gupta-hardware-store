<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

// Search & Filter Logic
$search = $_GET['search'] ?? '';
$registered = $_GET['registered'] ?? '';

$sql = "SELECT id, name, email, phone, is_admin, created_at FROM users WHERE 1";

// 🔍 Search
if (!empty($search)) {
    $esc = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (name LIKE '%$esc%' OR email LIKE '%$esc%' OR phone LIKE '%$esc%')";
}

// 📅 Registration Date Filter
if ($registered === 'today') {
    $sql .= " AND DATE(created_at) = CURDATE()";
} elseif ($registered === 'week') {
    $sql .= " AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($registered === 'month') {
    $sql .= " AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
}

$sql .= " ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Users – Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0c0f1a] text-white min-h-screen">

<!-- ✅ Navbar -->
<nav class="bg-[#10152b] text-white px-6 py-3 flex items-center justify-between shadow">
    <div class="flex items-center space-x-3">
        <img src="../assets/images/GSH Logo.jpg" alt="Logo" class="h-10 w-10 rounded-full" />
        <h1 class="text-lg font-semibold text-white">Gupta Sanitary & Hardware Store <span class="text-cyan-400">Admin</span></h1>
    </div>
    <div class="flex flex-wrap gap-4 text-sm font-medium justify-center md:justify-end">
        <a href="dashboard.php" class="hover:text-cyan-400">🏠 Dashboard</a>
        <a href="add-product.php" class="hover:text-cyan-400">➕ Add Product</a>
        <a href="view-products.php" class="hover:text-cyan-400">📚 Products</a>
        <a href="view-orders.php" class="hover:text-cyan-400">📦 Orders</a>
        <a href="view-users.php" class="hover:text-cyan-400 text-cyan-400 font-semibold">👥 Users</a>
      
        <a href="../auth/logout.php" class="hover:text-red-400">🚪 Logout</a>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-6 py-10">
    <h2 class="text-2xl font-bold text-cyan-300 mb-6 text-center">👥 Registered Users</h2>

    <!-- ✅ Flash Message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="text-green-400 text-center font-semibold mb-4">
            <?= $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <!-- ✅ Search + Filter Form -->
    <form method="GET" class="flex flex-wrap gap-4 justify-center mb-6">
        <input
            type="text"
            name="search"
            value="<?= htmlspecialchars($search) ?>"
            placeholder="Search by name, email, phone"
            class="px-4 py-2 rounded bg-gray-800 text-white placeholder-gray-400 border border-gray-600 w-64"
        >

        <select name="registered" class="px-4 py-2 rounded bg-gray-800 text-white border border-gray-600">
            <option value="">📆 Registered: All</option>
            <option value="today" <?= $registered === 'today' ? 'selected' : '' ?>>Today</option>
            <option value="week" <?= $registered === 'week' ? 'selected' : '' ?>>This Week</option>
            <option value="month" <?= $registered === 'month' ? 'selected' : '' ?>>This Month</option>
        </select>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded">
            🔍 Search
        </button>
    </form>

    <!-- ✅ Users Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-300 border border-gray-700">
            <thead class="bg-[#1b2136] text-xs uppercase">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Admin?</th>
                    <th class="px-4 py-3">Registered On</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): $i = 1; ?>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr class="border-b border-gray-700 hover:bg-[#131929] transition">
                            <td class="px-4 py-2"><?= $i++ ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($user['name']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                            <td class="px-4 py-2"><?= ((int)$user['is_admin'] === 1) ? 'Yes' : 'No' ?></td>
                            <td class="px-4 py-2"><?= $user['created_at'] ?></td>
                      <td class="px-4 py-2 text-center space-x-2">
    <form method="POST" action="userActions.php" class="inline">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
        <input type="hidden" name="action" value="toggle">
        <button type="submit" class="bg-yellow-400 hover:bg-yellow-500 text-black px-3 py-1 rounded text-xs font-semibold">
            Toggle Role
        </button>
    </form>
    <form method="POST" action="userActions.php" class="inline" onsubmit="return confirm('Delete this user?');">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-semibold">
            Delete
        </button>
    </form>
</td>

                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center py-6 text-gray-400">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

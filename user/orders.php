<?php
include '../includes/db.php';
include '../includes/auth.php';
checkAuth();

$userId = $_SESSION['user_id'];

$sql = "SELECT o.id, o.total, o.created_at, GROUP_CONCAT(p.name SEPARATOR ', ') AS items
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = $userId
        GROUP BY o.id
        ORDER BY o.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Orders – <?= 'Gupta Sanitary & Hardware Store' ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<h2>Your Order History</h2>

<?php if ($result && $result->num_rows > 0): ?>
<table>
    <tr>
        <th>#</th>
        <th>Items</th>
        <th>Total</th>
        <th>Date</th>
    </tr>
    <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $i++ ?></td>
        <td data-label="Product"><?= htmlspecialchars($row['items']) ?></td>
        <td data-label="Total">₹<?= number_format($row['total'], 2) ?></td>
        <td data-label="Date"><?= $row['created_at'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
    <p>You haven't placed any orders yet.</p>
<?php endif; ?>

</body>
</html>

<?php
include '../includes/db.php';
include '../includes/auth.php';
checkAuth();

session_start();
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;
$cartItems = [];

foreach ($cart as $productId => $qty) {
    $sql = "SELECT * FROM products WHERE id = $productId";
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        $row['qty'] = $qty;
        $row['subtotal'] = $row['price'] * $qty;
        $cartItems[] = $row;
        $total += $row['subtotal'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<h2>Your Shopping Cart</h2>

<?php if (isset($_SESSION['msg'])) { echo "<p>" . $_SESSION['msg'] . "</p>"; unset($_SESSION['msg']); } ?>

<?php if (count($cartItems) === 0): ?>
    <p>Your cart is empty.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
        </tr>
        <?php foreach ($cartItems as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= $item['qty'] ?></td>
            <td>₹<?= number_format($item['price'], 2) ?></td>
            <td>₹<?= number_format($item['subtotal'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <th colspan="3">Total</th>
            <th>₹<?= number_format($total, 2) ?></th>
        </tr>
    </table>

    <form method="POST" action="../server/placeOrder.php">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    <button type="submit">Place Order</button>
    </form>
<?php endif; ?>

</body>
</html>

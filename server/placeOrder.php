<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';

checkAuth();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ CSRF Check
$csrf = $_POST['csrf_token'] ?? '';
if (!checkCSRF($csrf)) {
    // If it's AJAX (from cart)
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode(["success" => false, "message" => "❌ CSRF token mismatch."]);
        exit;
    } else {
        die("❌ CSRF token mismatch.");
    }
}

$userId = $_SESSION['user_id'];

// ✅ 1. BUY NOW (product_id and quantity directly posted)
if (isset($_POST['product_id'], $_POST['quantity']) && is_numeric($_POST['product_id']) && is_numeric($_POST['quantity'])) {
    $productId = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Fetch product
    $stmt = $conn->prepare("SELECT name, price, stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        die("❌ Product not found.");
    }

    if ($quantity > $product['stock']) {
        die("❌ Not enough stock available.");
    }

    $total = $product['price'] * $quantity;

    // Insert order
    $orderStmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'Pending')");
    $orderStmt->bind_param("id", $userId, $total);
    $orderStmt->execute();
    $orderId = $orderStmt->insert_id;

    // Insert order item
    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $itemStmt->bind_param("iiid", $orderId, $productId, $quantity, $product['price']);
    $itemStmt->execute();

    // Update stock
    $stockStmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $stockStmt->bind_param("ii", $quantity, $productId);
    $stockStmt->execute();

    // Redirect
    header("Location: ../user/orders.php?success=1");
    exit;
}

// ✅ 2. CART ORDER (AJAX-based)
$cart = [];
$res = $conn->prepare("SELECT product_id, quantity FROM user_cart WHERE user_id = ?");
$res->bind_param("i", $userId);
$res->execute();
$result = $res->get_result();
while ($row = $result->fetch_assoc()) {
    $cart[$row['product_id']] = $row['quantity'];
}
$res->close();

if (count($cart) === 0) {
    echo json_encode(["success" => false, "message" => "🛒 Cart is empty."]);
    exit;
}

$total = 0;
$itemsText = '';
foreach ($cart as $productId => $qty) {
    $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $price = $row['price'];
        $subtotal = $price * $qty;
        $total += $subtotal;
        $itemsText .= "{$row['name']} (x{$qty}) - ₹" . number_format($subtotal, 2) . "\n";
    }
    $stmt->close();
}

// Insert order
$insertOrder = $conn->prepare("INSERT INTO orders (user_id, items, total) VALUES (?, ?, ?)");
$insertOrder->bind_param("isi", $userId, $itemsText, $total);
if (!$insertOrder->execute()) {
    echo json_encode(["success" => false, "message" => "❌ Failed to place order."]);
    exit;
}
$orderId = $insertOrder->insert_id;
$insertOrder->close();

// Insert items
$insertItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
foreach ($cart as $productId => $qty) {
    $insertItem->bind_param("iii", $orderId, $productId, $qty);
    $insertItem->execute();
}
$insertItem->close();

// Clear cart
$clearCart = $conn->prepare("DELETE FROM user_cart WHERE user_id = ?");
$clearCart->bind_param("i", $userId);
$clearCart->execute();
$clearCart->close();

unset($_SESSION['cart']);

echo json_encode([
    "success" => true,
    "message" => "✅ Order placed successfully!",
    "order_id" => $orderId,
    "total" => $total
]);
exit;

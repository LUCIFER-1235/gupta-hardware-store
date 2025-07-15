<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]++;
    } else {
        $_SESSION['cart'][$productId] = 1;
    }

    echo json_encode(["status" => "success", "message" => "Added to cart"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}

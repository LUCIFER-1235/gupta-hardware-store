<?php
session_start();
include '../includes/db.php';
include '../includes/csrf.php';

// CSRF Check
if (!checkCSRF($_POST['csrf_token'])) {
    die("❌ CSRF token mismatch.");
}

// Only allow POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);

    // Validate input
    if (!$name || !$email || !$pass) {
        $_SESSION['error'] = "❌ All fields are required.";
        header("Location: ../user/register.php");
        exit;
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "❌ Email already registered.";
        header("Location: ../user/register.php");
        exit;
    }

    // Hash password
    $hashed = password_hash($pass, PASSWORD_BCRYPT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashed);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "✅ Registration successful. Please login.";
        header("Location: ../user/login.php");
        exit;
    } else {
        $_SESSION['error'] = "❌ Registration failed. Try again.";
        header("Location: ../user/register.php");
        exit;
    }
}

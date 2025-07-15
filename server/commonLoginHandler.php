<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);

    if (empty($email) || empty($pass)) {
        $_SESSION['error'] = "Email and password are required.";
        header("Location: ../auth/login.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
       if (password_verify($pass, $user['password'])) {
    $_SESSION['role'] = $user['role']; // <-- ADD THIS LINE

    if ($user['role'] === 'admin') {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['name'];
        header("Location: ../admin/dashboard.php");
        exit();
    } else if ($user['role'] === 'user') {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: ../user/index.php");
        exit();
    }

        } else {
            $_SESSION['error'] = "Incorrect password.";
        }
    } else {
        $_SESSION['error'] = "No account found with that email.";
    }

    header("Location: ../auth/login.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../auth/login.php");
    exit();
}

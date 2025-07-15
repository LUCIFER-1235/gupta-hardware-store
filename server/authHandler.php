<?php
include '../includes/db.php';
include '../includes/csrf.php';

if (!checkCSRF($_POST['csrf_token'])) {
    die("❌ CSRF token mismatch.");
}

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Use plain-text for now, replace with password_hash() later
        if ($pass === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];

            if ($user['is_admin'] == 1) {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../user/index.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "❌ Incorrect password.";
        }
    } else {
        $_SESSION['error'] = "❌ User not found.";
    }

    header("Location: ../user/login.php");
    exit();
}

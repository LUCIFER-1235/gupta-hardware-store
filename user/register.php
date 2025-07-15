<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once '../includes/config.php';

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Flash messages
$error = $_SESSION['error'] ?? '';
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['error'], $_SESSION['msg']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register – <?= APP_NAME ?></title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    body {
      margin: 0;
      padding: 0;
      background: linear-gradient(145deg, #e0e7ff, #f0f4ff);
      font-family: 'Segoe UI', sans-serif;
    }

    .register-container {
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .register-box {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 15px 25px rgba(0, 0, 50, 0.1);
      width: 100%;
      max-width: 400px;
      animation: fadeIn 0.5s ease;
    }

    .register-box h2 {
      margin: 0 0 20px;
      font-size: 28px;
      text-align: center;
      color: #001f4d;
    }

    .register-box input[type="text"],
    .register-box input[type="email"],
    .register-box input[type="password"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 18px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
    }

    .register-box button {
      width: 100%;
      padding: 12px;
      background-color: #001f4d;
      color: white;
      font-size: 16px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }

    .register-box button:hover {
      background-color: #003366;
    }

    .register-box .brand {
      font-size: 18px;
      margin-bottom: 20px;
      color: #333;
      text-align: center;
      font-weight: 600;
    }

    .flash-msg {
      text-align: center;
      margin-bottom: 10px;
      color: red;
    }

    .flash-success {
      color: green;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to   { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<div class="register-container">
  <form class="register-box" method="POST" action="../server/registerHandler.php">
    <div class="brand"><?= APP_NAME ?></div>
    <h2>Register</h2>

    <?php if ($error): ?>
      <div class="flash-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($msg): ?>
      <div class="flash-msg flash-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    <button type="submit">Create Account</button>

    <div style="margin-top: 15px; text-align: center;">
      Already have an account? <a href="login.php">Login here</a>.
    </div>
  </form>
</div>

</body>
</html>

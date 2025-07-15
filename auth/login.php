<?php
session_start();
require_once '../includes/config.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin' && isset($_SESSION['admin_id'])) {
        header("Location: ../admin/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'user' && isset($_SESSION['user_id'])) {
        header("Location: ../user/index.php");
        exit();
    } else {
        // Session is corrupt or role mismatch
        session_unset();
        session_destroy();
        header("Location: ../auth/login.php?error=corrupt_session");
        exit();
    }
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login – <?= APP_NAME ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0c0f1a] flex items-center justify-center min-h-screen">

  <div class="bg-[#1b2136] shadow-2xl rounded-2xl p-8 w-full max-w-md">
    <div class="flex items-center justify-center mb-6">
      <img src="../assets/images/GSH Logo.jpg" alt="Logo" class="h-12 w-12 rounded-full mr-2">
      <h1 class="text-2xl font-bold text-cyan-400">Login</h1>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-center text-sm">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>

    <form action="../server/commonLoginHandler.php" method="POST" class="space-y-4">
 <input 
  type="email" 
  name="email" 
  placeholder="Email" 
  required
  class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded 
         focus:outline-none focus:ring-2 focus:ring-blue-500 
         focus:bg-gray-100 text-gray-900"
/>
<input 
  type="password" 
  name="password" 
  placeholder="Password" 
  required
  class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded 
         focus:outline-none focus:ring-2 focus:ring-blue-500 
         focus:bg-gray-100 text-gray-900"
/>

      <button type="submit"
              class="w-full bg-blue-600 text-white py-2 font-semibold rounded hover:bg-blue-700 transition">Login</button>
    </form>

    <p class="text-sm text-center text-gray-400 mt-4">
      Don't have an account?
      <a href="../auth/register.php" class="text-cyan-400 hover:underline">Register here</a>
    </p>
  </div>

</body>
</html>

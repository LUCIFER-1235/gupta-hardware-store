<?php
require_once 'config.php';

// 🧠 Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ⏳ Timeout handler (common for all)
function enforceSessionTimeout() {
    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header("Location: ../auth/login.php?timeout=1");
        exit();
    }
    $_SESSION['last_activity'] = time();
}

// ✅ Check if a user is authenticated
function checkAuth() {
    enforceSessionTimeout();

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
        header("Location: ../auth/login.php");
        exit();
    }
}

// ✅ Check if an admin is authenticated
function checkAdmin() {
    enforceSessionTimeout();

    if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../auth/login.php");
        exit();
    }
}

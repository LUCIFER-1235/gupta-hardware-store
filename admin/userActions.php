<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

// CSRF token check (optional but recommended for POST operations)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    $action = $_POST['action'] ?? '';

    if ($userId <= 0) {
        $_SESSION['message'] = "❌ Invalid user ID.";
        header("Location: view-users.php");
        exit();
    }

    if ($action === 'toggle') {
        // Fetch current role
        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();

        if ($user) {
            $newRole = ($user['role'] === 'admin') ? 'user' : 'admin';
            $newIsAdmin = ($newRole === 'admin') ? 1 : 0;

            // Update role and is_admin
            $updateStmt = $conn->prepare("UPDATE users SET role = ?, is_admin = ? WHERE id = ?");
            $updateStmt->bind_param("sii", $newRole, $newIsAdmin, $userId);
            $updateStmt->execute();
            $updateStmt->close();

            $_SESSION['message'] = "✅ User role updated to '{$newRole}'.";
        } else {
            $_SESSION['message'] = "❌ User not found.";
        }

        $stmt->close();
        header("Location: view-users.php");
        exit();
    }

    if ($action === 'delete') {
        // Prevent deleting own account
        if ($userId == $_SESSION['admin_id']) {
            $_SESSION['message'] = "❌ You cannot delete your own admin account.";
            header("Location: view-users.php");
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        $_SESSION['message'] = "🗑️ User deleted successfully.";
        header("Location: view-users.php");
        exit();
    }

    // Unknown action fallback
    $_SESSION['message'] = "⚠️ Unknown action.";
    header("Location: view-users.php");
    exit();
}
?>

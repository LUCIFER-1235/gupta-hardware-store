<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int) $_POST['user_id'];
    $action = $_POST['action'] ?? '';

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

            // Update both role and is_admin
            $updateStmt = $conn->prepare("UPDATE users SET role = ?, is_admin = ? WHERE id = ?");
            $updateStmt->bind_param("sii", $newRole, $newIsAdmin, $userId);
            $updateStmt->execute();
            $updateStmt->close();

            $_SESSION['message'] = "✅ User role toggled successfully.";
        } else {
            $_SESSION['message'] = "❌ User not found.";
        }

        $stmt->close();
        header("Location: view-users.php");
        exit();
    }

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        $_SESSION['message'] = "🗑️ User deleted successfully.";
        header("Location: view-users.php");
        exit();
    }
}
?>

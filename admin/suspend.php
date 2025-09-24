<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}
include("../config/db.php");

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = (int) $_GET['id'];

    // Prevent admin from suspending themselves
    if ($user_id == $_SESSION['user_id']) {
        header("Location: users.php?msg=self_error");
        exit();
    }

    // Get current status
    $result = $conn->query("SELECT status FROM users WHERE user_id=$user_id");
    if ($result && $row = $result->fetch_assoc()) {
        $newStatus = ($row['status'] === 'active') ? 'suspended' : 'active';
        $stmt = $conn->prepare("UPDATE users SET status=? WHERE user_id=?");
        $stmt->bind_param("si", $newStatus, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: users.php?msg=updated");
exit();
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

if (isset($_GET['id']) || !is_numeric($_GET['id'])) {

    $house_id = (int) $_GET['id'];

    // Soft delete →  instead of deleting
    $stmt = $conn->prepare("UPDATE houses SET is_active = 0 WHERE house_id = ?");
    $stmt->bind_param("i", $house_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: manage_house.php?msg=deleted");
exit();

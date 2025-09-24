<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['landlord','agent'])) {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$house_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Delete only if it belongs to this user
$stmt = $conn->prepare("DELETE FROM houses WHERE house_id=? AND user_id=?");
$stmt->bind_param("ii", $house_id, $user_id);

if ($stmt->execute()) {
    // Also delete related images
    $conn->query("DELETE FROM house_images WHERE house_id = $house_id");

    header("Location: my_listings.php");
    exit();
} else {
    die("Error deleting house: " . $conn->error);
}
?>
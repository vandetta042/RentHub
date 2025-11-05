<?php
require_once __DIR__ . '/../config/db.php';
session_start();

$landlord_id = $_SESSION['user_id'] ?? 0;

if ($landlord_id <= 0) {
    die("Access denied. Please log in.");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid payment ID.");
}

$payment_id = (int) $_GET['id'];

// Update only if this payment belongs to the logged-in landlord
$stmt = $conn->prepare("UPDATE payments SET confirmed = 1 WHERE id = ? AND recipient_id = ?");
$stmt->bind_param("ii", $payment_id, $landlord_id);
$stmt->execute();
$stmt->close();

header("Location: landlord_payment.php");
exit;
?>

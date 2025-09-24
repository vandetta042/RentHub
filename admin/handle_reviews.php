<?php
// Include the database connection
session_start();

// Make sure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

// --- Validate Input ---
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    die('Error: Missing parameters.');
}

$review_id = intval($_GET['id']);
$action = $_GET['action'];
$allowed_actions = ['approve', 'flag', 'delete'];

if ($review_id <= 0 || !in_array($action, $allowed_actions)) {
    die('Error: Invalid parameters.');
}

// --- Determine New Status ---
$new_status = '';
$message = '';

switch ($action) {
    case 'approve':
        $new_status = 'active';
        $message = 'Review approved successfully.';
        break;
    case 'flag':
        $new_status = 'flagged';
        $message = 'Review flagged successfully.';
        break;
    case 'delete':
        // We use 'soft delete' by changing the status, as per your schema.
        // If you wanted to permanently delete it, the query would be:
        // $sql = "DELETE FROM reviews WHERE review_id = ?";
        $new_status = 'deleted';
        $message = 'Review deleted successfully.';
        break;
}

// --- Update Database Using Prepared Statements ---
if ($new_status) {
    $stmt = $conn->prepare("UPDATE reviews SET status = ? WHERE review_id = ?");

    $stmt->bind_param('si', $new_status, $review_id);

    if ($stmt->execute()) {
        // Redirect back to the reviews page with a success message
        header('Location: reviews.php?message=' . urlencode($message));
        exit();
    } else {
        die('Database update failed: ' . $stmt->error);
    }

    $stmt->close();
}

$conn->close();

// Fallback redirect in case something goes wrong
header('Location: reviews.php');
exit();

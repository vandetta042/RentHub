<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

$userId = intval($_SESSION['user_id']);
$notifications = [];

// 1️⃣ Fetch notifications safely
$stmt = $conn->prepare("
    SELECT id, content, link, created_at, is_read
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
");
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC); // fetch all at once
$stmt->close();

// 2️⃣ Mark unread notifications as read
$updateStmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
if ($updateStmt) {
    $updateStmt->bind_param("i", $userId);
    $updateStmt->execute();
    $updateStmt->close();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Your Notifications</title>
</head>

<body>
    <h2>Notifications</h2>
    <ul>
        <?php if (empty($notifications)): ?>
            <p>No notifications.</p>
        <?php else: ?>
            <?php foreach ($notifications as $row): ?>
                <li style="<?php echo ($row['is_read'] == 0) ? 'font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($row['content']); ?>
                    <?php if (!empty($row['link'])): ?>
                        — <a href="<?php echo htmlspecialchars($row['link']); ?>">View</a>
                    <?php endif; ?>
                    <small> (<?php echo htmlspecialchars($row['created_at']); ?>)</small>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</body>

</html>
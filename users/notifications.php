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

<?php include('../includes/header.php'); ?>
<style>
    .notifications-container {
        max-width: 700px;
        margin: 40px auto 0 auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(44, 62, 80, 0.10);
        padding: 36px 32px 28px 32px;
    }

    .notifications-title {
        color: #4a6a93;
        font-size: 2rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: 28px;
        letter-spacing: 1px;
    }

    .notification-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .notification-item {
        background: #f6f7fa;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.04);
        padding: 18px 22px;
        margin-bottom: 18px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        position: relative;
        border-left: 6px solid #4a6a93;
        transition: background 0.18s;
    }

    .notification-item.unread {
        background: #e3eaf3;
        border-left: 6px solid #e74c3c;
    }

    .notification-content {
        font-size: 1.13rem;
        color: #34495e;
        font-weight: 500;
    }

    .notification-link {
        color: #27ae60;
        text-decoration: none;
        font-weight: 500;
        font-size: 1.04rem;
        transition: color 0.18s;
    }

    .notification-link:hover {
        color: #219150;
    }

    .notification-date {
        color: #aaa;
        font-size: 0.98rem;
        align-self: flex-end;
    }

    .no-notifications {
        text-align: center;
        color: #aaa;
        font-size: 1.1rem;
        margin-top: 32px;
    }

    @media (max-width: 600px) {
        .notifications-container {
            padding: 18px 8px;
        }

        .notification-item {
            padding: 12px 8px;
        }
    }
</style>
<div class="notifications-container">
    <div class="notifications-title">Notifications</div>
    <?php if (empty($notifications)): ?>
        <div class="no-notifications">No notifications.</div>
    <?php else: ?>
        <ul class="notification-list">
            <?php foreach ($notifications as $row): ?>
                <li class="notification-item<?php echo ($row['is_read'] == 0) ? ' unread' : ''; ?>">
                    <span class="notification-content"><?php echo htmlspecialchars($row['content']); ?></span>
                    <?php if (!empty($row['link'])): ?>
                        <a href="<?php echo htmlspecialchars($row['link']); ?>" class="notification-link">View</a>
                    <?php endif; ?>
                    <span class="notification-date"><?php echo htmlspecialchars($row['created_at']); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?php include('../includes/footer.php'); ?>
<?php
//
function addNotification($conn, $userId, $content, $link = null)
{
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, content, link, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    if (!$stmt) {
        error_log("Notification prepare failed: " . $conn->error);
        return false;
    }
    $stmt->bind_param("iss", $userId, $content, $link);
    $stmt->execute();
    $stmt->close();
    return true;
}

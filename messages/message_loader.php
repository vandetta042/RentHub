<?php
session_start();
require_once "../config/db.php";

$currentUser = $_SESSION['user_id'];
$otherUserId = intval($_GET['user_id']);
$houseId     = intval($_GET['house_id']);
$afterId     = intval($_GET['after_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT m.*, u.full_name 
    FROM messages m
    JOIN users u ON m.sender_id = u.user_id
    WHERE m.house_id=? AND ((m.sender_id=? AND receiver_id=?) OR (m.sender_id=? AND receiver_id=?)) AND m.message_id > ?
    ORDER BY m.created_at ASC
");
$stmt->bind_param("iiiiii", $houseId, $currentUser, $otherUserId, $otherUserId, $currentUser, $afterId);
$stmt->execute();
$result = $stmt->get_result();

$lastDate = '';
while($row = $result->fetch_assoc()){
    $class = ($row['sender_id'] == $currentUser) ? 'me' : 'other';
    $date = date('Y-m-d', strtotime($row['created_at']));
    if ($date !== $lastDate) {
        echo '<div class="message-date">'.($date === date('Y-m-d') ? 'Today' : $date).'</div>';
        $lastDate = $date;
    }
    $time = date('H:i', strtotime($row['created_at']));
    echo '<div class="message '.$class.'" data-id="'.$row['message_id'].'" data-datetime="'.$row['created_at'].'">';
    echo nl2br(htmlspecialchars($row['content']));
    echo '<span class="time">'.$time.'</span>';
    echo '</div>';
}
$stmt->close();

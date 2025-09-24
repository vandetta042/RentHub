<?php
session_start();
require_once "../config/db.php"; // adjust path if needed

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view your inbox.");
}

$currentUser = $_SESSION['user_id'];

/**
 * Fetch the latest message for each conversation
 * Grouped by (house_id + other user)
 */
$sql = "
    SELECT m.message_id, m.content, m.created_at,
           h.house_id, h.title AS house_title,
           u.user_id AS other_user_id, u.full_name AS other_user_name
    FROM messages m
    INNER JOIN houses h ON m.house_id = h.house_id
    INNER JOIN users u ON u.user_id = 
        CASE 
            WHEN m.sender_id = ? THEN m.receiver_id
            ELSE m.sender_id
        END
    INNER JOIN (
        SELECT house_id,
               LEAST(sender_id, receiver_id) AS user_a,
               GREATEST(sender_id, receiver_id) AS user_b,
               MAX(created_at) AS last_msg
        FROM messages
        WHERE sender_id = ? OR receiver_id = ?
        GROUP BY house_id, user_a, user_b
    ) t 
    ON m.house_id = t.house_id 
       AND LEAST(m.sender_id, m.receiver_id) = t.user_a
       AND GREATEST(m.sender_id, m.receiver_id) = t.user_b
       AND m.created_at = t.last_msg
    ORDER BY m.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $currentUser, $currentUser, $currentUser);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php include("../includes/header.php"); ?>
    <h2>Your Inbox</h2>
<p><a href="../users/dashboard.php">← Back to Dashboard</a></p>
    <?php if ($result->num_rows > 0): ?>
        <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
            <li>
                <strong><?php echo htmlspecialchars($row['other_user_name']); ?></strong>
                about <em><?php echo htmlspecialchars($row['house_title']); ?></em><br>
                <?php echo htmlspecialchars(substr($row['content'], 0, 50)); ?>...
                <br>
                <small><?php echo $row['created_at']; ?></small><br>
                <a href="conversation.php?user_id=<?php echo $row['other_user_id']; ?>&house_id=<?php echo $row['house_id']; ?>">
                    View Conversation
                </a>
                <hr>
            </li>
        <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No conversations yet.</p>
    <?php endif; ?>
<?php include("../includes/footer.php"); ?>
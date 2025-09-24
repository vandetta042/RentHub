<?php
session_start();
require_once "../config/db.php";
require_once "../includes/notification_helper.php";

//  AUTHENTICATION CHECK
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view this page.");
}
$currentUser = $_SESSION['user_id'];
$senderName  = $_SESSION['full_name'] ?? 'Someone';

//  VALIDATE REQUEST
if (!isset($_GET['user_id']) || !isset($_GET['house_id'])) {
    die("Invalid request.");
}
$otherUserId = intval($_GET['user_id']);
$houseId     = intval($_GET['house_id']);

//  HANDLE NEW MESSAGE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $msg = trim($_POST['message']);
    if ($msg !== "") {
        // 1️⃣ Insert new message into database
        $stmt = $conn->prepare("
            INSERT INTO messages (house_id, sender_id, receiver_id, content, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("iiis", $houseId, $currentUser, $otherUserId, $msg);
        $stmt->execute();
        $stmt->close();

        // 2️⃣ Add notification for recipient
        $notificationContent = "You received a new message from $senderName";
        $notificationLink = "../messages/conversation.php?user_id=$currentUser&house_id=$houseId&page=last";
        addNotification($conn, $otherUserId, $notificationContent, $notificationLink);
    }

    // 3️⃣ Redirect to last page to avoid form resubmission
    header("Location: conversation.php?user_id=$otherUserId&house_id=$houseId&page=last");
    exit();
}

//  FETCH HOUSE INFO
$houseStmt = $conn->prepare("SELECT title FROM houses WHERE house_id=?");
$houseStmt->bind_param("i", $houseId);
$houseStmt->execute();
$house = $houseStmt->get_result()->fetch_assoc();
$houseStmt->close();

// PAGINATION SETUP
$limit = 10;

// Count total messages
$countStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM messages
    WHERE house_id = ? 
      AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
");
$countStmt->bind_param("iiiii", $houseId, $currentUser, $otherUserId, $otherUserId, $currentUser);
$countStmt->execute();
$totalMessages = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$totalPages = max(1, ceil($totalMessages / $limit));
$page = isset($_GET['page']) && $_GET['page'] === "last" ? $totalPages : (isset($_GET['page']) ? max(1, intval($_GET['page'])) : $totalPages);
$offset = ($page - 1) * $limit;

// FETCH PAGINATED MESSAGES
$stmt = $conn->prepare("
    SELECT m.*, u.full_name 
    FROM messages m
    JOIN users u ON m.sender_id = u.user_id
    WHERE m.house_id = ? 
      AND ((m.sender_id = ? AND receiver_id = ?) OR (m.sender_id = ? AND receiver_id = ?))
    ORDER BY m.created_at ASC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iiiiiii", $houseId, $currentUser, $otherUserId, $otherUserId, $currentUser, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC); // fetch all messages
$stmt->close(); // ✅ important to prevent "commands out of sync"

// Mark messages as read
$updateStmt = $conn->prepare("
    UPDATE messages 
    SET is_read = 1
    WHERE receiver_id = ? AND house_id = ? AND sender_id = ?
");
$updateStmt->bind_param("iii", $currentUser, $houseId, $otherUserId);
$updateStmt->execute();
$updateStmt->close();
?>

<?php include("../includes/header.php"); ?>
<h2>Conversation about: <?php echo htmlspecialchars($house['title']); ?></h2>
<a href="inbox.php">← Back to Inbox</a><br><br>
<p><a href="../users/dashboard.php">← Back to Dashboard</a></p>

<div>
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $row): ?>
            <div class="msg <?php echo ($row['sender_id'] == $currentUser) ? 'me' : 'other'; ?>">
                <strong><?php echo htmlspecialchars($row['full_name']); ?></strong><br>
                <?php echo nl2br(htmlspecialchars($row['content'])); ?><br>
                <small><?php echo $row['created_at']; ?></small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No messages yet.</p>
    <?php endif; ?>
</div>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="conversation.php?user_id=<?php echo $otherUserId; ?>&house_id=<?php echo $houseId; ?>&page=<?php echo $page - 1; ?>">⬅ Prev</a>
    <?php endif; ?>
    Page <?php echo $page; ?> of <?php echo $totalPages; ?>
    <?php if ($page < $totalPages): ?>
        <a href="conversation.php?user_id=<?php echo $otherUserId; ?>&house_id=<?php echo $houseId; ?>&page=<?php echo $page + 1; ?>">Next ➡</a>
    <?php endif; ?>
</div>

<h3>Send a Message</h3>
<form method="post" action="">
    <textarea name="message" rows="3" cols="50" required></textarea><br>
    <button type="submit">Send</button>
</form>
<?php include("../includes/footer.php"); ?>
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
        $notificationContent = "You received a house inquiry from $senderName";
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
<style>
    body {
        background: #f4f6fa;
        color: #23272f;
    }

    .conv-wrapper {
        max-width: 1100px;
        margin: 36px auto 0 auto;
        background: #f9fafb;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(44, 62, 80, 0.10);
        padding: 36px 32px 28px 32px;
        border: 1px solid #e0e3e7;
    }

    .conv-title {
        color: #263238;
        font-size: 1.3rem;
        margin-bottom: 10px;
        text-align: center;
        font-weight: 600;
    }

    .conv-nav {
        float: right;
        margin-bottom: 18px;
        text-align: left;
    }

    .conv-nav a {
        color: #607d8b;
        text-decoration: underline;
        font-weight: 500;
        font-size: 1.04rem;
        transition: color 0.18s;
    }

    .conv-nav a:hover {
        color: #e67e22;
    }

    .conv-messages {
        margin-bottom: 18px;
        min-height: 180px;
        background: #f9fafb;
        border-radius: 12px;
        padding: 18px 10px;
        box-shadow: 0 1px 4px rgba(44, 62, 80, 0.04);
    }

    .conv-msg {
        max-width: 80%;
        margin-bottom: 14px;
        padding: 12px 16px;
        border-radius: 12px;
        background: #e7e9ec;
        color: #263238;
        font-size: 1.04rem;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.04);
        position: relative;
        word-break: break-word;
    }

    .conv-msg.me {
        background: #c8e6c9;
        color: #263238;
        margin-left: auto;
        text-align: right;
    }

    .conv-msg.other {
        background: #eceff1;
        color: #263238;
        margin-right: auto;
        text-align: left;
    }

    .conv-msg .conv-sender {
        font-weight: 600;
        font-size: 0.98rem;
        margin-bottom: 2px;
        display: block;
        color: #607d8b;
    }

    .conv-msg .conv-date {
        color: #90a4ae;
        font-size: 0.93rem;
        margin-top: 4px;
        display: block;
    }

    .conv-pagination {
        margin-bottom: 18px;
        text-align: center;
        font-size: 1.04rem;
    }

    .conv-pagination a {
        color: #607d8b;
        text-decoration: underline;
        margin: 0 8px;
        font-weight: 500;
        transition: color 0.18s;
    }

    .conv-pagination a:hover {
        color: #374151;
    }

    .conv-form-title {
        font-size: 1.08rem;
        color: #263238;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .conv-form textarea {
        width: 100%;
        min-height: 70px;
        padding: 10px;
        border-radius: 7px;
        border: 1.5px solid #e0e0e0;
        font-size: 1.05rem;
        background: #f7f8fa;
        color: #263238;
        margin-bottom: 10px;
        resize: vertical;
        transition: border 0.18s;
    }

    .conv-form textarea:focus {
        border: 1.5px solid #90a4ae;
        outline: none;
    }

    .conv-form button {
        background: #607d8b;
        color: #fff;
        border: none;
        border-radius: 7px;
        padding: 10px 28px;
        font-size: 1.08rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.18s;
    }

    .conv-form button:hover {
        background: #374151;
        color: #fff;
    }

    .back-inbox-btn {
        display: inline-block;
        /* background: #607d8b; */
        color: #fff !important;
        padding: 8px 20px;
        border-radius: 7px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.04rem;
        /* box-shadow: 0 1px 4px rgba(44, 62, 80, 0.08); */
        transition: background 0.18s, color 0.18s;
        margin-bottom: 8px;
    }

    .back-inbox-btn:hover {
        color: #fff !important;
        cursor: pointer;
    }
</style>
<div class="conv-nav">
    <span class="back-inbox-btn"><a href="inbox.php">← Back to Inbox</a></span>
</div>
<div class="conv-wrapper">
    <div class="conv-title">Conversation about: <?php echo htmlspecialchars($house['title']); ?></div>


    <div class="conv-messages">
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $row): ?>
                <div class="conv-msg <?php echo ($row['sender_id'] == $currentUser) ? 'me' : 'other'; ?>">
                    <span class="conv-sender"><?php echo htmlspecialchars($row['full_name']); ?></span>
                    <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                    <span class="conv-date"><?php echo $row['created_at']; ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No messages yet.</p>
        <?php endif; ?>
    </div>
    <div class="conv-pagination">
        <?php if ($page > 1): ?>
            <a href="conversation.php?user_id=<?php echo $otherUserId; ?>&house_id=<?php echo $houseId; ?>&page=<?php echo $page - 1; ?>">⬅ Prev</a>
        <?php endif; ?>
        Page <?php echo $page; ?> of <?php echo $totalPages; ?>
        <?php if ($page < $totalPages): ?>
            <a href="conversation.php?user_id=<?php echo $otherUserId; ?>&house_id=<?php echo $houseId; ?>&page=<?php echo $page + 1; ?>">Next ➡</a>
        <?php endif; ?>
    </div>
    <div class="conv-form-title">Send a Message</div>
    <form method="post" action="" class="conv-form">
        <textarea name="message" required></textarea>
        <button type="submit">Send</button>
    </form>
</div>
<?php include("../includes/footer.php"); ?>
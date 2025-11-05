<?php
session_start();
require_once "../config/db.php";
require_once "../includes/notification_helper.php";

if (!isset($_SESSION['user_id'])) die("You must be logged in.");

$currentUser = $_SESSION['user_id'];
$otherUserId = intval($_GET['user_id']);
$houseId     = intval($_GET['house_id']);

// Get other user info
$stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id=?");
$stmt->bind_param("i", $otherUserId);
$stmt->execute();
$otherUser = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get house info
$houseStmt = $conn->prepare("SELECT title FROM houses WHERE house_id=?");
$houseStmt->bind_param("i", $houseId);
$houseStmt->execute();
$house = $houseStmt->get_result()->fetch_assoc();
$houseStmt->close();

// Handle sending new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $msg = trim($_POST['message']);
    if ($msg !== "") {
        $stmt = $conn->prepare("
            INSERT INTO messages (house_id, sender_id, receiver_id, content, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("iiis", $houseId, $currentUser, $otherUserId, $msg);
        $stmt->execute();
        $stmt->close();

        $notificationContent = "You received a house inquiry";
        $notificationLink = "../messages/conversation.php?user_id=$currentUser&house_id=$houseId";
        addNotification($conn, $otherUserId, $notificationContent, $notificationLink);
    }
    exit(); // AJAX will handle reloading
}
?>

<?php $title = "Chat";
include("../includes/header.php"); ?>

<style>
    html,
    body {
        height: 100%;
        margin: 0;
        font-family: 'Inter', sans-serif;
        background: #f4f6fa;
    }

    .chat-wrapper {
        display: flex;
        flex-direction: column;
        height: 100vh;
        max-width: 1000px;
        margin: 0 auto;
        padding: 10px;
        box-sizing: border-box;
    }

    .chat-card {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        position: relative;
    }

    .chat-header {
        padding: 16px 24px;
        background: #f9fafb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
        font-size: 1.2rem;
        border-bottom: 1px solid #e0e3e7;
    }

    .chat-header a.btn-pay {
        background: #0123ffff;
        color: #fff;
        padding: 6px 14px;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s;
    }

    .chat-header a.btn-pay:hover {
        background: #1bbe4eff;
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #f4f6fa;
    }

    .message {
        max-width: 70%;
        padding: 14px 18px;
        border-radius: 12px;
        font-size: 0.95rem;
        position: relative;
        word-wrap: break-word;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        background: #e7e9ec;
    }

    .message.me {
        margin-left: auto;
        background: #c8e6c9;
    }

    .message .time {
        font-size: 0.75rem;
        color: #607d8b;
        position: absolute;
        top: 6px;
        right: 10px;
    }

    .message-date {
        text-align: center;
        font-size: 0.8rem;
        color: #90a4ae;
        margin: 12px 0;
        font-weight: 500;
    }

    .chat-form-container {
        flex-shrink: 0;
        padding: 12px 20px;
        background: #fff;
        display: flex;
        gap: 12px;
        align-items: center;
        border-top: 1px solid #e0e3e7;
    }

    .chat-form-container textarea {
        flex: 1;
        padding: 12px 16px;
        border-radius: 20px;
        border: 1px solid #d1d5db;
        font-size: 1rem;
        resize: vertical;
        min-height: 50px;
    }

    .chat-form-container button {
        background: #2d4564ff;
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .chat-form-container button:hover {
        background: #374151;
    }

    .back-btn {
        text-decoration: underline;
        font-weight: 500;
        color: #607d8b;
        padding: 10px 0;
    }

    .back-btn:hover {
        color: #374151;
    }

    /* Responsive */
    @media(max-width:768px) {
        .chat-card {
            border-radius: 0;
        }

        .chat-header {
            font-size: 1rem;
            padding: 12px;
        }

        .chat-form-container textarea {
            font-size: 0.95rem;
        }

        .chat-form-container button {
            width: 45px;
            height: 45px;
        }

        .message {
            font-size: 0.9rem;
            padding: 12px 16px;
        }
    }
</style>

<div class="chat-wrapper">
    <a href="inbox.php" class="back-btn">← Back to Inbox</a>

    <div class="chat-card">
        <div class="chat-header">
            <span><?php echo htmlspecialchars($otherUser['full_name']); ?> – <?php echo htmlspecialchars($house['title']); ?></span>
            <a href="../pay.php?id=<?php echo $houseId; ?>" class="btn-pay"><i class="fas fa-credit-card"></i> Pay</a>
        </div>

        <div id="chatMessages" class="chat-messages"></div>

        <div class="chat-form-container">
            <form id="chatForm" style="flex:1; display:flex; gap:12px;">
                <textarea name="message" placeholder="Write a message..." required></textarea>
                <button type="submit"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
</div>

<script>
    const chatContainer = document.getElementById('chatMessages');
    const form = document.getElementById('chatForm');
    let lastMessageId = 0;

    async function loadMessages() {
        const resp = await fetch(`message_loader.php?house_id=<?php echo $houseId; ?>&user_id=<?php echo $otherUserId; ?>&after_id=${lastMessageId}`);
        const data = await resp.text();
        if (data.trim() !== '') {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = data;
            chatContainer.append(...tempDiv.children);
            const messages = chatContainer.querySelectorAll('.message');
            lastMessageId = messages[messages.length - 1]?.dataset.id || lastMessageId;
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }

    setInterval(loadMessages, 2000);
    loadMessages();

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const formData = new FormData(form);
        await fetch('', {
            method: 'POST',
            body: formData
        });
        form.reset();
        loadMessages();
    });
</script>

<?php include("../includes/footer.php"); ?>
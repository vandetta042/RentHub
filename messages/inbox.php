<?php
session_start();
require_once "../config/db.php";
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view your inbox.");
}
$currentUser = $_SESSION['user_id'];

// Fetch last message per house-user pair
$sql = "
    SELECT h.house_id, h.title AS house_title,
           u.user_id AS other_user_id, u.full_name AS other_user_name,
           m.content, m.created_at
    FROM messages m
    INNER JOIN houses h ON m.house_id = h.house_id
    INNER JOIN users u ON u.user_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
    INNER JOIN (
        SELECT house_id, LEAST(sender_id, receiver_id) AS user_a, GREATEST(sender_id, receiver_id) AS user_b, MAX(created_at) AS last_msg
        FROM messages
        WHERE sender_id = ? OR receiver_id = ?
        GROUP BY house_id, user_a, user_b
    ) t ON m.house_id = t.house_id 
       AND LEAST(m.sender_id, m.receiver_id) = t.user_a
       AND GREATEST(m.sender_id, m.receiver_id) = t.user_b
       AND m.created_at = t.last_msg
    ORDER BY h.house_id DESC, m.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $currentUser, $currentUser, $currentUser);
$stmt->execute();
$result = $stmt->get_result();

// Group by house
$houses = [];
while ($row = $result->fetch_assoc()) {
    $houses[$row['house_id']]['house_title'] = $row['house_title'];
    $houses[$row['house_id']]['conversations'][] = $row;
}
?>

<?php $title = "Inbox"; include("../includes/header.php"); ?>

<style>
.inbox-wrapper {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 16px;
}

/* Page title */
.inbox-title {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50; /* match your scheme */
    text-align: center;
    margin-bottom: 24px;
}

/* House card */
.house-card {
    background: #f8f9fa; /* soft background for house */
    border-radius: 14px;
    margin-bottom: 16px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* Collapsible button */
.house-header {
    padding: 14px 20px;
    background: #4a6a93; /* your primary color */
    color: #fff;
    font-weight: 600;
    font-size: 1.1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    border: none;
    outline: none;
}
.house-header i {
    transition: transform 0.3s;
}
.house-header.active i {
    transform: rotate(180deg);
}

/* Collapsible content */
.house-content {
    display: none;
    background: #ffffff;
    padding: 12px 20px;
    border-top: 1px solid #ddd;
}

/* Conversation cards */
.conv-card {
    background: #f0f4f8; /* match page style */
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    cursor: pointer;
    transition: transform 0.15s;
}
.conv-card:hover {
    transform: translateY(-1px);
}
.conv-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.conv-info .user-name {
    font-weight: 600;
    color: #2c3e50;
}
.conv-info .snippet {
    font-size: 0.95rem;
    color: #6c757d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 400px;
}
.conv-meta {
    font-size: 0.85rem;
    color: #aaa;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}
.conv-meta i {
    color: #4a6a93;
    margin-top: 4px;
}

/* Responsive */
@media(max-width:768px){
    .conv-card { flex-direction: column; align-items: flex-start; gap: 6px; }
    .conv-meta { flex-direction: row; width: 100%; justify-content: space-between; }
    .conv-info .snippet { max-width: 100%; }
}
</style>

<div class="inbox-wrapper">
    <a href="../users/dashboard.php">← Back to Dashboard</a>
    <div class="inbox-title">Your Inbox</div>
    

    <?php if(!empty($houses)): ?>
        <?php foreach($houses as $houseId => $house): ?>
            <div class="house-card">
                <div class="house-header" onclick="toggleHouse('<?php echo $houseId; ?>')">
                    <span><i class="fas fa-home"></i> <?php echo htmlspecialchars($house['house_title']); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="house-content" id="house-<?php echo $houseId; ?>">
                    <?php foreach($house['conversations'] as $conv): ?>
                        <a href="conversation.php?user_id=<?php echo $conv['other_user_id']; ?>&house_id=<?php echo $houseId; ?>" style="text-decoration:none;">
                            <div class="conv-card">
                                <div class="conv-info">
                                    <span class="user-name"><i class="fas fa-user"></i> <?php echo htmlspecialchars($conv['other_user_name']); ?></span>
                                    <span class="snippet"><?php echo htmlspecialchars(substr($conv['content'],0,60)); ?>...</span>
                                </div>
                                <div class="conv-meta">
                                    <span><?php echo date("h:i A", strtotime($conv['created_at'])); ?></span>
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center; color:#6c757d;">No conversations yet.</p>
    <?php endif; ?>
</div>

<script>
function toggleHouse(id){
    const content = document.getElementById('house-' + id);
    const header = content.previousElementSibling;
    if(content.style.display === "none" || content.style.display === ""){
        content.style.display = "block";
        header.classList.add('active');
    } else {
        content.style.display = "none";
        header.classList.remove('active');
    }
}
</script>

<?php include("../includes/footer.php"); ?>

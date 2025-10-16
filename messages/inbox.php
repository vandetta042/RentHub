<?php
session_start();
require_once "../config/db.php";
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view your inbox.");
}
$currentUser = $_SESSION['user_id'];

// Pagination setup
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 8;
$offset = ($page - 1) * $perPage;

// Group messages by house, then by user
$sql = "
    SELECT h.house_id, h.title AS house_title,
           u.user_id AS other_user_id, u.full_name AS other_user_name,
           m.content, m.created_at
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
    ORDER BY h.house_id DESC, m.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiii", $currentUser, $currentUser, $currentUser, $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Get total count for pagination
$countSql = "
    SELECT COUNT(*) as total
    FROM (
        SELECT house_id, LEAST(sender_id, receiver_id) AS user_a, GREATEST(sender_id, receiver_id) AS user_b
        FROM messages
        WHERE sender_id = ? OR receiver_id = ?
        GROUP BY house_id, user_a, user_b
    ) grouped
";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("ii", $currentUser, $currentUser);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $perPage);

?>
<?php include("../includes/header.php"); ?>
<style>
    .inbox-wrapper {
        max-width: 700px;
        margin: 36px auto 0 auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(44, 62, 80, 0.10);
        padding: 36px 32px 28px 32px;
    }

    .inbox-title {
        color: #2c3e50;
        font-size: 1.7rem;
        margin-bottom: 18px;
        text-align: center;
        font-weight: 700;
        letter-spacing: 1px;
    }

    .inbox-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .inbox-group {
        margin-bottom: 18px;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 0;
        background: #f6f7fa;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.04);
    }

    .inbox-group:last-child {
        border-bottom: none;
    }

    .dropdown-btn {
        width: 100%;
        background: #e5e1e1ff;
        color: #2d4666;
        font-size: 1.15rem;
        font-weight: 600;
        border: none;
        border-radius: 10px;
        padding: 16px 18px;
        text-align: left;
        cursor: pointer;
        outline: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background 0.18s;
        margin-bottom: 0;
    }

    .dropdown-btn.active {
        background-color: #2d4666;
        color: #fff;
    }

    /* .dropdown-btn:hover {
        background: #2d4666;
    } */

    .arrow {
        margin-left: 8px;
        font-size: 1.1rem;
        transition: transform 0.3s;
    }

    .dropdown-btn.active .arrow {
        transform: rotate(180deg);
    }

    .dropdown-content {
        padding: 0 18px 12px 18px;
        background: #ffffffff;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.03);
        margin-bottom: 8px;
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .inbox-item {
        padding: 12px 0 8px 0;
        display: flex;
        flex-direction: column;
        gap: 2px;
        border-bottom: 1px solid #f7f7f7;
    }

    .inbox-item:last-child {
        border-bottom: none;
    }

    .inbox-user {
        font-weight: 600;
        color: #34495e;
        font-size: 1.08rem;
    }

    .inbox-snippet {
        color: #2c3e50;
        font-size: 1.04rem;
        margin: 2px 0 2px 0;
    }

    .inbox-date {
        color: #aaa;
        font-size: 0.98rem;
    }

    .view-conv {
        background-color: #4CAF50;
        color: #fff;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;

        transition: background-color 0.3s ease-in-out;
    }

    .view-conv:hover {
        background-color: #219150;
    }

    .view-conv:active {
        transform: scale(0.98);
    }


    .inbox-nav {
        float: right;
        margin-bottom: 18px;
        text-align: left;
    }

    .inbox-nav a {
        display: flex;
        color: #2c3e50ff;
        text-decoration: underline;
        text-align: right;
        font-weight: 500;
        font-size: 1.04rem;
        transition: color 0.18s;
    }

    .inbox-nav a:hover {
        color: #e67e22;
    }

    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 32px;
        gap: 8px;
    }

    .pagination a {
        color: #4a6a93;
        background: #f6f7fa;
        border-radius: 8px;
        padding: 8px 16px;
        text-decoration: none;
        font-weight: 500;
        font-size: 1.05rem;
        transition: background 0.18s, color 0.18s;
    }

    .pagination a.active {
        background: #4a6a93;
        color: #fff;
    }

    .pagination a:hover {
        background: #e3eaf3;
        color: #2c3e50;
    }
</style>
<div class="inbox-nav">
    <a href="../users/dashboard.php">← Back to Dashboard</a>
</div>
<div class="inbox-wrapper">
    <div class="inbox-title">Your Inbox</div>
    <?php
    // Group results by house
    $groups = [];
    while ($row = $result->fetch_assoc()) {
        $houseId = $row['house_id'];
        if (!isset($groups[$houseId])) {
            $groups[$houseId] = [
                'house_title' => $row['house_title'],
                'items' => []
            ];
        }
        $groups[$houseId]['items'][] = $row;
    }
    ?>
    <?php if (count($groups) > 0): ?>
        <ul class="inbox-list">
            <?php foreach ($groups as $houseId => $group): ?>
                <li class="inbox-group">
                    <button class="dropdown-btn" onclick="toggleDropdown('dropdown-<?php echo $houseId; ?>', this)">
                        🏠 <?php echo htmlspecialchars($group['house_title']); ?> <span class="arrow">▼</span>
                    </button>
                    <div class="dropdown-content" id="dropdown-<?php echo $houseId; ?>" style="display:none;">
                        <?php foreach ($group['items'] as $item): ?>
                            <div class="inbox-item">
                                <span class="inbox-user">👤 <?php echo htmlspecialchars($item['other_user_name']); ?></span>
                                <span class="inbox-snippet"><?php echo htmlspecialchars(substr($item['content'], 0, 50)); ?>...</span>
                                <span class="inbox-date"><?php echo $item['created_at']; ?></span>
                                <a href="conversation.php?user_id=<?php echo $item['other_user_id']; ?>&house_id=<?php echo $houseId; ?>" class="inbox-link">
                                    <button class="view-conv">View Conversations</button>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php else: ?>
        <p>No conversations yet.</p>
    <?php endif; ?>

    <script>
        function toggleDropdown(id, btn) {
            var el = document.getElementById(id);
            if (el.style.display === "none" || el.style.display === "") {
                el.style.display = "block";
                btn.classList.add('active');
            } else {
                el.style.display = "none";
                btn.classList.remove('active');
            }
        }
    </script>
</div>
<?php include("../includes/footer.php"); ?>
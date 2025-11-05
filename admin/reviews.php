<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

$sql = "
    SELECT 
        r.review_id, 
        r.rating, 
        r.comment, 
        r.status, 
        r.created_at,
        u.full_name, 
        h.title 
    FROM 
        reviews r
    LEFT JOIN users u ON r.user_id = u.user_id
    LEFT JOIN houses h ON r.house_id = h.house_id
    ORDER BY 
        FIELD(r.status, 'flagged', 'pending_mode', 'active', 'deleted'), r.created_at DESC
";

$result = $conn->query($sql);

$title = "Manage Reviews";
include("../includes/header.php"); 
?>

<style>
.admin-reviews-wrapper {
    max-width: 1100px;
    margin: 0 auto 0 auto;
    padding: 0 18px 36px 18px;
}

.admin-reviews-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
    margin-top: 32px;
}

.admin-reviews-header h1 {
    color: #2c3e50;
    font-size: 1.7rem;
    margin: 0;
}

.admin-reviews-header a {
    color: #111;
    padding: 8px 22px;
    text-decoration: underline;
    font-size: 1.05rem;
    font-weight: bold;
    transition: color 0.18s;
}

.admin-reviews-header a:hover {
    color: #c1952e;
}

.admin-reviews-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(44,62,80,0.1);
    padding: 32px 24px 24px 24px;
    margin-bottom: 24px;
    overflow-x: auto;
}

.admin-reviews-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    min-width: 800px;
}

.admin-reviews-table th,
.admin-reviews-table td {
    padding: 12px 10px;
    border-bottom: 1.5px solid #f0f0f0;
    text-align: left;
    font-size: 1.05rem;
}

.admin-reviews-table th {
    background: #f4f6f8;
    color: #2c3e50;
    font-weight: 600;
}

.comment-cell {
    cursor: pointer;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.admin-action-link {
    text-decoration: none;
    margin-right: 8px;
    font-weight: 500;
    transition: color 0.18s;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.95rem;
}

.admin-action-approve { color: #27ae60; }
.admin-action-approve:hover { color: #219150; }

.admin-action-flag { color: #e67e22; }
.admin-action-flag:hover { color: #b9770e; }

.admin-action-delete { color: #e74c3c; }
.admin-action-delete:hover { color: #c0392b; }

.admin-status-badge {
    padding: 3px 12px;
    border-radius: 12px;
    font-weight: bold;
    font-size: 0.98rem;
    display: inline-block;
}

.admin-status-active { background: #27ae60; color: #fff; }
.admin-status-flagged { background: #ff4d4d; color: #fff; }
.admin-status-pending_mode { background: #ffc107; color: #2c3e50; }
.admin-status-deleted { background: #e74c3c; color: #fff; }

@media (max-width: 800px) {
    .admin-reviews-card { padding: 12px 2px; }
    .admin-reviews-header { flex-direction: column; align-items: flex-start; gap: 10px; }
    .admin-reviews-header h1 { font-size: 1.2rem; }
}
</style>

<div class="admin-reviews-wrapper">
    <div class="admin-reviews-header">
        <h1>Manage Reviews</h1>
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

    <div class="admin-reviews-card">
        <?php if (isset($_GET['message'])): ?>
            <p style="color:#27ae60;font-weight:bold;margin-bottom:18px;">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </p>
        <?php endif; ?>

        <table class="admin-reviews-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>House</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['full_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['title'] ?? 'N/A'); ?></td>
                            <td><?php echo str_repeat('⭐', $row['rating']); ?></td>
                            <td class="comment-cell" title="Click to expand"><?php echo htmlspecialchars($row['comment']); ?></td>
                            <td>
                                <span class="admin-status-badge admin-status-<?php echo $row['status']; ?>">
                                    <?php echo str_replace('_', ' ', $row['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                           <td>
    <?php if ($row['status'] != 'active'): ?>
        <a href="handle_reviews.php?id=<?php echo $row['review_id']; ?>&action=approve" 
           class="admin-action-link admin-action-approve" title="Approve Review">
           <i class="fa-solid fa-check"></i> Approve
        </a>
    <?php endif; ?>
    <?php if ($row['status'] != 'flagged'): ?>
        <a href="handle_reviews.php?id=<?php echo $row['review_id']; ?>&action=flag" 
           class="admin-action-link admin-action-flag" title="Flag Review">
           <i class="fa-solid fa-flag"></i> Flag
        </a>
    <?php endif; ?>
    <?php if ($row['status'] != 'deleted'): ?>
        <a href="handle_reviews.php?id=<?php echo $row['review_id']; ?>&action=delete" 
           class="admin-action-link admin-action-delete" 
           onclick="return confirm('Are you sure you want to delete this review?')" 
           title="Delete Review">
           <i class="fa-solid fa-trash"></i> Delete
        </a>
    <?php endif; ?>
</td>

                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No reviews found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.comment-cell').forEach(cell => {
    cell.addEventListener('click', () => {
        if(cell.style.whiteSpace === 'normal') {
            cell.style.whiteSpace = 'nowrap';
            cell.style.textOverflow = 'ellipsis';
        } else {
            cell.style.whiteSpace = 'normal';
            cell.style.textOverflow = 'clip';
        }
    });
});
</script>

<?php include("../includes/footer.php"); ?>
<?php $conn->close(); ?>

<?php
// Include the database connection
session_start();

// Make sure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

// --- ASSUMPTIONS ---
// For a better display, we'll get usernames and house names.
// This query assumes you have a 'users' table with 'user_id' and 'username' columns,
// and a 'houses' table with 'house_id' and 'house_name' columns.
// If your tables are named differently, please adjust the JOINs below.

$sql = "
    SELECT 
        r.review_id, 
        r.rating, 
        r.comment, 
        r.status, 
        r.created_at,
        u.full_name,  -- From the 'users' table
        h.title -- From the 'houses' table
    FROM 
        reviews r
    LEFT JOIN 
        users u ON r.user_id = u.user_id
    LEFT JOIN 
        houses h ON r.house_id = h.house_id
    ORDER BY 
        FIELD(r.status, 'flagged', 'pending_mode', 'active', 'deleted'), r.created_at DESC
";

$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Reviews</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 0;
        }

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
            background: #2c3e50;
            color: #fff;
            border-radius: 7px;
            padding: 8px 22px;
            text-decoration: none;
            font-size: 1.05rem;
            font-weight: 500;
            transition: background 0.18s;
        }

        .admin-reviews-header a:hover {
            background: #34495e;
        }

        .admin-reviews-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(44, 62, 80, 0.10);
            padding: 32px 24px 24px 24px;
            margin-bottom: 24px;
        }

        .admin-reviews-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
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

        .admin-reviews-table tr:last-child td {
            border-bottom: none;
        }

        .admin-reviews-table tr.status-flagged {
            background-color: #fff0f0;
            border-left: 5px solid #ff4d4d;
        }

        .admin-reviews-table tr.status-pending_mode {
            background-color: #fffbe6;
            border-left: 5px solid #ffc107;
        }

        .admin-reviews-table tr.status-active {
            background-color: #f8fff8;
            border-left: 5px solid #27ae60;
        }

        .admin-reviews-table tr.status-deleted {
            background-color: #f4f4f4;
            border-left: 5px solid #e74c3c;
        }

        .admin-action-link {
            color: #2c3e50;
            text-decoration: underline;
            margin-right: 8px;
            font-weight: 500;
            transition: color 0.18s;
            padding: 0;
            background: none;
            border: none;
        }

        .admin-action-link.admin-action-approve {
            color: #27ae60;
        }

        .admin-action-link.admin-action-approve:hover {
            color: #219150;
        }

        .admin-action-link.admin-action-flag {
            color: #e67e22;
        }

        .admin-action-link.admin-action-flag:hover {
            color: #b9770e;
        }

        .admin-action-link.admin-action-delete {
            color: #e74c3c;
        }

        .admin-action-link.admin-action-delete:hover {
            color: #c0392b;
        }

        .admin-status-badge {
            padding: 3px 12px;
            border-radius: 12px;
            color: #fff;
            font-weight: bold;
            font-size: 0.98rem;
            display: inline-block;
        }

        .admin-status-active {
            background: #27ae60;
        }

        .admin-status-flagged {
            background: #ff4d4d;
        }

        .admin-status-pending_mode {
            background: #ffc107;
            color: #2c3e50;
        }

        .admin-status-deleted {
            background: #e74c3c;
        }

        @media (max-width: 800px) {
            .admin-reviews-card {
                padding: 12px 2px;
            }

            .admin-reviews-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .admin-reviews-header h1 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <div class="admin-reviews-wrapper">
        <div class="admin-reviews-header">
            <h1>Manage Reviews</h1>
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
        <div class="admin-reviews-card">
            <?php if (isset($_GET['message'])): ?>
                <p style="color: #27ae60; font-weight: bold; margin-bottom: 18px;"><?php echo htmlspecialchars($_GET['message']); ?></p>
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
                            <tr class="status-<?php echo $row['status']; ?>">
                                <td><?php echo htmlspecialchars($row['full_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['title'] ?? 'N/A'); ?></td>
                                <td><?php echo str_repeat('⭐', $row['rating']); ?></td>
                                <td><?php echo htmlspecialchars($row['comment']); ?></td>
                                <td>
                                    <span class="admin-status-badge admin-status-<?php echo $row['status']; ?>">
                                        <?php echo str_replace('_', ' ', $row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <?php if ($row['status'] != 'active'): ?>
                                        <a href="handle_reviews.php?id=<?php echo $row['review_id']; ?>&action=approve" class="admin-action-link admin-action-approve">Approve</a>
                                    <?php endif; ?>
                                    <?php if ($row['status'] != 'flagged'): ?>
                                        <a href="handle_reviews.php?id=<?php echo $row['review_id']; ?>&action=flag" class="admin-action-link admin-action-flag">Flag</a>
                                    <?php endif; ?>
                                    <?php if ($row['status'] != 'deleted'): ?>
                                        <a href="handle_reviews.php?id=<?php echo $row['review_id']; ?>&action=delete" class="admin-action-link admin-action-delete" onclick="return confirm('Are you sure you want to delete this review?')">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No reviews found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>

<?php
// Close the connection
$conn->close();
?>
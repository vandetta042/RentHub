<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

// Fetch all users
$sql = "SELECT user_id, full_name, email, user_type, status, created_at 
        FROM users 
        ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<?php include("../includes/header.php"); ?>
<style>
    .admin-users-wrapper {
        max-width: 1100px;
        margin: 0 auto 0 auto;
        padding: 0 18px;
    }

    .admin-users-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
        margin-top: 18px;
    }

    .admin-users-header h2 {
        color: #2c3e50;
        font-size: 1.7rem;
        margin: 0;
    }

    .admin-users-header a {
        background: #2c3e50;
        color: #fff;
        border-radius: 7px;
        padding: 8px 22px;
        text-decoration: none;
        font-size: 1.05rem;
        font-weight: 500;
        transition: background 0.18s;
    }

    .admin-users-header a:hover {
        background: #34495e;
    }

    .admin-users-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(44, 62, 80, 0.10);
        padding: 32px 24px 24px 24px;
        margin-bottom: 24px;
    }

    .admin-users-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }

    .admin-users-table th,
    .admin-users-table td {
        padding: 12px 10px;
        border-bottom: 1.5px solid #f0f0f0;
        text-align: left;
        font-size: 1.05rem;
    }

    .admin-users-table th {
        background: #f4f6f8;
        color: #2c3e50;
        font-weight: 600;
    }

    .admin-users-table tr:last-child td {
        border-bottom: none;
    }

    .admin-users-table tr:hover {
        background: #f9fafb;
    }

    .admin-action-link {
        color: #2c3e50;
        text-decoration: underline;
        margin-right: 8px;
        font-weight: 500;
        transition: color 0.18s;
    }

    .admin-action-link:hover {
        color: #27ae60;
    }

    .admin-status-active {
        color: #27ae60;
        font-weight: bold;
    }

    .admin-status-suspended {
        color: #e67e22;
        font-weight: bold;
    }

    .admin-status-deleted {
        color: #e74c3c;
        font-weight: bold;
    }
</style>
<div class="admin-users-wrapper">
    <div class="admin-users-header">
        <h2>User Management</h2>
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
    <div class="admin-users-card">
        <?php if ($result && $result->num_rows > 0): ?>
            <table class="admin-users-table">
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                        <td class="admin-status-<?php echo htmlspecialchars(strtolower($row['status'])); ?>"><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <?php if ($row['user_type'] === 'admin' && $row['user_id'] == $_SESSION['user_id']): ?>
                                <em>admin privileges</em>
                            <?php else: ?>
                                <?php if ($row['status'] === 'active'): ?>
                                    <a href="suspend.php?id=<?php echo $row['user_id']; ?>" class="admin-action-link" onclick="return confirm('Suspend this user?');">Suspend</a>
                                    <a href="delete_user.php?id=<?php echo $row['user_id']; ?>" class="admin-action-link" onclick="return confirm('Permanently delete this user?');">Delete</a>
                                <?php elseif ($row['status'] === 'suspended'): ?>
                                    <a href="suspend.php?id=<?php echo $row['user_id']; ?>" class="admin-action-link" onclick="return confirm('Unsuspend this user?');">Unsuspend</a>
                                    <a href="restore_user.php?id=<?php echo $row['user_id']; ?>" class="admin-action-link" onclick="return confirm('Restore this user?');">Restore</a>
                                <?php else: ?>
                                    <em>Deleted</em>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>
</div>
<?php include("../includes/footer.php"); ?>
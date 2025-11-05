<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

$sql = "SELECT user_id, full_name, email, user_type, status, created_at 
        FROM users 
        ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<?php 
$title = "User Management"; 
include("../includes/header.php"); 
?>

<style>
.admin-users-wrapper {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 18px;
}

.admin-users-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 24px 0 16px 0;
}

.admin-users-header h2 {
    color: #2c3e50;
    font-size: 1.7rem;
    margin: 0;
}

.admin-users-header a {
    color: #0a0a0a;
    text-decoration: underline;
    font-weight: 600;
    transition: color 0.2s;
}

.admin-users-header a:hover {
    color: #d19e28;
}

.admin-users-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(44, 62, 80, 0.10);
    padding: 26px 20px;
    margin-bottom: 24px;
    overflow-x: auto; /* ensures responsiveness on mobile */
}

.admin-users-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 800px; /* keeps structure stable */
}

.admin-users-table th,
.admin-users-table td {
    padding: 12px 10px;
    border-bottom: 1.5px solid #f0f0f0;
    text-align: left;
    font-size: 0.97rem;
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

.admin-status-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.88rem;
    text-transform: capitalize;
}

.admin-status-active {
    background-color: #e7f8ef;
    color: #27ae60;
}

.admin-status-suspended {
    background-color: #fff4e6;
    color: #e67e22;
}

.admin-status-deleted {
    background-color: #fdecea;
    color: #e74c3c;
}

/* created date styling */
.admin-created {
    color: #555;
    font-size: 0.9rem;
}

/* action links */
.admin-action-cell {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.admin-action-link {
    background: #eef3f7;
    color: #2c3e50;
    border-radius: 6px;
    padding: 6px 10px;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.88rem;
    transition: background 0.2s, color 0.2s;
}

.admin-action-link:hover {
    background: #2c3e50;
    color: #fff;
}

/* responsive tweaks */
@media (max-width: 768px) {
    .admin-users-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .admin-users-table {
        min-width: 100%;
        font-size: 0.9rem;
    }
}
</style>


<div class="admin-users-wrapper">
    <div class="admin-users-header">
        <h2><i class="fa-solid fa-users"></i> User Management</h2>
        <a href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
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
                    <th>Actions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?php echo $row['user_id']; ?></td>
    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
    <td><?php echo htmlspecialchars($row['email']); ?></td>
    <td><?php echo htmlspecialchars($row['user_type']); ?></td>
    <td>
        <span class="admin-status-badge admin-status-<?php echo htmlspecialchars(strtolower($row['status'])); ?>">
            <?php echo htmlspecialchars($row['status']); ?>
        </span>
    </td>
    <td class="admin-created">
        <?php echo date("d M Y", strtotime($row['created_at'])); ?>
    </td>
    <td class="admin-action-cell">
        <?php if ($row['user_type'] === 'admin' && $row['user_id'] == $_SESSION['user_id']): ?>
            <em>admin privileges</em>
        <?php else: ?>
            <?php if ($row['status'] === 'active'): ?>
                <a href="suspend.php?id=<?php echo $row['user_id']; ?>" class="admin-action-link" onclick="return confirm('Suspend this user?');">
                    <i class="fa-solid fa-ban"></i> Suspend
                </a>
                <a href="delete_user.php?id=<?php echo $row['user_id']; ?>" class="admin-action-link" onclick="return confirm('Permanently delete this user?');">
                    <i class="fa-solid fa-trash"></i> Delete
                </a>
            <?php elseif ($row['status'] === 'suspended'): ?>
                <a href="suspend.php?id=<?php echo $row['user_id']; ?>" class="admin-action-link" onclick="return confirm('Unsuspend this user?');">
                    <i class="fa-solid fa-rotate-left"></i> Unsuspend
                </a>
                <!-- <a href="restore_user.php?id=<?php 
                // echo $row['user_id']; 
                ?>" class="admin-action-link" onclick="return confirm('Restore this user?');">
                    <i class="fa-solid fa-undo"></i> Restore
                </a> -->
            <?php else: ?>
                <a href="restore.php?id=<?php echo $row['user_id']; ?>" class="admin-action-link" onclick="return confirm('Restore this user?');">
                    <i class="fa-solid fa-user-restore"></i> Restore
                </a>
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

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

// Fetch all users
$sql = "SELECT h.*, u.full_name AS owner_name
        FROM houses h
        JOIN users u ON h.user_id = u.user_id
        WHERE h.status IN ('available','taken')

        ORDER BY h.created_at DESC";
$result = $conn->query($sql);
?>

<?php include("../includes/header.php"); ?>
<style>
    .admin-houses-wrapper {
        max-width: 1100px;
        margin: 0 auto 0 auto;
        padding: 0 18px;
    }

    .admin-houses-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
        margin-top: 18px;
    }

    .admin-houses-header h2 {
        color: #2c3e50;
        font-size: 1.7rem;
        margin: 0;
    }

    .admin-houses-header a {
        background: #2c3e50;
        color: #fff;
        border-radius: 7px;
        padding: 8px 22px;
        text-decoration: none;
        font-size: 1.05rem;
        font-weight: 500;
        transition: background 0.18s;
    }

    .admin-houses-header a:hover {
        background: #34495e;
    }

    .admin-houses-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(44, 62, 80, 0.10);
        padding: 32px 24px 24px 24px;
        margin-bottom: 24px;
    }

    .admin-houses-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }

    .admin-houses-table th,
    .admin-houses-table td {
        padding: 12px 10px;
        border-bottom: 1.5px solid #f0f0f0;
        text-align: left;
        font-size: 1.05rem;
    }

    .admin-houses-table th {
        background: #f4f6f8;
        color: #2c3e50;
        font-weight: 600;
    }

    .admin-houses-table tr:last-child td {
        border-bottom: none;
    }

    .admin-houses-table tr:hover {
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
        color: #e67e22;
    }

    .admin-status-available {
        color: #27ae60;
        font-weight: bold;
    }

    .admin-status-taken {
        color: #e67e22;
        font-weight: bold;
    }

    .admin-status-deleted {
        color: #e74c3c;
        font-weight: bold;
    }

    .admin-active {
        color: #27ae60;
        font-weight: bold;
    }

    .admin-deleted {
        color: #e74c3c;
        font-weight: bold;
    }
</style>
<div class="admin-houses-wrapper">
    <div class="admin-houses-header">
        <h2>Manage Houses</h2>
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
    <div class="admin-houses-card">
        <?php if ($result && $result->num_rows > 0): ?>
            <table class="admin-houses-table">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Location</th>
                    <th>Price</th>
                    <th>Owner</th>
                    <th>Status</th>
                    <th>is_active</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['house_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td><?php echo htmlspecialchars($row['price']); ?></td>
                        <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                        <td class="admin-status-<?php echo htmlspecialchars(strtolower($row['status'])); ?>"><?php echo htmlspecialchars($row['status']); ?></td>
                        <td class="admin-<?php echo $row['is_active'] ? 'active' : 'deleted'; ?>"><?php echo $row['is_active'] ? 'active' : 'deleted'; ?></td>
                        <td>
                            <?php if ($row['is_active']): ?>
                                <a href="view_house.php?id=<?php echo $row['house_id']; ?>" class="admin-action-link">View</a>
                                <a href="delete_house.php?id=<?php echo $row['house_id']; ?>" class="admin-action-link" onclick="return confirm('Are you sure you want to permanently delete this house?');">Delete</a>
                            <?php else: ?>
                                <a href="restore_houses.php?id=<?php echo $row['house_id']; ?>" class="admin-action-link" onclick="return confirm('restore this house?');">Restore</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No houses found.</p>
        <?php endif; ?>
    </div>
</div>
<?php include("../includes/footer.php"); ?>
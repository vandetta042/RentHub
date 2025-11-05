<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

// Fetch all houses
$sql = "SELECT h.*, u.full_name AS owner_name
        FROM houses h
        JOIN users u ON h.user_id = u.user_id
        WHERE h.status IN ('available','taken')
        ORDER BY h.created_at DESC";
$result = $conn->query($sql);
?>

<?php 
$title = "Admin Manage Houses"; 
include("../includes/header.php"); 
?>

<style>
    .admin-houses-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .admin-houses-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .admin-houses-header h2 {
        color: #2c3e50;
        font-size: 1.8rem;
        margin: 0;
    }

    .admin-houses-header a {
        color: #3f8fd1;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.05rem;
        transition: color 0.2s ease;
    }

    .admin-houses-header a:hover {
        color: #e67e22;
    }

    .admin-houses-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        padding: 20px;
        overflow-x: auto;
    }

    .admin-houses-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .admin-houses-table th,
    .admin-houses-table td {
        padding: 14px 12px;
        text-align: left;
        font-size: 1rem;
        border-bottom: 1.5px solid #f0f0f0;
        white-space: nowrap;
    }

    .admin-houses-table th {
        background: #f7f9fa;
        color: #34495e;
        font-weight: 600;
    }

    .admin-houses-table tr:hover {
        background: #f9fbfc;
    }

    .admin-status-available {
        color: #27ae60;
        font-weight: bold;
    }

    .admin-status-taken {
        color: #e67e22;
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

    /* --- Actions --- */
    .admin-action-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ecf0f1;
        padding: 6px 10px;
        border-radius: 6px;
        text-decoration: none;
        color: #2c3e50;
        font-size: 0.95rem;
        font-weight: 500;
        transition: background 0.2s, color 0.2s;
    }

    .admin-action-link:hover {
        background: #3f8fd1;
        color: #fff;
    }

    .admin-action-group {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    @media (max-width: 768px) {
        .admin-houses-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .admin-houses-header h2 {
            margin-bottom: 8px;
        }

        .admin-houses-card {
            padding: 16px;
        }

        .admin-houses-table th,
        .admin-houses-table td {
            padding: 10px 8px;
            font-size: 0.9rem;
        }
    }
</style>

<div class="admin-houses-wrapper">
    <div class="admin-houses-header">
        <h2><i class="fa-solid fa-building"></i> Manage Houses</h2>
        <a href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="admin-houses-card">
        <?php if ($result && $result->num_rows > 0): ?>
            <table class="admin-houses-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['house_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                            <td>₦<?php echo number_format($row['price']); ?></td>
                            <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                            <td class="admin-status-<?php echo htmlspecialchars(strtolower($row['status'])); ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </td>
                            <td class="admin-<?php echo $row['is_active'] ? 'active' : 'deleted'; ?>">
                                <?php echo $row['is_active'] ? 'Active' : 'Suspended'; ?>
                            </td>
                            <td>
                                <div class="admin-action-group">
                                    <?php if ($row['is_active']): ?>
                                        <a href="view_house.php?id=<?php echo $row['house_id']; ?>" class="admin-action-link">
                                            <i class="fa-solid fa-eye"></i> View
                                        </a>
                                        <a href="delete_house.php?id=<?php echo $row['house_id']; ?>" class="admin-action-link" onclick="return confirm('Are you sure you want to permanently delete this house?');">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </a>
                                    <?php else: ?>
                                        <a href="restore_houses.php?id=<?php echo $row['house_id']; ?>" class="admin-action-link" onclick="return confirm('Restore this house?');">
                                            <i class="fa-solid fa-rotate-left"></i> Restore
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No houses found.</p>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>

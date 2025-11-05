<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

// Handle resolve action
if (isset($_GET['resolve']) && is_numeric($_GET['resolve'])) {
    $report_id = (int) $_GET['resolve'];

    $stmt = $conn->prepare("UPDATE reports SET status = 'Resolved' WHERE report_id = ?");
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $stmt->close();

    header("Location: reports.php?msg=resolved");
    exit();
}

// Fetch all reports (newest first)
$sql = "SELECT r.report_id, r.reason, r.details, r.status, r.created_at,
               h.title AS house_title, h.house_id,
               reporter.full_name AS reporter_name,
               reported.full_name AS reported_name
        FROM reports r
        LEFT JOIN houses h ON r.house_id = h.house_id
        LEFT JOIN users reporter ON r.reporter_id = reporter.user_id
        LEFT JOIN users reported ON r.reported_user_id = reported.user_id
        ORDER BY r.created_at DESC";

$result = $conn->query($sql);
?>

<?php $title = "Reports Management"; include("../includes/header.php"); ?>
<!-- Include Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f4f6f8;
        margin: 0;
        padding: 0;
    }

    .admin-reports-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px 16px 40px 16px;
    }

    .admin-reports-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .admin-reports-header h2 {
        color: #2c3e50;
        font-size: 1.8rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .admin-reports-header a {
        color: #040404;
        padding: 8px 18px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.18s;
    }

    .admin-reports-header a:hover {
        background: #e1c17a;
        color: #fff;
    }

    .admin-reports-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(44, 62, 80, 0.08);
        padding: 24px;
        overflow-x: auto;
    }

    .admin-reports-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .admin-reports-table th,
    .admin-reports-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #e0e0e0;
        text-align: left;
        font-size: 0.95rem;
        vertical-align: middle;
    }

    .admin-reports-table th {
        background: #f9fafb;
        color: #2c3e50;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .admin-reports-table tr:hover {
        background: #f5f6f8;
    }

    .admin-status-pending {
        color: #e67e22;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .admin-status-resolved {
        color: #27ae60;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .admin-action-link {
        color: #2c3e50;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: color 0.18s;
    }

    .admin-action-link:hover {
        color: #e67e22;
    }

    @media (max-width: 768px) {
        .admin-reports-table th, .admin-reports-table td {
            font-size: 0.85rem;
            padding: 8px 6px;
        }

        .admin-reports-header h2 {
            font-size: 1.5rem;
        }
    }

    @media (max-width: 500px) {
        .admin-reports-wrapper {
            padding: 12px;
        }

        .admin-reports-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .admin-reports-header a {
            font-size: 0.9rem;
            padding: 6px 12px;
        }

        .admin-reports-table {
            min-width: unset;
            font-size: 0.85rem;
        }

        .admin-reports-table th, .admin-reports-table td {
            padding: 6px 4px;
        }
    }
</style>

<div class="admin-reports-wrapper">
    <div class="admin-reports-header">
        <h2><i class="fas fa-flag"></i> Reports Management</h2>
        <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="admin-reports-card">
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'resolved'): ?>
            <p style="color:#27ae60; font-weight:600; margin-bottom: 18px;">
                <i class="fas fa-check-circle"></i> Report marked as resolved.
            </p>
        <?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <table class="admin-reports-table">
                <tr>
                    <th>ID</th>
                    <th>House</th>
                    <th>Reported User</th>
                    <th>Reporter</th>
                    <th>Reason</th>
                    <th>Details</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['report_id']; ?></td>
                        <td>
                            <?php if (!empty($row['house_id'])): ?>
                                <a href="../admin/view_house.php?id=<?php echo $row['house_id']; ?>">
                                    <i class="fas fa-home"></i> <?php echo htmlspecialchars($row['house_title']); ?>
                                </a>
                            <?php else: ?>
                                <em>General User Report</em>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['reported_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['reporter_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['reason']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['details'])); ?></td>
                        <td class="admin-status-<?php echo strtolower($row['status']); ?>">
                            <?php if(strtolower($row['status']) === 'resolved'): ?>
                                <i class="fas fa-check-circle"></i>
                            <?php else: ?>
                                <i class="fas fa-hourglass-half"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($row['status']); ?>
                        </td>
                        <td>
                            <?php if (strtolower($row['status']) === 'pending'): ?>
                                <a href="reports.php?resolve=<?php echo $row['report_id']; ?>" class="admin-action-link" onclick="return confirm('Mark this report as resolved?');">
                                    <i class="fas fa-clipboard-check"></i> Resolve
                                </a>
                            <?php else: ?>
                                <em>Resolved</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No reports found.</p>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>

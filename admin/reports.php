<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

// ✅ Handle resolve action
if (isset($_GET['resolve']) && is_numeric($_GET['resolve'])) {
    $report_id = (int) $_GET['resolve'];

    $stmt = $conn->prepare("UPDATE reports SET status = 'Resolved' WHERE report_id = ?");
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $stmt->close();

    header("Location: reports.php?msg=resolved");
    exit();
}

// ✅ Fetch all reports (newest first)
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

<?php include("../includes/header.php"); ?>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f4f6f8;
        margin: 0;
        padding: 0;
    }

    .admin-reports-wrapper {
        max-width: 1100px;
        margin: 0 auto 0 auto;
        padding: 0 18px 36px 18px;
    }

    .admin-reports-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
        margin-top: 32px;
    }

    .admin-reports-header h2 {
        color: #2c3e50;
        font-size: 1.7rem;
        margin: 0;
    }

    .admin-reports-header a {
        background: #2c3e50;
        color: #fff;
        border-radius: 7px;
        padding: 8px 22px;
        text-decoration: none;
        font-size: 1.05rem;
        font-weight: 500;
        transition: background 0.18s;
    }

    .admin-reports-header a:hover {
        background: #34495e;
    }

    .admin-reports-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(44, 62, 80, 0.10);
        padding: 32px 24px 24px 24px;
        margin-bottom: 24px;
    }

    .admin-reports-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }

    .admin-reports-table th,
    .admin-reports-table td {
        padding: 12px 10px;
        border-bottom: 1.5px solid #f0f0f0;
        text-align: left;
        font-size: 1.05rem;
    }

    .admin-reports-table th {
        background: #f4f6f8;
        color: #2c3e50;
        font-weight: 600;
    }

    .admin-reports-table tr:last-child td {
        border-bottom: none;
    }

    .admin-reports-table tr:hover {
        background: #f9fafb;
    }

    .admin-status-pending {
        color: #e67e22;
        font-weight: bold;
    }

    .admin-status-resolved {
        color: #27ae60;
        font-weight: bold;
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
</style>
<div class="admin-reports-wrapper">
    <div class="admin-reports-header">
        <h2>Reports Management</h2>
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
    <div class="admin-reports-card">
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'resolved'): ?>
            <p style="color:#27ae60; font-weight:600; margin-bottom: 18px;">✅ Report marked as resolved.</p>
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
                                    <?php echo htmlspecialchars($row['house_title']); ?>
                                </a>
                            <?php else: ?>
                                <em>General User Report</em>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['reported_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['reporter_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['reason']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['details'])); ?></td>
                        <td class="admin-status-<?php echo strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <?php if (strtolower($row['status']) === 'pending'): ?>
                                <a href="reports.php?resolve=<?php echo $row['report_id']; ?>" class="admin-action-link" onclick="return confirm('Mark this report as resolved?');">Resolve</a>
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
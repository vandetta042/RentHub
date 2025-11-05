<?php
require_once __DIR__ . '/../config/db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id <= 0) {
    die("Access denied.");
}

// Fetch all payments by this tenant
$stmt = $conn->prepare("
    SELECT p.id, p.reference, p.amount, p.status, p.paid_at, h.title AS house_name
    FROM payments p
    LEFT JOIN houses h ON p.house_id = h.house_id
    WHERE p.user_id = ?
    ORDER BY p.paid_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Payments - RentHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f9f9f9; }
        .container { max-width: 900px; margin: 40px auto; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 3px 8px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f3f3f3; }
        .status-success { color: green; font-weight: bold; }
        .status-failed { color: red; font-weight: bold; }
        .btn { padding: 6px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class="container">
    <h2>My Payments</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>House</th>
                    <th>Amount (₦)</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['reference']) ?></td>
                        <td><?= htmlspecialchars($row['house_name']) ?></td>
                        <td><?= number_format($row['amount'] / 100, 2) ?></td>
                        <td class="<?= $row['status'] === 'success' ? 'status-success' : 'status-failed' ?>">
                            <?= ucfirst($row['status']) ?>
                        </td>
                        <td><?= date('Y-m-d H:i', strtotime($row['paid_at'])) ?></td>
                        <td>
                            <a class="btn" href="view_receipt.php?id=<?= $row['id'] ?>" target="_blank">View</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No payments found.</p>
    <?php endif; ?>
</div>
</body>
</html>

<?php
require_once __DIR__ . '/../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

$landlord_id = (int) $_SESSION['user_id'];

if (isset($_GET['confirm_id'])) {
    $payment_id = (int) $_GET['confirm_id'];
    $stmt = $conn->prepare("UPDATE payments SET confirmed = 1 WHERE payment_id = ? AND recipient_id = ?");
    $stmt->bind_param("ii", $payment_id, $landlord_id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Payment marked as confirmed.'); window.location='landlord_payment.php';</script>";
    exit;
}

$query = "
    SELECT 
        p.id,
        p.amount,
        p.status,
        p.paid_at,
        p.confirmed,
        u.full_name AS tenant_name,
        u.email AS tenant_email,
        h.title AS house_title
    FROM payments p
    JOIN users u ON p.user_id = u.user_id
    JOIN houses h ON p.house_id = h.house_id
    WHERE p.recipient_id = ?
    ORDER BY p.paid_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php 
$title = "Received Payments";
include("../includes/header.php"); 
?>

<div class="payments-container">
    <h2>Received Payments</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-wrapper">
            <table class="payments-table">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Email</th>
                        <th>House</th>
                        <th>Amount (₦)</th>
                        <th>Date Paid</th>
                        <th>Status</th>
                        <th>Confirm</th>
                        <th>Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['tenant_name']) ?></td>
                            <td><?= htmlspecialchars($row['tenant_email']) ?></td>
                            <td><?= htmlspecialchars($row['house_title']) ?></td>
                            <td><?= number_format($row['amount'] / 100, 2) ?></td>
                            <td><?= htmlspecialchars($row['paid_at']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                            <td>
                                <?php if ($row['confirmed'] == 1): ?>
                                    <span class="btn-confirmed"><i class="fas fa-check-circle"></i> Confirmed</span>
                                <?php else: ?>
                                    <a href="../services/confirm_payments.php?id=<?= $row['id'] ?>" class="btn-confirm"><i class="fas fa-check"></i> Confirm</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="view_receipt.php?id=<?= $row['id'] ?>" target="_blank" class="btn-receipt"><i class="fas fa-receipt"></i> View</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-payments">No payments received yet.</div>
    <?php endif; ?>
</div>

<style>
.payments-container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 0 20px;
}

.payments-container h2 {
    font-size: 1.8rem;
    color: #23272f;
    margin-bottom: 20px;
}

.table-wrapper {
    overflow-x: auto;
}

.payments-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.payments-table th, .payments-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #e0e3e7;
    text-align: left;
    font-size: 0.95rem;
    color: #333;
}

.payments-table th {
    background-color: #607d8b; /* primary color */
    color: #fff;
    font-weight: 600;
}

.payments-table tr:hover {
    background-color: #f9fafb;
}

.btn-confirm, .btn-receipt, .btn-confirmed {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-confirm {
    background-color: #28a745;
    color: #fff;
}

.btn-confirm:hover {
    background-color: #218838;
}

.btn-receipt {
    background-color: #007bff;
    color: #fff;
}

.btn-receipt:hover {
    background-color: #0069d9;
}

.btn-confirmed {
    background-color: #e0e0e0;
    color: #666;
    cursor: default;
}

.no-payments {
    text-align: center;
    padding: 40px 0;
    color: #666;
    font-size: 1.1rem;
}

@media screen and (max-width: 768px) {
    .payments-table th, .payments-table td {
        padding: 10px 8px;
        font-size: 0.85rem;
    }

    .btn-confirm, .btn-receipt, .btn-confirmed {
        padding: 4px 8px;
        font-size: 0.75rem;
    }
}
</style>

<?php
include("../includes/footer.php"); 
?>

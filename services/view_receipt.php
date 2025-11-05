<?php
require_once __DIR__ . '/../config/db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id <= 0) {
    die("Access denied.");
}

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$payment_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT p.*, u.full_name AS tenant_name, r.full_name AS recipient_name, h.title AS house_name
    FROM payments p
    LEFT JOIN users u ON p.user_id = u.user_id
    LEFT JOIN users r ON p.recipient_id = r.user_id
    LEFT JOIN houses h ON p.house_id = h.house_id
    WHERE p.id = ? AND p.user_id = ?
");
$stmt->bind_param("ii", $payment_id, $user_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    die("Payment not found or access denied.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Receipt - <?= htmlspecialchars($payment['reference']) ?></title>
<style>
body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f9f9f9; margin: 0; padding: 0; }
.receipt { max-width: 600px; background: white; margin: 40px auto; padding: 20px 30px; border-radius: 10px; box-shadow: 0 3px 8px rgba(0,0,0,0.1); }
.header { text-align: center; margin-bottom: 20px; }
.header h2 { margin: 0; color: #333; }
.details { margin-bottom: 15px; }
.details p { margin: 6px 0; }
strong { color: #444; }
.print-btn { display: block; width: 100%; text-align: center; padding: 10px; background: #007bff; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; margin-top: 20px; }
.print-btn:hover { background: #0056b3; }
.footer { text-align: center; margin-top: 20px; font-size: 13px; color: gray; }
@media print {
    .print-btn { display: none; }
    body { background: white; }
}
</style>
</head>
<body>
<div class="receipt">
    <div class="header">
        <h2>🏠 RentHub Payment Receipt</h2>
        <p>Reference: <strong><?= htmlspecialchars($payment['reference']) ?></strong></p>
    </div>

    <div class="details">
        <p><strong>Tenant:</strong> <?= htmlspecialchars($payment['tenant_name']) ?></p>
        <p><strong>Recipient:</strong> <?= htmlspecialchars($payment['recipient_name']) ?></p>
        <p><strong>House:</strong> <?= htmlspecialchars($payment['house_name']) ?></p>
        <p><strong>Amount:</strong> ₦<?= number_format($payment['amount'] / 100, 2) ?></p>
        <p><strong>Status:</strong> <?= ucfirst($payment['status']) ?></p>
        <p><strong>Paid At:</strong> <?= date('Y-m-d H:i', strtotime($payment['paid_at'])) ?></p>
    </div>

    <button class="print-btn" onclick="window.print()">🖨 Print or Save as PDF</button>

    <div class="footer">
        <p>Thank you for your payment.</p>
        <p>RentHub – Affordable Student Housing Transparency System</p>
    </div>
</div>
</body>
</html>

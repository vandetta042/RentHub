<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

if (!isset($_GET['reference'])) {
    http_response_code(400);
    echo "No reference provided.";
    exit;
}

$reference = $_GET['reference'];

// Verify transaction with Paystack
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/verify/" . rawurlencode($reference));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$paystack_secret_key}",
    "Cache-Control: no-cache",
]);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    echo "Error contacting Paystack API.";
    exit;
}

$data = json_decode($response, true);

if ($httpcode === 200 && isset($data['status']) && $data['status'] === true && isset($data['data']['status'])) {
    $txStatus = $data['data']['status'];
    $amount = $data['data']['amount'];
    $referenceReturned = $data['data']['reference'];
    $paid_at = $data['data']['paid_at'] ?? date('Y-m-d H:i:s');
    $email = $data['data']['customer']['email'] ?? 'Unknown';
    $metadata = $data['data']['metadata'] ?? [];

    $user_id = $metadata['user_id'] ?? null;
    $recipient_id = $metadata['recipient_id'] ?? null;
    $house_id = $metadata['house_id'] ?? null;

    // 🏠 Fetch house name
    $house_name = "Unknown House";
    if (!empty($house_id)) {
        $houseQuery = $conn->prepare("SELECT title FROM houses WHERE house_id = ?");
        $houseQuery->bind_param("i", $house_id);
        $houseQuery->execute();
        $houseResult = $houseQuery->get_result();
        if ($houseRow = $houseResult->fetch_assoc()) {
            $house_name = $houseRow['title'];
        }
        $houseQuery->close();
    }

    // 👤 Fetch recipient name
    $recipient_name = "Unknown Recipient";
    if (!empty($recipient_id)) {
        $recipientQuery = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
        $recipientQuery->bind_param("i", $recipient_id);
        $recipientQuery->execute();
        $recipientResult = $recipientQuery->get_result();
        if ($recipientRow = $recipientResult->fetch_assoc()) {
            $recipient_name = $recipientRow['full_name'];
        }
        $recipientQuery->close();
    }

    if ($txStatus === 'success') {
        // ✅ Save payment info
        $stmt = $conn->prepare("INSERT INTO payments (user_id, recipient_id, house_id, amount, reference, status, paid_at, created_at)
                                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiidsss", $user_id, $recipient_id, $house_id, $amount, $referenceReturned, $txStatus, $paid_at);
        $stmt->execute();
        $stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Receipt</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        margin: 0;
        padding: 30px;
    }
    .receipt-container {
        background: #fff;
        border-radius: 12px;
        max-width: 600px;
        margin: 0 auto;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        padding: 30px;
    }
    .header {
        text-align: center;
        border-bottom: 2px solid #eee;
        margin-bottom: 20px;
    }
    .header h2 {
        margin: 0;
        color: #2d89ef;
    }
    .details {
        margin: 20px 0;
        font-size: 16px;
    }
    .details p {
        margin: 8px 0;
    }
    .highlight {
        color: #2d89ef;
        font-weight: bold;
    }
    .footer {
        border-top: 2px solid #eee;
        text-align: center;
        margin-top: 30px;
        padding-top: 10px;
        font-size: 14px;
        color: #777;
    }
    .btn {
        display: inline-block;
        margin-top: 20px;
        background: #2d89ef;
        color: white;
        text-decoration: none;
        padding: 10px 18px;
        border-radius: 6px;
    }
    .btn:hover {
        background: #1b5dbf;
    }
</style>
</head>
<body>

<div class="receipt-container" id="receipt">
    <div class="header">
        <h2>Rent Payment Receipt</h2>
        <p>Affordable Student Housing Transparency System</p>
    </div>

    <div class="details">
        <p><strong>Reference:</strong> <?= htmlspecialchars($referenceReturned) ?></p>
        <p><strong>Amount Paid:</strong> <span class="highlight">₦<?= number_format($amount / 100, 2) ?></span></p>
        <p><strong>Paid By (Tenant):</strong> <?= htmlspecialchars($email) ?></p>
        <p><strong>House:</strong> <?= htmlspecialchars($house_name) ?></p>
        <p><strong>Recipient (Landlord/Agent):</strong> <?= htmlspecialchars($recipient_name) ?></p>
        <p><strong>Payment Status:</strong> ✅ Successful</p>
        <p><strong>Paid At:</strong> <?= htmlspecialchars($paid_at) ?></p>
    </div>

    <div class="footer">
        <p>Thank you for your payment!<br>Keep this receipt for your records.</p>
        <a href="#" class="btn" onclick="downloadPDF()">Download as PDF</a>
        <a href="houses/view.php?id=<?= $house_id ?>" class="btn">Back to House</a>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadPDF() {
    const receipt = document.getElementById("receipt");
    const options = {
        margin: 0.5,
        filename: 'rent_receipt_<?= $referenceReturned ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().from(receipt).set(options).save();
}
</script>

</body>
</html>
<?php
    } else {
        echo "<h3>❌ Payment Failed</h3>";
        echo "<p>Status: " . htmlspecialchars($txStatus) . "</p>";
    }
} else {
    echo "<h3>Verification Failed</h3>";
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}
?>

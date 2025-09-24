<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../config/db.php";

// Ensure admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

// --- USER METRICS ---
// Total users by type
$userQuery = $conn->query("SELECT user_type, COUNT(*) AS total FROM users GROUP BY user_type");
$usersByType = [];
while ($row = $userQuery->fetch_assoc()) {
    $usersByType[$row['user_type']] = $row['total'];
}

// Active users
$activeQuery = $conn->query("SELECT user_type, COUNT(*) AS total FROM users WHERE status = 'active' GROUP BY user_type");
$activeUsersByType = [];
while ($row = $activeQuery->fetch_assoc()) {
    $activeUsersByType[$row['user_type']] = $row['total'];
}

//total active users
$totalActiveUsers = array_sum($activeUsersByType);

// --- HOUSE METRICS ---
// Total houses
$totalHouses = $conn->query("SELECT COUNT(*) AS total FROM houses")->fetch_assoc()['total'];

// Houses by status
$statusQuery = $conn->query("SELECT status, COUNT(*) AS total FROM houses GROUP BY status");
$housesByStatus = [];
while ($row = $statusQuery->fetch_assoc()) {
    $housesByStatus[$row['status']] = $row['total'];
}

// Average rent per house type
$rentQuery = $conn->query("SELECT house_type, AVG(price) AS avg_price FROM houses GROUP BY house_type");
$avgRent = [];
while ($row = $rentQuery->fetch_assoc()) {
    $avgRent[$row['house_type']] = round($row['avg_price'], 2);
}

// Optional: Top 5 most contacted/viewed houses (if you have view count or inquiries)
$topHouses = $conn->query("
    SELECT h.title, COUNT(m.message_id) AS inquiries 
    FROM houses h
    LEFT JOIN messages m ON h.house_id = m.house_id
    GROUP BY h.house_id
    ORDER BY inquiries DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Admin Analytics</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
        }

        h2 {
            margin-top: 40px;
        }

        table {
            border-collapse: collapse;
            width: 50%;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .card {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <h1>Admin Analytics Dashboard</h1>

    <div class="card">
        <h2>Users Summary</h2>
        <p>Total Active Users : <strong><?php echo $totalActiveUsers; ?></strong></p>
        <table>
            <tr>
                <th>User Type</th>
                <th>Total</th>
            </tr>
            <?php foreach ($usersByType as $type => $count): ?>
                <tr>
                    <td><?php echo ucfirst($type); ?></td>
                    <td><?php echo $count; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="card">
        <h2>Houses Summary</h2>
        <p>Total Houses: <strong><?php echo $totalHouses; ?></strong></p>
        <table>
            <tr>
                <th>Status</th>
                <th>Count</th>
            </tr>
            <?php foreach ($housesByStatus as $status => $count): ?>
                <tr>
                    <td><?php echo ucfirst($status); ?></td>
                    <td><?php echo $count; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Average Rent by Type</h3>
        <table>
            <tr>
                <th>House Type</th>
                <th>Average Rent</th>
            </tr>
            <?php foreach ($avgRent as $type => $rent): ?>
                <tr>
                    <td><?php echo ucfirst($type); ?></td>
                    <td><?php echo $rent; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="card">
        <h2>Top 5 Houses by Messages/Inquiries</h2>
        <table>
            <tr>
                <th>House</th>
                <th>Messages</th>
            </tr>
            <?php while ($row = $topHouses->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo $row['inquiries']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>

</html>
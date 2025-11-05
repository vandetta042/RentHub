<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

// --- USER METRICS ---
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
$totalActiveUsers = array_sum($activeUsersByType);

// --- HOUSE METRICS ---
$totalHouses = $conn->query("SELECT COUNT(*) AS total FROM houses")->fetch_assoc()['total'];

$statusQuery = $conn->query("SELECT status, COUNT(*) AS total FROM houses GROUP BY status");
$housesByStatus = [];
while ($row = $statusQuery->fetch_assoc()) {
    $housesByStatus[$row['status']] = $row['total'];
}

$rentQuery = $conn->query("SELECT house_type, AVG(price) AS avg_price FROM houses GROUP BY house_type");
$avgRent = [];
while ($row = $rentQuery->fetch_assoc()) {
    $avgRent[$row['house_type']] = round($row['avg_price'], 2);
}

// --- TOP HOUSES ---
$topHouses = $conn->query("
    SELECT h.title, COUNT(m.message_id) AS inquiries 
    FROM houses h
    LEFT JOIN messages m ON h.house_id = m.house_id
    GROUP BY h.house_id
    ORDER BY inquiries DESC
    LIMIT 5
");
?>

<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> -->

<div class="analytics-wrapper">
    <h2 class="analytics-title"><i class="fa-solid fa-chart-line"></i> Admin Analytics Overview</h2>

    <div class="analytics-grid">
        <!-- USERS CARD -->
        <div class="analytics-card user-card">
            <h3><i class="fa-solid fa-users"></i> User Overview</h3>
            <p><strong>Total Active Users:</strong> <?php echo $totalActiveUsers; ?></p>
            <table class="analytics-table">
                <tr><th>User Type</th><th>Total</th></tr>
                <?php foreach (['student', 'worker', 'agent', 'landlord'] as $type): ?>
                    <tr>
                        <td><?php echo ucfirst($type); ?></td>
                        <td><?php echo $usersByType[$type] ?? 0; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- ACTIVE USERS CARD -->
        <div class="analytics-card active-card">
            <h3><i class="fa-solid fa-user-check"></i> Active Users</h3>
            <table class="analytics-table">
                <tr><th>User Type</th><th>Active</th></tr>
                <?php foreach (['student', 'worker', 'agent', 'landlord'] as $type): ?>
                    <tr>
                        <td><?php echo ucfirst($type); ?></td>
                        <td><?php echo $activeUsersByType[$type] ?? 0; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- HOUSES STATUS CARD -->
        <div class="analytics-card house-card">
            <h3><i class="fa-solid fa-house"></i> Houses Overview</h3>
            <p><strong>Total Houses:</strong> <?php echo $totalHouses; ?></p>
            <table class="analytics-table">
                <tr><th>Status</th><th>Count</th></tr>
                <?php foreach ($housesByStatus as $status => $count): ?>
                    <tr><td><?php echo ucfirst($status); ?></td><td><?php echo $count; ?></td></tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- RENT PRICES CARD -->
        <div class="analytics-card rent-card">
            <h3><i class="fa-solid fa-coins"></i> Average Rent by Type</h3>
            <table class="analytics-table">
                <tr><th>House Type</th><th>Average Rent (₦)</th></tr>
                <?php foreach ($avgRent as $type => $rent): ?>
                    <tr><td><?php echo ucfirst($type); ?></td><td><?php echo number_format($rent); ?></td></tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- TOP 5 HOUSES CARD -->
        <div class="analytics-card top-card">
            <h3><i class="fa-solid fa-ranking-star"></i> Top 5 Houses by Inquiries</h3>
            <table class="analytics-table">
                <tr><th>House</th><th>Messages</th></tr>
                <?php while ($row = $topHouses->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo $row['inquiries']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>

<style>
.analytics-wrapper {
    margin-top: 30px;
}

.analytics-title {
    color: #2d5e8f;
    font-size: 1.8rem;
    margin-bottom: 24px;
    text-align: center;
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 28px;
}

.analytics-card {
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(44, 62, 80, 0.1);
    padding: 26px 22px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    background: #fff;
}

.analytics-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 20px rgba(44, 62, 80, 0.15);
}

.analytics-card h3 {
    font-size: 1.2rem;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.analytics-card p {
    font-size: 1rem;
    margin-bottom: 10px;
}

/* Accent colors */
.user-card {
    border-top: 6px solid #3b82f6;
}
.user-card h3, .user-card th {
    color: #3b82f6;
}

.active-card {
    border-top: 6px solid #10b981;
}
.active-card h3, .active-card th {
    color: #10b981;
}

.house-card {
    border-top: 6px solid #6366f1;
}
.house-card h3, .house-card th {
    color: #6366f1;
}

.rent-card {
    border-top: 6px solid #f59e0b;
}
.rent-card h3, .rent-card th {
    color: #f59e0b;
}

.top-card {
    border-top: 6px solid #ef4444;
}
.top-card h3, .top-card th {
    color: #ef4444;
}

/* Table style */
.analytics-table {
    border-collapse: collapse;
    width: 100%;
}
.analytics-table th, .analytics-table td {
    border: 1px solid #ddd;
    padding: 8px;
    font-size: 0.95rem;
    text-align: left;
}
.analytics-table tr:hover {
    background-color: #f9fbfd;
}
.analytics-table th {
    background-color: #f5f8fc;
}

@media (max-width: 768px) {
    .analytics-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}
</style>

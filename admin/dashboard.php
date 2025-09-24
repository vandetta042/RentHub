<?php
session_start();

// Only admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

// Quick stats
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalHouses = $conn->query("SELECT COUNT(*) as count FROM houses")->fetch_assoc()['count'];
$totalReports = $conn->query("SELECT COUNT(*) as count FROM reports WHERE status='pending'")->fetch_assoc()['count'];
?>

<?php include("../includes/header.php"); ?>
<style>
    .admin-dashboard-wrapper {
        max-width: 1100px;
        margin: 0 auto 0 auto;
        padding: 0 18px;
    }

    .admin-dashboard-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
        margin-top: 18px;
    }

    .admin-dashboard-header h1 {
        color: #2c3e50;
        font-size: 2.1rem;
        margin: 0;
    }

    .admin-dashboard-header .admin-badge {
        background: #2c3e50;
        color: #fff;
        border-radius: 8px;
        padding: 8px 18px;
        font-size: 1.08rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .admin-nav {
        display: flex;
        gap: 18px;
        margin-bottom: 28px;
    }

    .admin-nav a {
        background: #2c3e50;
        color: #fff;
        border-radius: 7px;
        padding: 10px 22px;
        text-decoration: none;
        font-size: 1.08rem;
        font-weight: 500;
        transition: background 0.18s;
    }

    .admin-nav a:hover,
    .admin-nav a.active {
        background: #34495e;
        color: #fff;
    }

    .admin-dashboard-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 32px;
        margin-top: 18px;
        justify-content: center;
    }

    .admin-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(44, 62, 80, 0.10);
        padding: 32px 36px 26px 36px;
        min-width: 320px;
        flex: 1 1 340px;
        max-width: 420px;
        margin-bottom: 18px;
    }

    .admin-card h2 {
        margin-top: 0;
        color: #2c3e50;
        font-size: 1.3rem;
        margin-bottom: 12px;
    }

    .admin-card ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .admin-card ul li {
        font-size: 1.08rem;
        color: #34495e;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .admin-card ul li strong {
        color: #27ae60;
        font-size: 1.12rem;
    }

    @media (max-width: 900px) {
        .admin-dashboard-cards {
            flex-direction: column;
            gap: 18px;
        }
    }
</style>
<div class="admin-dashboard-wrapper">
    <div class="admin-dashboard-header">
        <h1>Admin Dashboard</h1>
        <span>👑 <?php echo htmlspecialchars($_SESSION['full_name']); ?> (Admin)</span>
    </div>
    <div class="admin-nav">
        <a href="#analytics">View Analytics</a>
        <a href="users.php">Manage Users</a>
        <a href="manage_house.php">Manage Houses</a>
        <a href="reviews.php">View Reviews</a>
        <a href="reports.php">View Reports</a>
    </div>
    <div class="admin-dashboard-cards">
        <div class="admin-card">
            <h2>Quick Stats</h2>
            <ul>
                <li>Total Users: <strong><?php echo $totalUsers; ?></strong></li>
                <li>Total Houses: <strong><?php echo $totalHouses; ?></strong></li>
                <li>Pending Reports: <strong><?php echo $totalReports; ?></strong></li>
            </ul>
        </div>
        <div class="admin-card" id="analytics">
            <?php include("admin_analytics.php"); ?>
        </div>
    </div>
</div>
<?php include("../includes/footer.php"); ?>
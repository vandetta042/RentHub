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
/* ===== DASHBOARD LAYOUT ===== */
.admin-dashboard-wrapper {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 18px;
}

.admin-dashboard-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 18px 0;
}

.admin-dashboard-header h1 {
    color: #3776b6ff;
    font-size: 2.1rem;
    margin: 0;
}

.admin-dashboard-header span {
    background: #2973beff;
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
    flex-wrap: wrap;
    justify-content: center;
    gap: 16px;
    margin-bottom: 28px;
}

.admin-nav a {
    background: #3f6c99ff;
    color: #fff;
    border-radius: 7px;
    padding: 10px 22px;
    text-decoration: none;
    font-size: 1.05rem;
    font-weight: 500;
    transition: background 0.18s;
}

.admin-nav a:hover,
.admin-nav a.active {
    background: #92afccff;
    color: #fff;
}

/* ===== QUICK STATS CARD ===== */
.admin-card.elongated {
    flex: 1 1 100%;
    max-width: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #2d8f5a, #3ea971);
    color: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(44, 62, 80, 0.1);
    padding: 22px 30px;
    margin-bottom: 28px;
}

.admin-card.elongated h2 {
    color: #fff;
    margin: 0;
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.admin-card.elongated ul {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.admin-card.elongated ul li {
    font-size: 1.05rem;
}

.admin-card.elongated ul li strong {
    color: #fff;
}

/* ===== ANALYTICS SECTION ===== */
.admin-analytics-section {
    margin-top: 24px;
    width: 100%;
}

/* ===== ANALYTICS CARDS ===== */
.analytics-wrapper {
    margin-top: 10px;
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
.user-card { border-top: 6px solid #3b82f6; }
.user-card h3, .user-card th { color: #3b82f6; }

.active-card { border-top: 6px solid #10b981; }
.active-card h3, .active-card th { color: #10b981; }

.house-card { border-top: 6px solid #6366f1; }
.house-card h3, .house-card th { color: #6366f1; }

.rent-card { border-top: 6px solid #f59e0b; }
.rent-card h3, .rent-card th { color: #f59e0b; }

.top-card { border-top: 6px solid #ef4444; }
.top-card h3, .top-card th { color: #ef4444; }

/* Table */
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
    .admin-card.elongated {
        flex-direction: column;
        text-align: center;
        gap: 14px;
    }
    .admin-card.elongated ul {
        justify-content: center;
    }
    .analytics-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}
</style>

<div class="admin-dashboard-wrapper">
    <div class="admin-dashboard-header">
        <h1>Admin Dashboard</h1>
        <span><i class="fa-solid fa-crown"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?> (Admin)</span>
    </div>

    <div class="admin-nav">
        <a href="users.php"><i class="fa-solid fa-users"></i> Manage Users</a>
        <a href="manage_house.php"><i class="fa-solid fa-house"></i> Manage Houses</a>
        <a href="reviews.php"><i class="fa-solid fa-star"></i> View Reviews</a>
        <a href="reports.php"><i class="fa-solid fa-flag"></i> View Reports</a>
    </div>

    <!-- Quick Stats Card -->
    <div class="admin-card elongated">
        <h2><i class="fa-solid fa-chart-simple"></i> Quick Stats</h2>
        <ul>
            <li>Total Users: <strong><?php echo $totalUsers; ?></strong></li>
            <li>Total Houses: <strong><?php echo $totalHouses; ?></strong></li>
            <li>Pending Reports: <strong><?php echo $totalReports; ?></strong></li>
        </ul>
    </div>

    <!-- Analytics Grid Section -->
    <section class="admin-analytics-section">
        <?php include("admin_analytics.php"); ?>
    </section>
</div>

<?php include("../includes/footer.php"); ?>

<?php
session_start();
require_once "../config/db.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
//unread message counter
$userId = $_SESSION['user_id'];
$unreadMsgCount = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($unreadMsgCount);
    $stmt->fetch();
    $stmt->close();
}
//profile picture being called from database
$profilePic = '../public/asset/profile_pictures/default-avatar.png';
if (!empty($_SESSION['profile_pictures'])) {
    $customPic = '../public/asset/profile_pictures/' . htmlspecialchars($_SESSION['profile_pictures']);
    if (file_exists($customPic)) {
        $profilePic = $customPic;
    } else {
        $profilePic = '../public/asset/profile_pictures/default-avatar.png';
    }
}
?>

<?php $title = "Dashboard";
include("../includes/header.php");
?>
<!-- CSS-only sidebar toggle (no JS) -->
<style>
    body {
        background: #f6f7fa;
        font-family: "Segoe UI", Arial, sans-serif;
        margin: 0;
    }

    .dashboard-container {
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 250px;
        background: linear-gradient(135deg, #4a6a93 60%, #6e8bb7 100%);
        border: 1px;
        border-radius: 10px;
        color: #fff;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 32px 20px 20px 20px;
        box-shadow: 2px 0 12px rgba(0, 0, 0, 0.04);
        transition: transform 0.28s ease, box-shadow 0.28s ease;
    }

    .sidebar .logo {
        font-size: 1.7rem;
        font-weight: bold;
        margin-bottom: 32px;
        letter-spacing: 1px;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        width: 100%;
    }

    .sidebar li {
        margin-bottom: 18px;
    }

    .sidebar a {
        color: #e3eaf3;
        text-decoration: none;
        font-size: 1.08rem;
        padding: 10px 18px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        transition: background 0.2s, color 0.2s;
    }

    .sidebar-icon {
        width: 22px;
        text-align: center;
        margin-right: 12px;
        font-size: 1.05rem;
        color: rgba(227, 234, 243, 0.95);
    }

    .sidebar-text {
        display: inline-block;
    }

    .sidebar a.active,
    .sidebar a:hover {
        background: rgba(255, 255, 255, 0.13);
        color: #fff;
    }

    .sidebar-link {
        position: relative;
    }

    .sidebar-badge {
        background: #e74c3c;
        color: #fff;
        font-size: 0.75rem;
        padding: 2px 7px;
        border-radius: 50%;
        margin-left: 10px;
    }

    .main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #f6f7fa;
    }

    .topbar {
        background: #fff;
        border: 1px;
        border-radius: 10px;
        padding: 18px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e3eaf3;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .topbar .welcome {
        font-size: 1.25rem;
        color: #3a4a5d;
        font-weight: 500;
    }

    .topbar-right {
        display: flex;
        align-items: center;
    }

    .notification {
        position: relative;
        margin-right: 18px;
        font-size: 1.3rem;
        color: #4a6a93;
        text-decoration: none;
        transition: color 0.2s;
    }

    .notification:hover {
        color: #e74c3c;
    }

    .notification .badge {
        background: #e74c3c;
        color: #fff;
        font-size: 0.75rem;
        padding: 2px 7px;
        border-radius: 50%;
        position: absolute;
        top: -8px;
        right: -14px;
        z-index: 2;
    }

    .avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e3eaf3;
        background: #fff;
    }

    .content {
        padding: 36px 32px;
    }

    /* new styles: stats and recent activity */
    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 18px;
        margin-top: 6px;
    }

    .stat {
        background: linear-gradient(180deg, #fff, #fbfdff);
        padding: 14px;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(41, 65, 97, 0.06);
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-height: 72px;
        justify-content: center;
    }

    .stat .num {
        font-size: 1.35rem;
        font-weight: 700;
        color: #264566;
    }

    .stat .label {
        font-size: 0.85rem;
        color: #6b7b8f;
    }

    .panel {
        background: #fff;
        padding: 16px;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(41, 65, 97, 0.04);
    }

    .recent-activity {
        margin-top: 18px;
    }

    .recent-item {
        padding: 10px 8px;
        border-bottom: 1px solid #f1f4f7;
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .recent-item:last-child {
        border-bottom: none;
    }

    .recent-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #4a6a93;
    }

    .topbar-search {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .topbar-search input {
        padding: 8px 10px;
        border-radius: 8px;
        border: 1px solid #e6eef6;
        min-width: 180px;
    }

    .topbar-cta {
        background: #1f5eb1ff;
        color: #fff;
        padding: 8px 12px;
        border-radius: 8px;
        text-decoration: none;
    }

    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 28px;
        margin-top: 32px;
    }

    .card {
        background: #fff;
        padding: 16px 12px;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07);
        transition: box-shadow 0.2s, transform 0.2s;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-height: 25px;
        width: 200px;
    }

    .card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        transform: translateY(-4px);
    }

    .card a {
        text-decoration: none;
        color: #4a6a93;
        font-size: 1.18rem;
        font-weight: 600;
        display: flex;
        align-items: center;
    }



    @media (max-width: 900px) {
        .dashboard-container {
            flex-direction: column;
        }

        /* show a toggle under the header */
        .sidebar-toggle-label {
            display: block;
            position: fixed;
            left: 16px;
            top: calc(var(--header-height) + 6px);
            z-index: 1250;
        }

        /* Collapsible sidebar: hidden by default on small screens */
        .sidebar {
            position: fixed;
            left: 0;
            top: var(--header-height);
            bottom: 0;
            width: 240px;
            transform: translateX(-104%);
            z-index: 1100;
            padding-top: calc(var(--header-height) + 6px);
        }

        /* when checked, slide in */
        #sidebar-toggle:checked~.dashboard-container .sidebar {
            transform: translateX(0);
            box-shadow: 2px 0 18px rgba(0, 0, 0, 0.18);
        }

        /* dim main area when sidebar open */
        #sidebar-toggle:checked~.dashboard-container .main::before {
            content: '';
            position: fixed;
            inset: 0 0 0 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 1050;
        }

        .sidebar ul {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .main {
            padding-top: calc(var(--header-height) + 8px);
        }

        .content {
            padding: 18px 10px;
        }
    }
</style>

<!-- Mobile sidebar toggle (checkbox hack) -->
<input type="checkbox" id="sidebar-toggle" aria-hidden="true">
<label for="sidebar-toggle" class="sidebar-toggle-label" aria-hidden="true" title="Open menu">
    <i class="fa-solid fa-bars"></i>
</label>

<div class="dashboard-container">
    <aside class="sidebar">
        <div class="logo">RentHub</div>
        <ul>
            <li><a href="profile.php" class="sidebar-link"><i class="fa-solid fa-user sidebar-icon" aria-hidden="true"></i><span class="sidebar-text">Profile</span></a></li>
            <li><a href="../messages/inbox.php" class="sidebar-link"><i class="fa-solid fa-envelope sidebar-icon" aria-hidden="true"></i><span class="sidebar-text">Enquiries</span> <?php if ($unreadMsgCount > 0): ?><span class="sidebar-badge"><?php echo $unreadMsgCount; ?></span><?php endif; ?></a></li>
            <li><a href="../services/landlord_payment.php" class="sidebar-link"><i class="fas fa-receipt" aria-hidden="true"></i><span class="sidebar-text">received payments</span></a></li>

            <li><a href="../public/logout.php" class="sidebar-link"><i class="fa-solid fa-sign-out-alt sidebar-icon" aria-hidden="true"></i><span class="sidebar-text">Logout</span></a></li>
        </ul>
    </aside>
    <main class="main">
        <header class="topbar">
            <div class="welcome">Welcome back, <?= htmlspecialchars($_SESSION['full_name']); ?> (<?php echo htmlspecialchars($_SESSION['user_type']); ?>)</div>
            <div class="topbar-right">
                <a href="../messages/inbox.php" class="notification" title="Messages">
                    <i class="fa-solid fa-envelope"></i>
                    <?php if ($unreadMsgCount > 0): ?>
                        <span class="badge"><?php echo $unreadMsgCount; ?></span>
                    <?php endif; ?>
                </a>
                <img src="<?php echo $profilePic; ?>" class="avatar" alt="Profile Picture">
            </div>
        </header>
        <section class="content">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
                <h1 style="color:#4a6a93;font-size:2rem;font-weight:600;margin:0;">Dashboard</h1>
                <div class="topbar-search">
                    <input type="search" placeholder="Search listings, messages..." aria-label="Search">
                    <a href="../houses/add.php" class="topbar-cta"><i class="fa-solid fa-plus" style="margin-right:6px"></i>New Listing</a>
                </div>
            </div>

            <!-- quick stats -->
            <div class="stats" aria-hidden="false">
                <div class="stat">
                    <div class="num"><?php echo intval( /* placeholder */12); ?></div>
                    <div class="label">Active Listings</div>
                </div>
                <div class="stat">
                    <div class="num"><?php echo intval( /* placeholder */3); ?></div>
                    <div class="label">New Enquiries</div>
                </div>
                <div class="stat">
                    <div class="num"><?php echo intval( /* placeholder */24); ?></div>
                    <div class="label">Total Views (week)</div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:18px; margin-top:18px; align-items:start;">
                <div class="panel">
                    <h3 style="margin-top:0;">Overview</h3>
                    <div class="cards" style="margin-top:8px;">
                        <div class="card"><a href="../houses/my_listings.php"><i class="fa-solid fa-house fa-fw" style="margin-right:8px;color:#4a6a93;"></i> My Listings</a></div>
                        <div class="card"><a href="#"><i class="fa-solid fa-chart-line fa-fw" style="margin-right:8px;color:#4a6a93;"></i> Performance</a></div>
                        <div class="card"><a href="#"><i class="fa-solid fa-comments fa-fw" style="margin-right:8px;color:#4a6a93;"></i> Enquiries</a></div>
                    </div>
                </div>
                <aside class="panel recent-activity">
                    <h3 style="margin-top:0;">Recent Activity</h3>
                    <div class="recent-item">
                        <div class="recent-dot"></div>
                        <div><strong><i class="fa-solid fa-envelope" style="margin-right:8px;color:#4a6a93"></i>New enquiry</strong>
                            <div style="font-size:0.85rem;color:#6b7b8f;">Someone asked about Listing #23 • 1h ago</div>
                        </div>
                    </div>
                    <div class="recent-item">
                        <div class="recent-dot"></div>
                        <div><strong><i class="fa-solid fa-pencil" style="margin-right:8px;color:#4a6a93"></i>Listing updated</strong>
                            <div style="font-size:0.85rem;color:#6b7b8f;">You edited 'Studio near campus' • 2d ago</div>
                        </div>
                    </div>
                    <div class="recent-item">
                        <div class="recent-dot"></div>
                        <div><strong><i class="fa-solid fa-comments" style="margin-right:8px;color:#4a6a93"></i>New message</strong>
                            <div style="font-size:0.85rem;color:#6b7b8f;">You have 2 unread messages • 3d ago</div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </main>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('sidebar-toggle');
        const sidebar = document.querySelector('.sidebar');
        const toggleLabel = document.querySelector('.sidebar-toggle-label');

        // close sidebar when clicking outside (on the dim overlay)
        document.addEventListener('click', function(e) {
            if (!toggle) return;
            if (!toggle.checked) return;
            if (sidebar.contains(e.target) || (toggleLabel && toggleLabel.contains(e.target))) return;
            // clicked outside sidebar and not on toggle label -> close
            toggle.checked = false;
        });

        // close on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && toggle && toggle.checked) toggle.checked = false;
        });

        // Ensure label click toggles without immediate document click closure
        if (toggleLabel) {
            toggleLabel.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (!toggle) return;
                toggle.checked = !toggle.checked;
            });
        }
    });
</script>
<?php include('../includes/footer.php'); ?>
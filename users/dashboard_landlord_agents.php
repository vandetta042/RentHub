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

<?php include('../includes/header.php'); ?>
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

        .sidebar {
            width: 100%;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 18px 10px;
        }

        .sidebar ul {
            display: flex;
            flex-direction: row;
            gap: 18px;
        }

        .main {
            padding: 0;
        }

        .content {
            padding: 18px 10px;
        }
    }
</style>

<div class="dashboard-container">
    <aside class="sidebar">
        <div class="logo">🏠 RentHub</div>
        <ul>
            <li><a href="profile.php" class="sidebar-link">Profile</a></li>
            <li><a href="../messages/inbox.php" class="sidebar-link">Enquiries <?php if ($unreadMsgCount > 0): ?><span class="sidebar-badge"><?php echo $unreadMsgCount; ?></span><?php endif; ?></a></li>
            <li><a href="../public/logout.php" class="sidebar-link">Logout</a></li>
        </ul>
    </aside>
    <main class="main">
        <header class="topbar">
            <div class="welcome">Welcome back, <?= htmlspecialchars($_SESSION['full_name']); ?> 👋(<?php echo htmlspecialchars($_SESSION['user_type']); ?>)</div>
            <div class="topbar-right">
                <a href="../messages/inbox.php" class="notification" title="Messages">
                    <span>✉️</span>
                    <?php if ($unreadMsgCount > 0): ?>
                        <span class="badge"><?php echo $unreadMsgCount; ?></span>
                    <?php endif; ?>
                </a>
                <img src="<?php echo $profilePic; ?>" class="avatar" alt="Profile Picture">
            </div>
        </header>
        <section class="content">
            <h1 style="color:#4a6a93;font-size:2rem;font-weight:600;">Dashboard</h1>
            <div class="cards">
                <div class="card"><a href="../houses/my_listings.php">🏠 My Listings</a></div>
                <div class="card"><a href="#">📊 Performance</a></div>
            </div>
        </section>
    </main>
</div>
<?php include('../includes/footer.php'); ?>
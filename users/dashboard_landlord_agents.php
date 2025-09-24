<?php
session_start();
require_once "../config/db.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch unread messages count for notification badge
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

// Fetch profile picture path
$profilePic = '../public/asset/profile_pictures/default-avatar.png';
if (!empty($_SESSION['profile_pictures'])) {
    $customPic = '../public/asset/profile_pictures/' . htmlspecialchars($_SESSION['profile_pictures']);
    if (file_exists($customPic)) {
        $profilePic = $customPic;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Landlord/Agent Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <h2 class="logo">🏠 RentHub</h2>
        <ul>
            <li><a href="dashboard_landlord_agents.php" class="sidebar-link active">Dashboard</a></li>
            <li><a href="../houses/my_listings.php" class="sidebar-link">My Listings</a></li>
            <li>
                <a href="../messages/inbox.php" class="sidebar-link">
                    Messages
                    <?php if ($unreadMsgCount > 0): ?>
                        <span class="sidebar-badge"><?php echo $unreadMsgCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li><a href="profile.php" class="sidebar-link">Profile</a></li>
            <li><a href="../public/logout.php" class="sidebar-link">Logout</a></li>
        </ul>
    </aside>

    <!-- Main -->
    <main class="main">
        <header class="topbar">
            <div class="welcome">Welcome back, <?= htmlspecialchars($_SESSION['full_name']); ?> 👋</div>
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
            <h1>Your Dashboard</h1>
            <div class="cards">
                <div class="card"><a href="../houses/my_listings.php">🏠 My Listings</a></div>
                <div class="card"><a href="../messages/inbox.php">✉️ Enquiries <?php if ($unreadMsgCount > 0): ?><span class="badge"><?php echo $unreadMsgCount; ?></span><?php endif; ?></a></div>
                <div class="card">📊 Performance</div>
            </div>
        </section>
    </main>
</body>

</html>
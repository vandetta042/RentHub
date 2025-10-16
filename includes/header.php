<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("../config/db.php");

$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
$unreadCount = 0;

if ($userId) {
    // Use a prepared statement and bind_result (avoids dependency on mysqlnd get_result)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        // bind the single COUNT(*) result
        $stmt->bind_result($unreadCount);
        $stmt->fetch();
        $stmt->close();
    } else {
        error_log("Header prepare failed: " . $conn->error);
        // leave $unreadCount as 0 on failure
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Housing Portal</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            background: #edededff;
        }

        .main-header {
            background: #1f5eb1ff;
            color: #fff;
            padding: 20px 20px;
            margin-bottom: 0;
            box-shadow: 0 2px 8px rgba(197, 140, 34, 0.08);
        }

        .header-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 32px 12px 32px;
        }

        .header-logo {
            font-size: 2rem;
            font-weight: bold;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-logo img {
            height: 36px;
            width: 36px;
            border-radius: 8px;
            background: #fff;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .header-link {
            color: #fff;
            text-decoration: none;
            font-size: 1.08rem;
            font-weight: 500;
            padding: 7px 18px;
            border-radius: 7px;
            transition: background 0.18s, color 0.18s;
        }

        .header-link:hover,
        .header-link.active {
            background: #529ad4ff;
            color: #fff;
        }

        .notif-link {
            position: relative;
            color: #fff;
            text-decoration: none;
            font-size: 1.08rem;
            font-weight: 500;
            padding: 7px 18px 7px 32px;
            border-radius: 7px;
            background: none;
        }

        .notif-badge {
            position: absolute;
            left: 12px;
            top: 7px;
            background: crimson;
            color: #fff;
            font-size: 0.8rem;
            padding: 2px 7px;
            border-radius: 50%;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <header class="main-header">
        <div class="header-flex">
            <div class="header-logo">
                <img src="../public/asset/logo/logo.png" alt="Logo" onerror="this.style.display='none'">
                RentHub Portal
            </div>
            <nav class="header-nav">
                <a href="../public/index.php" class="header-link">Home</a>
                <?php if (isset($_SESSION['user_type'])): ?>
                    <?php if ($_SESSION['user_type'] == 'student' || $_SESSION['user_type'] == 'worker'): ?>
                        <a href="../houses/browse.php" class="header-link">Browse Houses</a>
                    <?php endif; ?>
                    <?php if ($_SESSION['user_type'] == 'landlord'): ?>
                        <a href="../houses/add.php" class="header-link">Add House</a>
                    <?php endif; ?>
                    <?php if ($_SESSION['user_type'] == 'admin'): ?>
                        <a href="../admin/dashboard.php" class="header-link">Admin Dashboard</a>
                    <?php endif; ?>
                    <a href="../users/dashboard.php" class="header-link">Dashboard</a>
                    <a href="../public/logout.php" class="header-link">Logout</a>
                <?php else: ?>
                    <a href="../public/login.php" class="header-link">Login</a>
                    <a href="../public/register.php" class="header-link">Register</a>
                <?php endif; ?>
                <?php if ($userId): ?>
                    <a href="../users/notifications.php" class="notif-link">
                        🔔 Notifications
                        <?php if ($unreadCount > 0): ?>
                            <span class="notif-badge"><?php echo intval($unreadCount); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main style="padding:28px;">
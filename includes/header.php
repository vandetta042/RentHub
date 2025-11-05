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
        $stmt->bind_result($unreadCount);
        $stmt->fetch();
        $stmt->close();
    } else {
        error_log("Header prepare failed: " . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Affordable Student Housing Transparency System">
    <title><?= isset($title) ? htmlspecialchars($title) : 'Housing Portal'; ?></title>

    <!-- Font Awesome & Remix Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

    <!-- Chart.js (needed globally but only used when admin page flag is true) -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->

    <style>
        :root {
            --header-height: 100px;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            background: #ededed;
            min-height: 100vh;
        }

        .main-header {
            background: #1f5eb1;
            color: #fff;
            padding: 0 16px;
            box-shadow: 0 2px 8px rgba(197, 140, 34, 0.08);
            width: 100%;
            position: sticky;
            top: 0;
            z-index: 1300;
            height: var(--header-height);
            display: flex;
            align-items: center;
        }

        .header-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 95%;
            gap: 10px;
            position: relative;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-logo svg {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: rgba(255,255,255,0.06);
            padding: 6px;
        }

        .site-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.6px;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            transition: all 0.3s ease;
        }

        .header-link {
            color: #fff;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            padding: 7px 12px;
            border-radius: 7px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .header-link:hover,
        .header-link.active {
            background: #529ad4;
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

        .mobile-menu-toggle {
            display: none;
            color: white;
            font-size: 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }

            .header-nav {
                display: none;
                width: 100%;
                padding: 10px 0;
                background: #1f5eb1;
                position: absolute;
                top: 100%;
                left: 0;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                opacity: 0;
                visibility: hidden;
                transform: translateY(-10px);
            }

            .header-nav.show {
                display: flex;
                flex-direction: column;
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }

            .header-link, .notif-link {
                width: 100%;
                text-align: center;
                padding: 12px;
                border-radius: 0;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }

            .notif-badge {
                position: static;
                margin-left: 5px;
            }
        }
    </style>
</head>

<body>
    <header class="main-header">
        <div class="header-flex">
            <div class="header-logo">
                <a href="../public/" aria-label="RentHub Home">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
                        <rect fill="#ffffff" opacity="0.06" x="6" y="6" width="88" height="88" rx="10" />
                        <path d="M20 60 L50 25 L80 60 V78 H60 V60 H40 V78 H20 Z" fill="#ffffff" opacity="0.95" />
                        <circle cx="50" cy="52" r="4" fill="#1f5eb1" />
                    </svg>
                </a>
                <span class="site-title">RentHub Portal</span>
            </div>

            <button class="mobile-menu-toggle" aria-expanded="false" aria-controls="main-nav">
                <i class="fa-solid fa-bars"></i>
            </button>

            <nav id="main-nav" class="header-nav">
                <a href="../public/" class="header-link">Home</a>

                <?php if (isset($_SESSION['user_type'])): ?>
                    <?php if (in_array($_SESSION['user_type'], ['student', 'worker'])): ?>
                        <a href="../houses/browse.php" class="header-link">Browse Houses</a>
                    <?php elseif ($_SESSION['user_type'] === 'landlord'): ?>
                        <a href="../houses/add.php" class="header-link">Add House</a>
                    <?php elseif ($_SESSION['user_type'] === 'admin'): ?>
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
                        <i class="fa-solid fa-bell"></i> Notifications
                        <?php if ($unreadCount > 0): ?>
                            <span class="notif-badge"><?= intval($unreadCount); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const nav = document.querySelector('.header-nav');
        const menuButton = document.querySelector('.mobile-menu-toggle');
        const menuIcon = menuButton?.querySelector('i');

        function openMenu() {
            nav.style.display = 'flex';
            setTimeout(() => nav.classList.add('show'), 10);
            menuButton.setAttribute('aria-expanded', 'true');
            menuIcon.classList.replace('fa-bars', 'fa-times');
        }

        function closeMenu() {
            nav.classList.remove('show');
            menuButton.setAttribute('aria-expanded', 'false');
            menuIcon.classList.replace('fa-times', 'fa-bars');
            setTimeout(() => {
                if (!nav.classList.contains('show')) nav.style.display = 'none';
            }, 300);
        }

        function toggleMenu(event) {
            event.stopPropagation();
            if (nav.classList.contains('show')) closeMenu(); else openMenu();
        }

        if (window.innerWidth <= 768) nav.style.display = 'none';
        menuButton.addEventListener('click', toggleMenu);

        document.addEventListener('click', function(e) {
            if (!nav.contains(e.target) && !menuButton.contains(e.target) && nav.classList.contains('show')) {
                closeMenu();
            }
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                nav.style.display = 'flex';
                nav.classList.remove('show');
                menuButton.setAttribute('aria-expanded', 'false');
                menuIcon.classList.replace('fa-times', 'fa-bars');
            } else if (!nav.classList.contains('show')) {
                nav.style.display = 'none';
            }
        });
    });
    </script>

    <main style="padding:28px;">

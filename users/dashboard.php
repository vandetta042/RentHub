<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$usertype = $_SESSION['user_type'];

switch ($usertype) {
    case 'worker':
    case 'student':
        header("Location: dashboard_tenants.php");
        break;

    case 'landlord':
    case 'agent':
        header("Location: dashboard_landlord_agents.php");
        break;

    case 'admin':
        header("Location: ../admin/dashboard.php");
        break;

    default:
        echo "Invalid user type.";
        break;
}
exit();

<?php
session_start();
require_once "../config/db.php";

// Ensure logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

//calling user details from database
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, email, phone, profile_pictures FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<?php include('../includes/header.php'); ?>
<style>
    .profile-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 80vh;
        background: #f6f7fa;
    }

    .profile-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        padding: 38px 32px 32px 32px;
        max-width: 400px;
        width: 100%;
        text-align: center;
        margin-top: 24px;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e3eaf3;
        background: #fff;
        margin-bottom: 18px;
    }

    .profile-name {
        font-size: 1.5rem;
        font-weight: 600;
        color: #4a6a93;
        margin-bottom: 8px;
    }

    .profile-email,
    .profile-phone {
        color: #6c7a89;
        font-size: 1.08rem;
        margin-bottom: 4px;
    }

    .edit-btn {
        display: inline-block;
        background: #4a6a93;
        color: #fff;
        border: none;
        border-radius: 24px;
        padding: 12px 32px;
        font-size: 1.08rem;
        font-weight: 500;
        margin-top: 22px;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
    }

    .edit-btn:hover {
        background: #2d4666;
    }

    .back-link {
        display: inline-block;
        margin-top: 18px;
        color: #4a6a93;
        text-decoration: none;
        font-size: 1rem;
        transition: color 0.2s;
    }

    .back-link:hover {
        color: #e74c3c;
    }

    @media (max-width: 500px) {
        .profile-card {
            padding: 18px 8px;
        }

        .profile-avatar {
            width: 90px;
            height: 90px;
        }
    }
</style>
<div class="profile-container">
    <a href="dashboard.php" class="back-link">← Back to dashboard</a>
    <div class="profile-card">
        <img src="<?php echo $user['profile_pictures'] ? '../public/asset/profile_pictures/' . htmlspecialchars($user['profile_pictures']) : '../public/asset/profile_pictures/default-avatar.png'; ?>"
            class="profile-avatar" alt="Profile Picture">
        <div class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
        <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
        <div class="profile-phone"><?php echo htmlspecialchars($user['phone']); ?></div>
        <a href="edit_profile.php" class="edit-btn">✏️ Edit Profile</a>
    </div>
</div>
<?php include('../includes/footer.php'); ?>
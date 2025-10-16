<?php
session_start();
require_once "../config/db.php";

// Ensure logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch current user data
$stmt = $conn->prepare("SELECT full_name, email, phone, profile_pictures FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>


<?php include('../includes/header.php'); ?>
<style>
    .edit-profile-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 80vh;
        background: #f6f7fa;
    }

    .edit-profile-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        padding: 38px 32px 32px 32px;
        max-width: 440px;
        width: 100%;
        margin-top: 24px;
    }

    .edit-profile-title {
        text-align: center;
        font-size: 1.5rem;
        font-weight: 600;
        color: #4a6a93;
        margin-bottom: 18px;
    }

    .edit-profile-form label {
        display: block;
        font-weight: 500;
        color: #4a6a93;
        margin-bottom: 6px;
        font-size: 1.05rem;
    }

    .edit-profile-form input[type="text"],
    .edit-profile-form input[type="email"],
    .edit-profile-form input[type="password"],
    .edit-profile-form input[type="file"] {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #e3eaf3;
        margin-bottom: 16px;
        font-size: 1rem;
        background: #f6f7fa;
        transition: border 0.2s;
    }

    .edit-profile-form input[type="file"] {
        padding: 6px 0;
    }

    .edit-profile-form input:focus {
        border-color: #4a6a93;
        outline: none;
    }

    .edit-profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e3eaf3;
        background: #fff;
        margin-bottom: 12px;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .edit-profile-section {
        margin-bottom: 24px;
    }

    .edit-profile-btn {
        display: block;
        width: 100%;
        background: #4a6a93;
        color: #fff;
        border: none;
        border-radius: 24px;
        padding: 14px 0;
        font-size: 1.08rem;
        font-weight: 500;
        margin-top: 18px;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
    }

    .edit-profile-btn:hover {
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
        .edit-profile-card {
            padding: 18px 8px;
        }

        .edit-profile-avatar {
            width: 70px;
            height: 70px;
        }
    }
</style>
<div class="edit-profile-container">
    <a href="profile.php" class="back-link">← Back to profile</a>
    <div class="edit-profile-card">
        <div class="edit-profile-title">Edit Profile</div>
        <form method="post" action="update_profile.php" enctype="multipart/form-data" class="edit-profile-form">
            <div class="edit-profile-section">
                <label for="full_name">Full Name</label>
                <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>">
            </div>
            <div class="edit-profile-section">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>
            <div class="edit-profile-section">
                <label for="phone">Phone</label>
                <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>
            <div class="edit-profile-section">
                <label>Profile Picture</label>
                <img src="<?php echo $user['profile_pictures'] ? '../public/asset/profile_pictures/' . htmlspecialchars($user['profile_pictures']) : '../public/asset/profile_pictures/default-avatar.png'; ?>" class="edit-profile-avatar" alt="Profile Picture">
                <input type="file" name="profile_picture">
            </div>
            <div class="edit-profile-section">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password">
            </div>
            <div class="edit-profile-section">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password">
            </div>
            <button type="submit" name="update_profile" class="edit-profile-btn">💾 Save Changes</button>
        </form>
    </div>
</div>
<?php include('../includes/footer.php'); ?>
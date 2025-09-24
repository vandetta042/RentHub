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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-lg rounded-4">
                    <div class="card-header bg-primary text-white text-center py-3 rounded-top-4">
                        <h4 class="mb-0">Edit Profile</h4>
                    </div>
                    <div class="card-body p-4">

                        <!-- Update Info -->
                        <form method="post" action="update_profile.php" enctype="multipart/form-data">
                            <h5 class="fw-bold mb-3">Personal Info</h5>
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>

                            <h5 class="fw-bold mt-4 mb-3">Profile Picture</h5>
                            <div class="mb-3">
                                <img src="<?php echo $user['profile_pictures'] ? '../public/profile/' . htmlspecialchars($user['profile_pictures']) : 'https://via.placeholder.com/100'; ?>"
                                    class="rounded-circle mb-2" width="100" height="100">
                                <input type="file" name="profile_picture" class="form-control">
                            </div>

                            <h5 class="fw-bold mt-4 mb-3">Change Password</h5>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control">
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-success btn-lg rounded-pill">💾 Save Changes</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
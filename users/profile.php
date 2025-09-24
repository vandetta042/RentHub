<?php
session_start();
require_once "../config/db.php";

// Ensure logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, email, phone, profile_pictures FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg rounded-4">
                    <div class="card-body text-center p-4">
                        <img src="<?php echo $user['profile_pictures'] ? '../public/profile/' . htmlspecialchars($user['profile_pictures']) : 'https://via.placeholder.com/150'; ?>"
                            class="rounded-circle mb-3" alt="Profile Picture" width="120" height="120">

                        <h4 class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                        <p class="text-muted mb-1"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></p>

                        <a href="edit_profile.php" class="btn btn-primary btn-lg mt-3 rounded-pill px-4">
                            ✏️ Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
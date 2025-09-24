<?php
session_start();
require_once "../config/db.php";

// ✅ Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if (isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $phone     = trim($_POST['phone']);

    // ✅ Handle profile picture upload
    $profilePic = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array(strtolower($ext), $allowed)) {
            $fileName = "user_" . $userId . "_" . time() . "." . $ext;
            $uploadPath = "../public/profile_pics/" . $fileName;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath)) {
                $profilePic = $fileName;
            }
        }
    }

    // ✅ Build query
    if ($profilePic) {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, profile_picture = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $full_name, $phone, $profilePic, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $full_name, $phone, $userId);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update profile. Please try again.";
    }

    header("Location: profile.php");
    exit();
}

<?php
session_start();
require_once "../config/db.php";

// must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if (isset($_POST['update_profile'])) {
    // Only update fields that are set and not empty
    $fields = [];
    $params = [];
    $types = '';

    if (isset($_POST['full_name']) && $_POST['full_name'] !== '') {
        $fields[] = 'full_name = ?';
        $params[] = trim($_POST['full_name']);
        $types .= 's';
    }
    if (isset($_POST['email']) && $_POST['email'] !== '') {
        $fields[] = 'email = ?';
        $params[] = trim($_POST['email']);
        $types .= 's';
    }
    if (isset($_POST['phone']) && $_POST['phone'] !== '') {
        $fields[] = 'phone = ?';
        $params[] = trim($_POST['phone']);
        $types .= 's';
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array(strtolower($ext), $allowed)) {
            $fileName = "user_" . $userId . "_" . time() . "." . $ext;
            $uploadPath = "../public/asset/profile_pictures/" . $fileName;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath)) {
                $fields[] = 'profile_pictures = ?';
                $params[] = $fileName;
                $types .= 's';
            }
        }
    }

    // Always run update if any field or profile picture is present
    if (!empty($fields)) {
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
        $types .= 'i';
        $params[] = $userId;
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            // If profile picture was updated, update session for immediate effect
            if (isset($fileName)) {
                $_SESSION['profile_pictures'] = $fileName;
            }
            $_SESSION['success'] = "Profile updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update profile. Please try again.";
        }
        $stmt->close();
    }
    header("Location: profile.php");
    exit();
}

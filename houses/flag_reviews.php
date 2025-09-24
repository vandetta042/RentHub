<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../users/dashboard.php'));
    exit();
}

$review_id = isset($_POST['review_id']) ? (int) $_POST['review_id'] : 0;
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
$flagger = (int) $_SESSION['user_id'];
$referer = $_SERVER['HTTP_REFERER'] ?? '../users/dashboard.php';

if ($review_id <= 0) {
    header("Location: {$referer}?msg=invalid");
    exit();
}

// 1) Prevent flagging your own review
$stm = $conn->prepare("SELECT user_id FROM reviews WHERE review_id = ?");
$stm->bind_param("i", $review_id);
$stm->execute();
$stm->bind_result($owner_id);
$stm->fetch();
$stm->close();

if (!$owner_id) {
    header("Location: {$referer}?msg=notfound");
    exit();
}
if ($owner_id == $flagger) {
    header("Location: {$referer}?msg=cannot_flag_self");
    exit();
}

// 2) Prevent duplicate flag by same user
$chk = $conn->prepare("SELECT flag_id FROM review_flags WHERE review_id = ? AND flagged_by = ?");
$chk->bind_param("ii", $review_id, $flagger);
$chk->execute();
$chk->store_result();
if ($chk->num_rows > 0) {
    $chk->close();
    header("Location: {$referer}?msg=already_flagged");
    exit();
}
$chk->close();

// 3) Insert flag
$ins = $conn->prepare("INSERT INTO review_flags (review_id, flagged_by, reason, created_at) VALUES (?, ?, ?, NOW())");
$ins->bind_param("iis", $review_id, $flagger, $reason);
$ok = $ins->execute();
$ins->close();

if ($ok) {
    // 4) If reviews table has a `status` column, set it to 'flagged' (safe: check column existence)
    $colCheck = $conn->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reviews' AND COLUMN_NAME = 'status'");
    if ($colCheck && $colCheck->num_rows > 0) {
        $upd = $conn->prepare("UPDATE reviews SET status = 'flagged' WHERE review_id = ? AND status <> 'deleted' AND status <> 'flagged'");
        $upd->bind_param("i", $review_id);
        $upd->execute();
        $upd->close();
    }

    // redirect back with success
    header("Location: {$referer}?msg=flagged");
    exit();
} else {
    header("Location: {$referer}?msg=flag_failed");
    exit();
}
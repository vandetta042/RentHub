<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_houses.php");
    exit();
}

$house_id = (int) $_GET['id'];

// Fetch house info
$stmt = $conn->prepare("SELECT h.*, u.full_name AS owner_name, u.email AS owner_email
                        FROM houses h
                        JOIN users u ON h.user_id = u.user_id
                        WHERE h.house_id = ?");
$stmt->bind_param("i", $house_id);
$stmt->execute();
$house = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$house) {
    header("Location: manage_houses.php?error=notfound");
    exit();
}

// Fetch images
$images = [];
$img_stmt = $conn->prepare("SELECT image_url FROM house_images WHERE house_id = ?");
$img_stmt->bind_param("i", $house_id);
$img_stmt->execute();
$result = $img_stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $images[] = $row['image_url'];
}
$img_stmt->close();
?>

<?php include("../includes/header.php"); ?>
<h2>House Details</h2>
<p><a href="manage_house.php">← Back to Houses</a></p>
<p><a href="reports.php">← Back to reports</a></p>

<h3><?php echo htmlspecialchars($house['title']); ?></h3>
<p><strong>Location:</strong> <?php echo htmlspecialchars($house['location']); ?></p>
<p><strong>Price:</strong> <?php echo number_format($house['price']); ?></p>
<p><strong>Status:</strong> <?php echo htmlspecialchars($house['status']); ?></p>
<p><strong>Owner:</strong> <?php echo htmlspecialchars($house['owner_name']); ?> (<?php echo htmlspecialchars($house['owner_email']); ?>)</p>
<p><strong>Created:</strong> <?php echo $house['created_at']; ?></p>
<p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($house['description'])); ?></p>

<h4>Images</h4>
<?php if (!empty($images)): ?>
    <?php foreach ($images as $img): ?>
        <img src="../<?php echo htmlspecialchars($img); ?>" width="200" style="margin:5px; border:1px solid #ccc;">
    <?php endforeach; ?>
<?php else: ?>
    <p>No images uploaded.</p>
<?php endif; ?>

<?php include("../includes/footer.php"); ?>
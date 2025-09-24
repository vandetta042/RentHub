<?php
session_start();

// ✅ Only logged in users of type student/worker can access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['student', 'worker'])) {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

// ✅ Validate request
if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$house_id = intval($_GET['id']);

// ✅ Fetch house + landlord info
$house = $conn->query("
    SELECT h.*, u.full_name, u.phone, u.email, u.user_id 
    FROM houses h 
    JOIN users u ON h.user_id = u.user_id 
    WHERE h.house_id = $house_id
")->fetch_assoc();

if (!$house) {
    die("House not found.");
}
?>

<?php
// ======================= REPORT LOGIC =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    $reason  = trim($_POST['reason']);
    $details = trim($_POST['details']);
    $reporter_id = $_SESSION['user_id'];
    $house_id    = $house['house_id'];
    $reported_user_id = $house['user_id']; // landlord/agent who posted

    if (!empty($reason)) {
        $stmt = $conn->prepare("INSERT INTO reports 
            (house_id, reported_user_id, reporter_id, reason, details, status, created_at) 
            VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
        $stmt->bind_param("iiiss", $house_id, $reported_user_id, $reporter_id, $reason, $details);
        if ($stmt->execute()) {
            echo "<p style='color:green;'>✅ Report submitted successfully.</p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to submit report.</p>";
        }
    } else {
        echo "<p style='color:red;'>Reason is required.</p>";
    }
}
?>


<?php include("../includes/header.php"); ?>
<style>
    .view-nav {
        display: flex;
        gap: 18px;
        margin-bottom: 28px;
    }

    .view-nav a {
        display: inline-block;
        background: #f4f6f8;
        color: #2c3e50;
        border: 1.5px solid #2c3e50;
        border-radius: 8px;
        padding: 8px 22px;
        font-size: 1.05rem;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.18s, color 0.18s, border 0.18s;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.04);
    }

    .view-nav a:hover {
        background: #2c3e50;
        color: #fff;
        border: 1.5px solid #2c3e50;
        text-decoration: none;
    }

    .house-view-main {
        display: flex;
        flex-wrap: wrap;
        gap: 48px;
        max-width: 1400px;
        margin: 0 auto 48px auto;
        align-items: flex-start;
    }

    .house-view-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 4px 32px rgba(44, 62, 80, 0.10);
        padding: 44px 44px 36px 44px;
        flex: 2 1 700px;
        min-width: 420px;
        max-width: 900px;
    }

    .house-view-images-row {
        display: flex;
        gap: 22px;
        overflow-x: auto;
        padding-bottom: 16px;
        margin-bottom: 28px;
        margin-top: 24px;
        background: #f9fafb;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.06);
    }

    .house-view-images-row img {
        width: 260px;
        height: 170px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.10);
        cursor: pointer;
        transition: transform 0.15s;
    }

    .house-view-images-row img:hover {
        transform: scale(1.09);
        box-shadow: 0 4px 22px rgba(44, 62, 80, 0.22);
    }

    .house-view-images-row {
        display: flex;
        gap: 14px;
        overflow-x: auto;
        padding-bottom: 8px;
        margin-bottom: 18px;
        margin-top: 10px;
    }

    .house-view-images-row img {
        width: 180px;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.10);
        cursor: pointer;
        transition: transform 0.15s;
    }

    .house-view-images-row img:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 16px rgba(44, 62, 80, 0.18);
    }

    .download-link {
        display: block;
        text-align: center;
        font-size: 0.95rem;
        color: #2c3e50;
        margin-top: 2px;
        text-decoration: underline;
    }

    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        overflow: auto;
        background: rgba(44, 62, 80, 0.45);
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background: #fff;
        border-radius: 12px;
        padding: 18px 18px 10px 18px;
        max-width: 90vw;
        max-height: 80vh;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .modal-content img {
        max-width: 80vw;
        max-height: 60vh;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .close-modal {
        background: #2c3e50;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 6px 16px;
        font-size: 1rem;
        cursor: pointer;
        margin-bottom: 8px;
    }

    .msg-btn {
        background: #2c3e50;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px 22px;
        font-size: 1.08rem;
        font-weight: bold;
        cursor: pointer;
        margin-top: 10px;
        transition: background 0.2s;
    }

    .msg-btn:hover {
        background: #34495e;
    }

    .house-view-images-col {
        flex: 1 1 260px;
        min-width: 220px;
        max-width: 340px;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .house-view-images {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .house-view-images img {
        width: 100%;
        max-width: 320px;
        height: 160px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.10);
    }

    .review-section,
    .report-section {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.06);
        padding: 22px 20px 16px 20px;
        max-width: 1100px;
        margin: 0 auto 30px auto;
    }

    .review-section h3,
    .report-section h3 {
        color: #2c3e50;
        margin-top: 0;
    }

    .review-section form,
    .report-section form {
        margin-bottom: 0;
    }

    .review-section textarea,
    .report-section textarea {
        width: 100%;
        border-radius: 7px;
        border: 1px solid #d1d5db;
        padding: 8px;
        font-size: 1rem;
        margin-bottom: 10px;
    }

    .review-section select,
    .report-section select {
        border-radius: 7px;
        border: 1px solid #d1d5db;
        padding: 7px;
        font-size: 1rem;
        margin-bottom: 10px;
    }

    .review-section button,
    .report-section button {
        background: #2c3e50;
        color: #fff;
        border: none;
        border-radius: 7px;
        padding: 8px 18px;
        font-size: 1rem;
        font-weight: bold;
        cursor: pointer;
        margin-top: 8px;
        transition: background 0.2s;
    }

    .review-section button:hover,
    .report-section button:hover {
        background: #34495e;
    }

    .review-section label,
    .report-section label {
        font-weight: 500;
        color: #2c3e50;
    }
</style>
<div class="view-nav">
    <a href="browse.php">← Back to Browse</a>
    <a href="../users/dashboard.php">← Back to Dashboard</a>
</div>
<div class="house-view-main">
    <div class="house-view-card">
        <h2 class="house-view-title"><?php echo htmlspecialchars($house['title']); ?></h2>
        <div class="house-view-price">₦<?php echo number_format($house['price']); ?></div>
        <div class="house-view-meta">Type: <?php echo htmlspecialchars($house['house_type']); ?> | Location: <?php echo htmlspecialchars($house['location']); ?></div>
        <div class="house-view-desc"><?php echo nl2br(htmlspecialchars($house['description'])); ?></div>
        <div class="contact-info">
            <h3>Contact Info</h3>
            <p><b>Posted by:</b> <?php echo htmlspecialchars($house['full_name']); ?></p>
            <p><b>Email:</b> <?php echo htmlspecialchars($house['email']); ?></p>
            <p><b>Phone:</b> <?php echo htmlspecialchars($house['phone']); ?></p>
            <?php if ($_SESSION['user_id'] != $house['user_id']): ?>
                <form action="../messages/conversation.php" method="get" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?php echo (int)$house['user_id']; ?>">
                    <input type="hidden" name="house_id" value="<?php echo (int)$house['house_id']; ?>">
                    <button type="submit" class="msg-btn">💬 Message Landlord/Agent</button>
                </form>
            <?php else: ?>
                <span style="color:#888;"><em>This is your property.</em></span>
            <?php endif; ?>
        </div>
        <div class="house-view-images-row">
            <?php
            $imgs = $conn->query("SELECT * FROM house_images WHERE house_id = $house_id");
            $hasImg = false;
            $imgIndex = 0;
            while ($img = $imgs->fetch_assoc()) {
                $hasImg = true;
                $imgUrl = '../' . htmlspecialchars($img['image_url']);
                $imgName = basename($img['image_url']);
                echo "<div><img src='$imgUrl' alt='House Image' onclick=\"openModal('$imgUrl')\"><a href='$imgUrl' download class='download-link'>Download</a></div>";
                $imgIndex++;
            }
            if (!$hasImg) echo "<span style='color:#888;'>No images available</span>";
            ?>
        </div>
    </div>
</div>
<!-- Modal for image preview -->
<div id="imgModal" class="modal" onclick="closeModal(event)">
    <div class="modal-content">
        <button class="close-modal" onclick="closeModal(event)">Close</button>
        <img id="modalImg" src="" alt="Preview">
        <a id="modalDownload" href="#" download class="download-link">Download Image</a>
    </div>
</div>
<script>
    function openModal(imgUrl) {
        document.getElementById('imgModal').style.display = 'flex';
        document.getElementById('modalImg').src = imgUrl;
        document.getElementById('modalDownload').href = imgUrl;
    }

    function closeModal(e) {
        if (e.target.classList.contains('modal') || e.target.classList.contains('close-modal')) {
            document.getElementById('imgModal').style.display = 'none';
            document.getElementById('modalImg').src = '';
        }
    }
</script>
<div class="review-section">
    <h3>Reviews</h3>
    <!-- Leave a Review -->
    <form method="post" style="margin-bottom:20px;">
        <label>Rating:</label>
        <select name="rating" required>
            <option value="">--Select--</option>
            <option value="1">⭐</option>
            <option value="2">⭐⭐</option>
            <option value="3">⭐⭐⭐</option>
            <option value="4">⭐⭐⭐⭐</option>
            <option value="5">⭐⭐⭐⭐⭐</option>
        </select><br><br>
        <label>Comment:</label><br>
        <textarea name="comment" rows="3"></textarea><br><br>
        <button type="submit">Submit Review</button>
    </form>
    <!-- Display Reviews -->
    <?php include("../includes/reviews_display.php"); ?>
</div>
<div class="report-section">
    <h3>Report This Listing</h3>
    <form method="post" action="view.php?id=<?php echo (int)$house['house_id']; ?>">
        <label for="reason">Reason:</label>
        <select name="reason" required>
            <option value="">-- Select Reason --</option>
            <option value="Fake listing">Fake listing</option>
            <option value="Wrong price">Wrong price</option>
            <option value="Fraudulent landlord">Fraudulent landlord</option>
            <option value="House already occupied">House already occupied</option>
            <option value="Other">Other</option>
        </select><br><br>
        <label for="details">Additional Details:</label><br>
        <textarea name="details" rows="3"></textarea><br><br>
        <button type="submit" name="submit_report">Submit Report</button>
    </form>
</div>
<?php include("../includes/footer.php"); ?>
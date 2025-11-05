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


<?php $title = "View Details"; include("../includes/header.php"); ?>

<style>
/* General wrapper */
.house-view-container {
    max-width: 1100px;
    margin: 20px auto;
    padding: 0 16px;
    display: flex;
    flex-direction: column;
    gap: 30px;
}

/* Navigation link */
.view-nav a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #3d78b3;
    font-weight: bold;
    text-decoration: none;
    margin-bottom: 10px;
    transition: all 0.2s;
}
.view-nav a:hover { color: #ce991f; }

/* Main house card */
.house-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(60,83,106,0.06);
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* House header */
.house-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
}
.house-header h2 {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.8rem;
    color: #2c3e50;
}
.house-price {
    font-size: 1.3rem;
    font-weight: bold;
    color: #27ae60;
}

/* Meta info */
.house-meta {
    font-size: 0.95rem;
    color: #555;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

/* Description */
.house-desc {
    font-size: 1rem;
    line-height: 1.5;
}

/* Contact card */
.contact-card {
    background: #f9fafb;
    padding: 16px 18px;
    border-radius: 10px;
    box-shadow: 0 1px 8px rgba(44,62,80,0.06);
}
.contact-card h3 { margin-top: 0; color: #2c5a89; }
.contact-card p { margin: 6px 0; }

/* Message button */
.msg-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #3276ba;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 18px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s;
}
.msg-btn:hover { background: #2b6197; transform: translateY(-1px) scale(1.03); }

/* Image gallery */
.house-images-row {
    display: flex;
    gap: 14px;
    overflow-x: auto;
    padding-bottom: 10px;
}
.house-images-row div {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.house-images-row img {
    width: 180px;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(44,62,80,0.10);
    cursor: pointer;
    transition: transform 0.2s;
}
.house-images-row img:hover { transform: scale(1.05); }

/* Download link */
.download-link {
    font-size: 0.85rem;
    margin-top: 4px;
    color: #3d3d3d;
    text-decoration: underline;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left:0; top:0;
    width: 100vw;
    height: 100vh;
    background: rgba(44,62,80,0.45);
    justify-content: center;
    align-items: center;
}
.modal-content {
    background: #fff;
    border-radius: 12px;
    padding: 18px;
    max-width: 90vw;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.modal-content img { max-width: 80vw; max-height: 60vh; border-radius: 8px; margin-bottom: 10px; }
.close-modal {
    background: #3c7ec1;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 6px 16px;
    cursor: pointer;
    margin-bottom: 8px;
}

/* Review & Report sections */
.review-section,
.report-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(60,83,106,0.06);
    padding: 22px;
}
.review-section h3,
.report-section h3 { margin-top: 0; color: #2c5a89; }
.review-section form textarea,
.report-section form textarea,
.review-section form select,
.report-section form select {
    width: 100%;
    border-radius: 7px;
    border: 1px solid #d1d5db;
    padding: 8px;
    font-size: 1rem;
    margin-bottom: 10px;
}
.review-section form button,
.report-section form button {
    background: #2f68a1;
    color: #fff;
    border: none;
    border-radius: 7px;
    padding: 8px 18px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.2s;
}
.review-section form button:hover,
.report-section form button:hover { background: #2b6197; }

/* Responsive */
@media (max-width: 768px) {
    .house-header { flex-direction: column; gap: 12px; }
    .house-images-row img { width: 140px; height: 100px; }
}
</style>

<div class="house-view-container">

    <div class="view-nav">
        <a href="browse.php"><i class="fas fa-arrow-left"></i> Back to Browse</a>
    </div>

    <div class="house-card">
        <div class="house-header">
            <h2><i class="fas fa-home"></i> <?php echo ucwords(htmlspecialchars($house['title'])); ?></h2>
            <div class="house-price">₦<?php echo number_format($house['price']); ?></div>
        </div>
        <div class="house-meta">
            <span><i class="fas fa-building"></i> Type: <?php echo htmlspecialchars($house['house_type']); ?></span>
            <span><i class="fas fa-map-marker-alt"></i> Location: <?php echo htmlspecialchars($house['location']); ?></span>
        </div>
        <div class="house-desc"><?php echo nl2br(htmlspecialchars($house['description'])); ?></div>

        <!-- Contact info -->
        <div class="contact-card">
            <h3><i class="fas fa-user"></i> Contact Info</h3>
            <p><b>Posted by:</b> <?php echo htmlspecialchars($house['full_name']); ?></p>
            <p><b>Email:</b> <?php echo htmlspecialchars($house['email']); ?></p>
            <p><b>Phone:</b> <?php echo htmlspecialchars($house['phone']); ?></p>
            <?php if ($_SESSION['user_id'] != $house['user_id']): ?>
                <form action="../messages/conversation.php" method="get" style="margin-top:10px;">
                    <input type="hidden" name="user_id" value="<?php echo (int)$house['user_id']; ?>">
                    <input type="hidden" name="house_id" value="<?php echo (int)$house['house_id']; ?>">
                    <button type="submit" class="msg-btn"><i class="fas fa-comment-dots"></i> Message Landlord/Agent</button>
                </form>
            <?php else: ?>
                <span style="color:#888;"><em>This is your property.</em></span>
            <?php endif; ?>
        </div>

        <!-- Images -->
        <div class="house-images-row">
            <?php
            $imgs = $conn->query("SELECT * FROM house_images WHERE house_id = $house_id");
            $hasImg = false;
            while ($img = $imgs->fetch_assoc()):
                $hasImg = true;
                $imgUrl = '../' . htmlspecialchars($img['image_url']);
            ?>
                <div>
                    <img src="<?php echo $imgUrl; ?>" alt="House Image" onclick="openModal('<?php echo $imgUrl; ?>')">
                    <a href="<?php echo $imgUrl; ?>" download class="download-link"><i class="fas fa-download"></i> Download</a>
                </div>
            <?php endwhile; 
            if (!$hasImg) echo "<span style='color:#888;'>No images available</span>"; ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="imgModal" class="modal" onclick="closeModal(event)">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal(event)"><i class="fas fa-times"></i> Close</button>
            <img id="modalImg" src="" alt="Preview">
            <a id="modalDownload" href="#" download class="download-link"><i class="fas fa-download"></i> Download Image</a>
        </div>
    </div>

    <div class="review-section">
        <h3><i class="fas fa-star"></i> Reviews</h3>
        <!-- Leave a Review -->
        <form method="post">
            <label>Rating:</label>
            <select name="rating" required>
                <option value="">--Select--</option>
                <option value="1">⭐</option>
                <option value="2">⭐⭐</option>
                <option value="3">⭐⭐⭐</option>
                <option value="4">⭐⭐⭐⭐</option>
                <option value="5">⭐⭐⭐⭐⭐</option>
            </select>
            <label>Comment:</label>
            <textarea name="comment" rows="3"></textarea>
            <button type="submit"><i class="fas fa-paper-plane"></i> Submit Review</button>
        </form>
        <?php include("../includes/reviews_display.php"); ?>
    </div>

    <div class="report-section">
        <h3><i class="fas fa-flag"></i> Report This Listing</h3>
        <form method="post" action="view.php?id=<?php echo (int)$house['house_id']; ?>">
            <label for="reason">Reason:</label>
            <select name="reason" required>
                <option value="">-- Select Reason --</option>
                <option value="Fake listing">Fake listing</option>
                <option value="Wrong price">Wrong price</option>
                <option value="Fraudulent landlord">Fraudulent landlord</option>
                <option value="House already occupied">House already occupied</option>
                <option value="Other">Other</option>
            </select>
            <label for="details">Additional Details:</label>
            <textarea name="details" rows="3"></textarea>
            <button type="submit" name="submit_report"><i class="fas fa-paper-plane"></i> Submit Report</button>
        </form>
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

<?php include("../includes/footer.php"); ?>

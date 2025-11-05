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

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
    .house-wrapper {
        max-width: 1000px;
        margin: 20px auto;
        padding: 16px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.05);
    }

    .house-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .house-header h2 {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.8rem;
        margin: 0;
        color: #2c3e50;
    }

    .house-back-links a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        color: #040404;
        font-weight: 600;
        margin-right: 10px;
        padding: 6px 12px;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .house-back-links a:hover {
        background: #f0c971;
        color: #fff;
    }

    .house-details p {
        margin: 8px 0;
        font-size: 1rem;
        line-height: 1.5;
    }

    .house-details strong {
        color: #2c3e50;
    }

    .house-images {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin-top: 12px;
    }

    .house-images img {
        width: 100%;
        border-radius: 8px;
        border: 1px solid #ddd;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .house-images img:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* Lightbox */
    .lightbox-overlay {
        position: fixed;
        top:0;
        left:0;
        width:100%;
        height:100%;
        background: rgba(0,0,0,0.85);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        flex-direction: column;
    }

    .lightbox-overlay img {
        max-width: 90%;
        max-height: 80%;
        border-radius: 8px;
        margin-bottom: 16px;
    }

    .lightbox-close, .lightbox-prev, .lightbox-next {
        color: #fff;
        font-size: 2rem;
        cursor: pointer;
        margin: 5px;
    }

    .lightbox-nav {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 40px;
    }

    @media (max-width: 768px) {
        .house-header h2 {
            font-size: 1.5rem;
        }
        .house-back-links a {
            font-size: 0.9rem;
            padding: 4px 10px;
        }
    }
</style>

<div class="house-wrapper">
    <div class="house-header">
        <h2><i class="fas fa-home"></i> <?php echo htmlspecialchars($house['title']); ?></h2>
        <div class="house-back-links">
            <a href="manage_house.php"><i class="fas fa-arrow-left"></i> Back to Houses</a>
            <a href="reports.php"><i class="fas fa-flag"></i> Back to Reports</a>
        </div>
    </div>

    <div class="house-details">
        <p><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> <?php echo htmlspecialchars($house['location']); ?></p>
        <p><i class="fas fa-money-bill-wave"></i> <strong>Price:</strong> ₦<?php echo number_format($house['price']); ?></p>
        <p><i class="fas fa-info-circle"></i> <strong>Status:</strong> <?php echo htmlspecialchars($house['status']); ?></p>
        <p><i class="fas fa-user"></i> <strong>Owner:</strong> <?php echo htmlspecialchars($house['owner_name']); ?> (<?php echo htmlspecialchars($house['owner_email']); ?>)</p>
        <p><i class="fas fa-calendar-alt"></i> <strong>Posted On:</strong> <?php echo $house['created_at']; ?></p>
        <p><i class="fas fa-align-left"></i> <strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($house['description'])); ?></p>
    </div>

    <h3><i class="fas fa-images"></i> Images</h3>
    <?php if (!empty($images)): ?>
        <div class="house-images">
            <?php foreach ($images as $img): ?>
                <img src="../<?php echo htmlspecialchars($img); ?>" alt="House Image">
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No images uploaded.</p>
    <?php endif; ?>
</div>

<!-- Lightbox overlay with prev/next -->
<div class="lightbox-overlay" id="lightbox">
    <span class="lightbox-close" id="lightbox-close"><i class="fas fa-times"></i></span>
    <img src="" id="lightbox-img">
    <div class="lightbox-nav">
        <span class="lightbox-prev" id="lightbox-prev"><i class="fas fa-chevron-left"></i></span>
        <span class="lightbox-next" id="lightbox-next"><i class="fas fa-chevron-right"></i></span>
    </div>
</div>

<script>
const images = document.querySelectorAll('.house-images img');
const lightbox = document.getElementById('lightbox');
const lightboxImg = document.getElementById('lightbox-img');
const closeBtn = document.getElementById('lightbox-close');
const prevBtn = document.getElementById('lightbox-prev');
const nextBtn = document.getElementById('lightbox-next');

let currentIndex = 0;

function showLightbox(index) {
    currentIndex = index;
    lightboxImg.src = images[currentIndex].src;
    lightbox.style.display = 'flex';
}

function hideLightbox() {
    lightbox.style.display = 'none';
}

function showPrev() {
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    lightboxImg.src = images[currentIndex].src;
}

function showNext() {
    currentIndex = (currentIndex + 1) % images.length;
    lightboxImg.src = images[currentIndex].src;
}

images.forEach((img, i) => img.addEventListener('click', () => showLightbox(i)));
closeBtn.addEventListener('click', hideLightbox);
prevBtn.addEventListener('click', showPrev);
nextBtn.addEventListener('click', showNext);

// Click outside image to close
lightbox.addEventListener('click', e => {
    if(e.target === lightbox) hideLightbox();
});
</script>

<?php include("../includes/footer.php"); ?>

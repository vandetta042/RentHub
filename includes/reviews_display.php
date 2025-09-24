<?php
$houseId = (int)$house['house_id'];

// Average rating + count
$avgQuery = $conn->prepare("
    SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews 
    FROM reviews 
    WHERE house_id = ?
");
$avgQuery->bind_param("i", $houseId);
$avgQuery->execute();
$avgResult = $avgQuery->get_result()->fetch_assoc();

$avgRating = $avgResult['avg_rating'] ? round($avgResult['avg_rating'], 1) : 0;
$totalReviews = (int)$avgResult['total_reviews'];

if ($totalReviews > 0): ?>
    <div style="margin:10px 0; padding:8px; background:#f9f9f9; border:1px solid #ddd;">
        <strong>Average Rating:</strong>
        <?php echo str_repeat("⭐", round($avgRating)); ?>
        (<?php echo $avgRating; ?>/5 from <?php echo $totalReviews; ?> reviews)
    </div>
<?php endif; ?>

<?php
// Fetch individual reviews
$result = $conn->query("
    SELECT r.review_id, r.user_id, r.comment, r.rating, r.created_at, u.full_name
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.house_id = $houseId
    ORDER BY r.created_at DESC
");

if ($result && $result->num_rows > 0):
    while ($review = $result->fetch_assoc()):
?>
    <div style="border:1px solid #ccc; padding:10px; margin:5px 0;">
        <p><strong><?php echo htmlspecialchars($review['full_name']); ?></strong></p>
        <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
        <p><em>Rating: <?php echo (int)$review['rating']; ?>/5</em></p>
        <p><small>Posted: <?php echo htmlspecialchars($review['created_at']); ?></small></p>

        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $review['user_id']): ?>
            <form action="../houses/flag_reviews.php" method="POST" style="display:inline;">
                <input type="hidden" name="review_id" value="<?php echo (int)$review['review_id']; ?>">
                <input type="hidden" name="reason" value="Inappropriate">
                <button type="submit" onclick="return confirm('Flag this review for admin review?');" style="color:crimson;">
                    🚩 Flag
                </button>
            </form>
        <?php endif; ?>
    </div>
<?php
    endwhile;
else:
    echo "<p>No reviews yet.</p>";
endif;
?>
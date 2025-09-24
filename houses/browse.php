<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['student', 'worker'])) {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

// --- Safe retrieval of filter inputs ---
$selectedLocation = $_GET['location'] ?? '';
$selectedType     = $_GET['type'] ?? '';
$minPrice         = $_GET['min_price'] ?? '';
$maxPrice         = $_GET['max_price'] ?? '';
$keyword          = $_GET['keyword'] ?? '';

// --- Pagination setup ---
$limit = 5; // houses per page (adjust if needed)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// --- Base query (for both count & data) ---
$baseQuery = "FROM houses h WHERE 1=1";

// --- Append filters safely ---
if ($selectedLocation !== '') {
    $loc = $conn->real_escape_string($selectedLocation);
    $baseQuery .= " AND h.location LIKE '%$loc%'";
}

if ($selectedType !== '') {
    $type = $conn->real_escape_string($selectedType);
    $baseQuery .= " AND h.house_type = '$type'";
}

if ($minPrice !== '') {
    $min = (float) $minPrice;
    $baseQuery .= " AND h.price >= $min";
}

if ($maxPrice !== '') {
    $max = (float) $maxPrice;
    $baseQuery .= " AND h.price <= $max";
}

if ($keyword !== '') {
    $kw = $conn->real_escape_string($keyword);
    $baseQuery .= " AND (h.title LIKE '%$kw%' OR h.description LIKE '%$kw%')";
}

// --- Count total rows for pagination ---
$countQuery = "SELECT COUNT(*) as total " . $baseQuery;
$countResult = $conn->query($countQuery);
$totalRows = ($countResult && $countResult->num_rows > 0) ? $countResult->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalRows / $limit);

// --- Final data query with pagination ---
$query = "SELECT h.house_id, h.title, h.price, h.location, h.house_type, h.description,
                 (SELECT hi.image_url FROM house_images hi WHERE hi.house_id = h.house_id LIMIT 1) AS image_url
          $baseQuery
          ORDER BY h.created_at DESC
          LIMIT $limit OFFSET $offset";

$result = $conn->query($query);
?>

<?php include("../includes/header.php"); ?>
<style>
    .browse-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .browse-header h2 {
        color: #2c3e50;
        margin: 0;
    }

    .browse-header a {
        color: #2c3e50;
        text-decoration: underline;
        font-size: 1rem;
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.06);
        padding: 18px 20px 10px 20px;
        margin-bottom: 22px;
        align-items: flex-end;
    }

    .filter-form input,
    .filter-form select {
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        font-size: 1rem;
        background: #f9fafb;
        min-width: 120px;
    }

    .filter-form button {
        background: #2c3e50;
        color: #fff;
        border: none;
        border-radius: 7px;
        padding: 10px 18px;
        font-size: 1rem;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.2s;
    }

    .filter-form button:hover {
        background: #34495e;
    }

    .house-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
        gap: 24px;
    }

    .house-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(44, 62, 80, 0.08);
        padding: 20px 18px 16px 18px;
        display: flex;
        flex-direction: column;
        min-height: 320px;
        position: relative;
        transition: box-shadow 0.2s, transform 0.2s;
    }

    .house-card:hover {
        box-shadow: 0 8px 32px rgba(44, 62, 80, 0.13);
        transform: translateY(-4px) scale(1.01);
    }

    .house-card h3 {
        margin: 0 0 8px 0;
        color: #2c3e50;
    }

    .house-card .price {
        color: #27ae60;
        font-size: 1.2rem;
        font-weight: bold;
    }

    .house-card .meta {
        color: #888;
        font-size: 0.98rem;
        margin-bottom: 7px;
    }

    .house-card .desc {
        color: #444;
        font-size: 1rem;
        margin-bottom: 10px;
    }

    .house-card img {
        width: 100%;
        max-width: 320px;
        max-height: 180px;
        object-fit: cover;
        border-radius: 8px;
        margin: 10px 0 12px 0;
        align-self: center;
    }

    .house-card .view-link {
        margin-top: auto;
        display: inline-block;
        background: #2c3e50;
        color: #fff;
        padding: 8px 18px;
        border-radius: 7px;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.2s;
    }

    .house-card .view-link:hover {
        background: #34495e;
    }

    .pagination {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin-top: 28px;
    }

    .pagination a {
        background: #fff;
        color: #2c3e50;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 6px 14px;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.2s, color 0.2s;
    }

    .pagination a[style*='font-weight:bold'] {
        background: #2c3e50;
        color: #fff;
        border: 1.5px solid #2c3e50;
    }

    .pagination a:hover {
        background: #f4f6f8;
        color: #2c3e50;
    }

    .no-results {
        text-align: center;
        color: #888;
        margin-top: 40px;
        font-size: 1.1rem;
    }
</style>
<div class="browse-header">
    <h2>Browse Available Houses</h2>
    <a href="../users/dashboard.php">← Back to Dashboard</a>
</div>
<form method="get" action="browse.php" class="filter-form">
    <input type="text" name="location" placeholder="Location" value="<?php echo htmlspecialchars($selectedLocation); ?>">
    <select name="type">
        <option value="">Any Type</option>
        <option value="self_contain" <?php if ($selectedType == 'self_contain') echo 'selected'; ?>>Self Contain</option>
        <option value="single_room" <?php if ($selectedType == 'single_room') echo 'selected'; ?>>Single Room</option>
        <option value="hostel" <?php if ($selectedType == 'hostel') echo 'selected'; ?>>Hostel</option>
        <option value="flat" <?php if ($selectedType == 'flat') echo 'selected'; ?>>Flat</option>
        <option value="shared_apartment" <?php if ($selectedType == 'shared_apartment') echo 'selected'; ?>>Shared Apartment</option>
    </select>
    <input type="number" name="min_price" placeholder="Min Price" value="<?php echo htmlspecialchars($minPrice); ?>">
    <input type="number" name="max_price" placeholder="Max Price" value="<?php echo htmlspecialchars($maxPrice); ?>">
    <input type="text" name="keyword" placeholder="Keyword" value="<?php echo htmlspecialchars($keyword); ?>">
    <button type="submit">Search</button>
</form>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="house-list">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="house-card">
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <div class="price">₦<?php echo number_format($row['price'], 2); ?></div>
                <div class="meta">Location: <?php echo htmlspecialchars($row['location']); ?> | Type: <?php echo htmlspecialchars(str_replace('_', ' ', $row['house_type'])); ?></div>
                <div class="desc"><?php echo nl2br(htmlspecialchars($row['description'])); ?></div>
                <?php if (!empty($row['image_url'])): ?>
                    <img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="house thumbnail">
                <?php else: ?>
                    <div class="meta"><em>No image available</em></div>
                <?php endif; ?>
                <a href="view.php?id=<?php echo $row['house_id']; ?>" class="view-link">View Details</a>
            </div>
        <?php endwhile; ?>
    </div>
    <div class="pagination">
        <?php
        // Preserve filters in pagination links
        $queryString = "";
        if ($selectedLocation !== '') $queryString .= "&location=" . urlencode($selectedLocation);
        if ($selectedType !== '')     $queryString .= "&type=" . urlencode($selectedType);
        if ($minPrice !== '')         $queryString .= "&min_price=" . urlencode($minPrice);
        if ($maxPrice !== '')         $queryString .= "&max_price=" . urlencode($maxPrice);
        if ($keyword !== '')          $queryString .= "&keyword=" . urlencode($keyword);

        if ($page > 1) {
            echo "<a href='?page=" . ($page - 1) . $queryString . "'>&laquo; Prev</a> ";
        }

        for ($i = 1; $i <= $totalPages; $i++) {
            $active = ($i == $page) ? "style='font-weight:bold;'" : "";
            echo "<a href='?page=$i$queryString' $active>$i</a> ";
        }

        if ($page < $totalPages) {
            echo "<a href='?page=" . ($page + 1) . $queryString . "'>Next &raquo;</a>";
        }
        ?>
    </div>
<?php else: ?>
    <div class="no-results">No houses found. Try adjusting your filters.</div>
<?php endif; ?>
<?php include("../includes/footer.php"); ?>
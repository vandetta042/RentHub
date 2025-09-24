<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['landlord', 'agent'])) {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");
$user_id = $_SESSION['user_id'];

$result = $conn->query("SELECT * FROM houses WHERE user_id = $user_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Listings</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 0;
        }


        .my-listings-wrapper {
            max-width: 1100px;
            margin: 36px auto 0 auto;
            padding: 0 18px 36px 18px;
        }

        .my-listings-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .my-listings-header h2 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin: 0;
        }

        .my-listings-header a {
            background: #27ae60;
            color: #fff;
            border-radius: 7px;
            padding: 8px 22px;
            text-decoration: none;
            font-size: 1.05rem;
            font-weight: 500;
            transition: background 0.18s;
        }

        .my-listings-header a:hover {
            background: #219150;
        }

        .my-listings-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(44, 62, 80, 0.10);
            padding: 28px 24px 18px 24px;
            margin-bottom: 28px;
            display: flex;
            flex-direction: column;
        }

        .my-listings-title {
            color: #2c3e50;
            font-size: 1.18rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .my-listings-meta {
            color: #888;
            font-size: 1.01rem;
            margin-bottom: 10px;
        }

        .my-listings-desc {
            color: #34495e;
            font-size: 1.04rem;
            margin-bottom: 12px;
        }

        .my-listings-images {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 12px;
        }

        .my-listings-images img {
            width: 120px;
            height: 90px;
            object-fit: cover;
            border-radius: 7px;
            box-shadow: 0 2px 8px rgba(44, 62, 80, 0.07);
        }

        .my-listings-actions {
            margin-top: 8px;
        }

        .my-listings-actions a {
            color: #2c3e50;
            text-decoration: underline;
            margin-right: 16px;
            font-weight: 500;
            font-size: 1.03rem;
            transition: color 0.18s;
        }

        .my-listings-actions a:hover {
            color: #e67e22;
        }

        .my-listings-links {
            display: flex;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .my-listings-links a {
            color: #2c3e50;
            text-decoration: underline;
            font-weight: 500;
            font-size: 1.04rem;
            transition: color 0.18s;
        }

        .my-listings-links a:hover {
            color: #e67e22;
        }
    </style>
</head>

<body>
    <div class="my-listings-wrapper">
        <div class="my-listings-header">
            <h2>My House Listings</h2>
            <a href="add.php">+ Add New House</a>
        </div>
        <div class="my-listings-links">
            <a href="../users/dashboard.php">Back to Dashboard</a>
        </div>
        <?php
        if ($result->num_rows > 0) {
            while ($house = $result->fetch_assoc()) {
                echo "<div class='my-listings-card'>";
                echo "<div class='my-listings-title'>" . htmlspecialchars($house['title']) . "</div>";
                echo "<div class='my-listings-meta'><b>₦" . htmlspecialchars($house['price']) . "</b> - " . htmlspecialchars($house['location']) . " (" . htmlspecialchars($house['house_type']) . ")</div>";
                echo "<div class='my-listings-desc'>" . nl2br(htmlspecialchars($house['description'])) . "</div>";
                // Fetch images
                $hid = $house['house_id'];
                $imgs = $conn->query("SELECT * FROM house_images WHERE house_id = $hid");
                if ($imgs->num_rows > 0) {
                    echo "<div class='my-listings-images'>";
                    while ($img = $imgs->fetch_assoc()) {
                        echo "<img src='../" . htmlspecialchars($img['image_url']) . "' alt='House Image'>";
                    }
                    echo "</div>";
                }
                echo "<div class='my-listings-actions'>
                    <a href='edit.php?id=" . $house['house_id'] . "'>Edit</a>
                    <a href='delete.php?id=" . $house['house_id'] . "' onclick=\"return confirm('Are you sure you want to delete this house?');\">Delete</a>
                </div>";
                echo "</div>";
            }
        } else {
            echo "<p>No houses posted yet.</p>";
        }
        ?>
    </div>
</body>

</html>
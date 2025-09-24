<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['landlord', 'agent'])) {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$house_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Fetch house (only if it belongs to this user)
$house = $conn->query("SELECT * FROM houses WHERE house_id = $house_id AND user_id = $user_id")->fetch_assoc();
if (!$house) {
    die("House not found or you don’t have permission.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $location = $_POST['location'];
    $house_type = $_POST['house_type'];

    $stmt = $conn->prepare("UPDATE houses SET title=?, description=?, price=?, location=?, house_type=? WHERE house_id=? AND user_id=?");
    $stmt->bind_param("ssdssii", $title, $description, $price, $location, $house_type, $house_id, $user_id);

    if ($stmt->execute()) {
        header("Location: my_listings.php");
        exit();
    } else {
        $error = "Error updating house: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit House</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .edit-house-wrapper {
            max-width: 540px;
            margin: 36px auto 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(44, 62, 80, 0.10);
            padding: 36px 32px 28px 32px;
        }

        .edit-house-title {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 18px;
            text-align: center;
            font-weight: 600;
        }

        .edit-house-form label {
            font-weight: 500;
            color: #34495e;
            margin-bottom: 6px;
            display: block;
        }

        .edit-house-form input[type="text"],
        .edit-house-form input[type="number"],
        .edit-house-form textarea,
        .edit-house-form select {
            width: 100%;
            padding: 12px 10px;
            margin-bottom: 16px;
            border: 1.5px solid #e0e0e0;
            border-radius: 7px;
            font-size: 1.08rem;
            background: #f8fafc;
            transition: border 0.18s;
        }

        .edit-house-form input[type="text"]:focus,
        .edit-house-form input[type="number"]:focus,
        .edit-house-form textarea:focus,
        .edit-house-form select:focus {
            border: 1.5px solid #2c3e50;
            outline: none;
        }

        .edit-house-form textarea {
            min-height: 90px;
            resize: vertical;
        }

        .edit-house-form button {
            width: 100%;
            background: #27ae60;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 13px 0;
            font-size: 1.13rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s;
            margin-top: 8px;
        }

        .edit-house-form button:hover {
            background: #219150;
        }

        .edit-house-links {
            display: flex;
            justify-content: space-between;
            margin-top: 18px;
        }

        .edit-house-links a {
            color: #2c3e50;
            text-decoration: underline;
            font-weight: 500;
            font-size: 1.04rem;
            transition: color 0.18s;
        }

        .edit-house-links a:hover {
            color: #e67e22;
        }

        .edit-house-feedback {
            text-align: center;
            font-size: 1.08rem;
            margin-bottom: 12px;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="edit-house-wrapper">
        <div class="edit-house-title">Edit House</div>
        <?php if (isset($error)): ?>
            <div class="edit-house-feedback" style="color:#e74c3c;"> <?php echo $error; ?> </div>
        <?php endif; ?>
        <form method="POST" class="edit-house-form">
            <label for="title">House Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($house['title']); ?>" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($house['description']); ?></textarea>

            <label for="price">Price (₦)</label>
            <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($house['price']); ?>" required>

            <label for="location">Location</label>
            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($house['location']); ?>" required>

            <label for="house_type">House Type</label>
            <select id="house_type" name="house_type" required>
                <option value="self_contain" <?php if ($house['house_type'] == "self_contain") echo "selected"; ?>>Self Contain</option>
                <option value="single_room" <?php if ($house['house_type'] == "single_room") echo "selected"; ?>>Single Room</option>
                <option value="hostel" <?php if ($house['house_type'] == "hostel") echo "selected"; ?>>Hostel</option>
                <option value="flat" <?php if ($house['house_type'] == "flat") echo "selected"; ?>>Flat</option>
                <option value="shared_apartment" <?php if ($house['house_type'] == "shared_apartment") echo "selected"; ?>>Shared Apartment</option>
            </select>

            <button type="submit">Update House</button>
        </form>
        <div class="edit-house-links">
            <a href="my_listings.php">← Back to My Listings</a>
            <a href="../users/dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
</body>

</html>
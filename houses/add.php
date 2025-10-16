<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['landlord', 'agent'])) {
    header("Location: ../public/login.php");
    exit();
}

include("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $location = $_POST['location'];
    $house_type = $_POST['house_type'];
    $user_id = $_SESSION['user_id'];

    // Insert into houses table
    $stmt = $conn->prepare("INSERT INTO houses (user_id, title, description, price, location, house_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdss", $user_id, $title, $description, $price, $location, $house_type);

    if ($stmt->execute()) {
        $house_id = $stmt->insert_id;

        // Handle multiple image uploads
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = time() . "_" . $_FILES['images']['name'][$key];
            $file_path = "../assets/images/" . $file_name;

            if (move_uploaded_file($tmp_name, $file_path)) {
                $img_url = "assets/images/" . $file_name; // relative path for frontend
                $img_stmt = $conn->prepare("INSERT INTO house_images (house_id, image_url) VALUES (?, ?)");
                $img_stmt->bind_param("is", $house_id, $img_url);
                $img_stmt->execute();
            }
        }

        $success = "House listing added successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

require_once "../includes/notification_helper.php";

// Only send notifications if house was successfully added
if (isset($success) && isset($title) && isset($house_id)) {
    $res = $conn->query("SELECT user_id FROM users WHERE user_type='student'");
    while ($row = $res->fetch_assoc()) {
        $content = "New house listed: $title";
        $link = "../houses/view.php?id=$house_id";
        addNotification($conn, $row['user_id'], $content, $link);
    }
}
?>

<?php include("../includes/header.php"); ?>
<style>
    .add-house-wrapper {
        max-width: 540px;
        margin: 36px auto 0 auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(44, 62, 80, 0.10);
        padding: 36px 32px 28px 32px;
    }

    .add-house-title {
        color: #2c3e50;
        font-size: 1.5rem;
        margin-bottom: 18px;
        text-align: center;
        font-weight: 600;
    }

    .add-house-form label {
        font-weight: 500;
        color: #34495e;
        margin-bottom: 6px;
        display: block;
    }

    .add-house-form input[type="text"],
    .add-house-form input[type="number"],
    .add-house-form textarea,
    .add-house-form select {
        width: 100%;
        padding: 12px 10px;
        margin-bottom: 16px;
        border: 1.5px solid #e0e0e0;
        border-radius: 7px;
        font-size: 1.08rem;
        background: #f8fafc;
        transition: border 0.18s;
    }

    .add-house-form input[type="text"]:focus,
    .add-house-form input[type="number"]:focus,
    .add-house-form textarea:focus,
    .add-house-form select:focus {
        border: 1.5px solid #2c3e50;
        outline: none;
    }

    .add-house-form textarea {
        min-height: 90px;
        resize: vertical;
    }

    .add-house-form input[type="file"] {
        margin-bottom: 18px;
    }

    .add-house-form button {
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

    .add-house-form button:hover {
        background: #219150;
    }

    .add-house-links {
        display: flex;
        justify-content: space-between;
        margin-top: 18px;
    }

    .add-house-links a {
        color: #2c3e50;
        text-decoration: underline;
        font-weight: bold;
        font-size: 1.04rem;
        transition: color 0.18s;
    }

    .add-house-links a:hover {
        color: #e67e22;
    }

    .add-house-feedback {
        text-align: center;
        font-size: 1.08rem;
        margin-bottom: 12px;
        font-weight: 500;
    }
</style>
<div class="add-house-links">
    <a href="my_listings.php">BACK</a>
</div>
<div class="add-house-wrapper">
    <div class="add-house-title">Add New House</div>
    <?php if (isset($success)): ?>
        <div class="add-house-feedback" style="color:#27ae60;"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="add-house-feedback" style="color:#e74c3c;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="add-house-form">
        <label for="title">House Title</label>
        <input type="text" id="title" name="title" placeholder="House Title" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" placeholder="Description" required></textarea>

        <label for="price">Price (₦)</label>
        <input type="number" id="price" name="price" step="0.01" placeholder="Price (₦)" required>

        <label for="location">Location</label>
        <input type="text" id="location" name="location" placeholder="Location" required>

        <label for="house_type">House Type</label>
        <select id="house_type" name="house_type" required>
            <option value="self_contain">Self Contain</option>
            <option value="single_room">Single Room</option>
            <option value="hostel">Hostel</option>
            <option value="flat">Flat</option>
            <option value="shared_apartment">Shared Apartment</option>
        </select>

        <label for="images">Upload Images (you can select multiple)</label>
        <input type="file" id="images" name="images[]" multiple accept="image/*">

        <button type="submit">Add House</button>
    </form>

</div>
<?php include("../includes/footer.php"); ?>
<?php
include("../config/db.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // secure hash
    $user_type = $_POST['user_type'];

    // Check if email already exists
    $check = $conn->prepare("SELECT * FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error = "Email already registered!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, user_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $full_name, $email, $phone, $password, $user_type);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! Please log in.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<?php include("../includes/header.php"); ?>
<style>
    body {
        background: #f4f6f8;
    }

    .auth-container {
        max-width: 420px;
        margin: 40px auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(44, 62, 80, 0.08);
        padding: 32px 28px 24px 28px;
        text-align: center;
    }

    .auth-container h2 {
        margin-bottom: 18px;
        color: #2c3e50;
    }

    .auth-container input[type="text"],
    .auth-container input[type="email"],
    .auth-container input[type="password"],
    .auth-container select {
        width: 90%;
        padding: 12px 10px;
        margin: 10px 0 18px 0;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 1rem;
        background: #f9fafb;
        transition: border 0.2s;
    }

    .auth-container input:focus,
    .auth-container select:focus {
        border: 1.5px solid #2c3e50;
        outline: none;
    }

    .auth-container button {
        width: 100%;
        background: #1c6ea9ff;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 12px;
        font-size: 1.1rem;
        font-weight: bold;
        cursor: pointer;
        margin-top: 8px;
        transition: background 0.2s;
    }

    .auth-container button:hover {
        background: rgba(128, 157, 185, 1)ff;
    }

    .auth-container button:active {
        transform: scale(0.98);
    }

    .auth-container .error {
        color: #e74c3c;
        background: #fdecea;
        border: 1px solid #f5c6cb;
        border-radius: 6px;
        padding: 8px 0;
        margin-bottom: 16px;
    }

    .auth-container label {
        display: block;
        margin-bottom: 6px;
        color: #2c3e50;
        text-align: left;
    }

    .auth-container p {
        margin-top: 18px;
    }

    .auth-container a {
        color: #2c3e50;
        text-decoration: underline;
    }
</style>
<div class="auth-container">
    <h2>Register</h2>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="password" name="password" placeholder="Password" required>
        <label>User Type:</label>
        <select name="user_type" required>
            <option value="student">Student</option>
            <option value="worker">Worker</option>
            <option value="landlord">Landlord</option>
            <option value="agent">Agent</option>
        </select>
        <button type="submit">Register</button>
    </form>
    <p>Already registered? <a href="login.php">Login here</a></p>
</div>
<?php include("../includes/footer.php"); ?>
<?php
include("../config/db.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        //handle account status
        switch ($user['status']) {
            case 'active':
                //normal login flow
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['full_name'] = $user['full_name'];



                // Redirect based on role
                if ($user['user_type'] === 'admin') {
                    header("Location: ../admin/dashboard.php");
                } else {
                    header("Location: ../users/dashboard.php");
                }
                exit();

            case 'suspended':
                $error = "Account suspended";
                break;

            case 'deleted':
                $error = "Account deleted";
                break;

            default:
                $error = "deactivated" . htmlspecialchars($user['status']);
                break;
        }
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<?php include("../includes/header.php"); ?>
<style>
    body {
        background: #f4f6f8;
    }

    .auth-container {
        max-width: 400px;
        margin: 40px auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(103, 87, 53, 0.08);
        padding: 32px 28px 24px 28px;
        text-align: center;
    }

    .auth-container h2 {
        margin-bottom: 18px;
        color: #685938ff;
    }

    .auth-container input[type="email"],
    .auth-container input[type="password"] {
        width: 90%;
        padding: 12px 10px;
        margin: 10px 0 18px 0;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 1rem;
        background: #f9fafb;
        transition: border 0.2s;
    }

    .auth-container input:focus {
        border: 1.5px solid #50462cff;
        outline: none;
    }

    .auth-container button {
        width: 90%;
        background: #1560c1ff;
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
        background: #1c6ea9ff;
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

    .auth-container p {
        margin-top: 18px;
    }

    .auth-container a {
        color: #50452cff;
        text-decoration: underline;
    }
</style>
<div class="auth-container">
    <h2>Login</h2>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>
<?php include("../includes/footer.php"); ?>
<?php
require_once "../includes/config.php";
require_once "../includes/base.php";

// Redirect if already logged in as user (not admin)
if (isset($_SESSION["user_id"]) && $_SESSION["role"] === "user") {
    $_SESSION["error_message"] = "Access denied. You're logged in as a regular user.";
    header("location: welcome.php");
    exit;
}

// Redirect if already logged in as admin
if (isset($_SESSION["user_id"]) && $_SESSION["role"] === "admin") {
    header("location: dashboard.php");
    exit;
}

$login_id = $password = "";
$_err = [];
$success_message = "";

// Check if success message exists
if (isset($_SESSION["success_message"])) {
    $success_message = $_SESSION["success_message"];
    unset($_SESSION["success_message"]);
}

if (is_post()) {
    $login_id = req('login_id');
    $password = req('password');

    // Validate login input
    if (!$login_id) $_err['login_id'] = 'Username or Email is required.';
    if (!$password) $_err['password'] = 'Password is required.';

    if (!$_err) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE EMAIL = :login OR USERNAME = :login");
        $stmt->execute(['login' => $login_id]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin["PASSWORD"])) {
            // Set session
            $_SESSION["user_id"] = $admin["AID"];
            $_SESSION["username"] = $admin["USERNAME"];
            $_SESSION["email"] = $admin["EMAIL"];
            $_SESSION["admin_role"] = $admin["ROLE"];
            $_SESSION["user_id"] = $admin["AID"];
            $_SESSION["role"] = "admin"; 
            $_SESSION["admin_role"] = $admin["ROLE"]; 


            // Update last login
            $updateStmt = $pdo->prepare("UPDATE admins SET LAST_LOGGEDIN = NOW() WHERE AID = :aid");
            $updateStmt->execute(['aid' => $admin["AID"]]);

            header("location: dashboard.php");
            exit;
        } else {
            $_err["login_id"] = "Invalid username/email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login | ByteMeTech.com</title>
    <?php include '../includes/headers.php'; ?>
    <script>
        function togglePasswordVisibility() {
            const input = document.getElementById("password");
            const toggle = document.querySelector(".toggle-password");
            if (input.type === "password") {
                input.type = "text";
                toggle.textContent = "🙈";
            } else {
                input.type = "password";
                toggle.textContent = "👁️";
            }
        }
    </script>
</head>

<body class="bg-light">
    <?php include '../includes/navbar.php'; ?>

    <div class="container bg-dark">
        <h1 class="white-text">Admin Login</h1>
    </div>

    <div class="container">
        <form method="post" class="login-form">
            <?php if (!empty($success_message)): ?><div class="alert-success"><?php echo $success_message; ?></div><?php endif; ?>
            <label for="login_id">Username or Email</label>
            <?= err('login_id') ?>
            <?= html_text('login_id', 'id="login_id" maxlength="100"') ?>

            <label for="password">Password</label>
            <?= err('password') ?>
            <div class="input-wrapper">
                <?= html_password('password', 'id="password" maxlength="100"') ?>
                <span class="toggle-password" onclick="togglePasswordVisibility()">👁️</span>
            </div>

            <button type="submit" class="btn btn-blue">Login</button>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>

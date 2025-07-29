<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_GET['id'])) {
    temp("info", "Invalid reset link.");
    header("location: login.php");
    exit;
}

$token_id = $_GET['id'];

// Validate token
$stmt = $pdo->prepare("SELECT * FROM token WHERE TOKEN_ID = :id AND EXPIRE > NOW()");
$stmt->execute(["id" => $token_id]);
$token = $stmt->fetch();

if (!$token) {
    temp("info", "Expired or invalid reset token.");
    header("location: login.php");
    exit;
}

$uid = $token["USER_ID"];

// Handle form submission
if (is_post()) {
    $new_password = post("new_password");
    $confirm_password = post("confirm_password");

    // Validation
    if ($new_password === "" || strlen($new_password) < 6) {
        $_err["new_password"] = "Password must be at least 6 characters.";
    }

    if ($confirm_password === "" || $new_password !== $confirm_password) {
        $_err["confirm_password"] = "Passwords do not match.";
    }

    if (!$_err) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password
        $stmt = $pdo->prepare("UPDATE users SET PASSWORD = :password WHERE UID = :uid");
        $stmt->execute([
            "password" => $hash,
            "uid" => $uid
        ]);

        // Delete token
        $stmt = $pdo->prepare("DELETE FROM token WHERE TOKEN_ID = :id");
        $stmt->execute(["id" => $token_id]);

        // Redirect with success
        $_SESSION["success_message"] = "Reset Password Successful, Please Login to continue";
        header("location: login.php");
        exit;
    }
}

$_title = "Reset Forgotten Password";
include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>ByteMeTech.com | Reset Forgotten Password</title>
        <script>
            function togglePasswordVisibility(inputId, toggleIcon) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
            input.type = "text";
            toggleIcon.textContent = "🙈";
            } else {
            input.type = "password";
            toggleIcon.textContent = "👁️";
            }
            }
        </script>
    </head>
    <body class="bg-light">
        <?php include '../includes/navbar.php'; ?>

        <div class="container bg-dark">
            <h1 class="white-text">Reset Forgotten Password</h1>
        </div>

        <div class="container">
            <form method="post" class="login-form">
                <label for="new_password">New Password</label>
                <?= err('new_password') ?>
                <div class="input-wrapper">
                    <?= html_password('new_password', 'placeholder="Enter new password" id="new_password"') ?>
                    <span class="toggle-password" onclick="togglePasswordVisibility('new_password', this)">👁️</span>
                </div>

                <label for="confirm_password">Confirm Password</label>
                <?= err('confirm_password') ?>
                <div class="input-wrapper">
                    <?= html_password('confirm_password', 'placeholder="Re-enter password" id="confirm_password"') ?>
                    <span class="toggle-password" onclick="togglePasswordVisibility('confirm_password', this)">👁️</span>
                </div>

                <br>
                <button type="submit" class="btn btn-green">Reset Password</button>
                <a href="login.php" class="btn btn-blue">Back to Login</a>
            </form>
        </div>

        <?php include '../includes/footer.php'; ?>
    </body>
</html>
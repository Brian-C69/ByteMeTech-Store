<?php
require_once "../includes/config.php";
require_once "../includes/base.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    $_SESSION['error_message'] = "You must log in to continue.";
    header("location: login.php");
    exit;
}

$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate new password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter the new password.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password must have at least 6 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if ($new_password !== $confirm_password) {
            $confirm_password_err = "Passwords do not match.";
        }
    }

    // If valid, update password in DB
    if (empty($new_password_err) && empty($confirm_password_err)) {
        $sql = "UPDATE users SET PASSWORD = :password WHERE UID = :uid";
        $stmt = $pdo->prepare($sql);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(":uid", $_SESSION["user_id"], PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Destroy current session and redirect with success message
            session_destroy();
            session_start(); // restart session to pass message
            $_SESSION["success_message"] = "Reset Password Successful, Login to continue.";
            header("location: login.php");
            exit;
        } else {
            $error_message = "Oops! Something went wrong. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>ByteMeTech.com | Reset Password</title>
    <?php include '../includes/headers.php'; ?>
    <script>
        function toggleVisibility(id, toggleId) {
            const input = document.getElementById(id);
            const toggle = document.getElementById(toggleId);
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
        <h1 class="white-text">Reset Password</h1>
    </div>

    <div class="container">
        <form method="post" class="login-form">

            <?php if (!empty($error_message)) echo '<div class="alert">' . $error_message . '</div>'; ?>

            <label for="new_password">New Password</label>
            <?php if (!empty($new_password_err)) echo '<div class="alert">' . $new_password_err . '</div>'; ?>
            <div class="input-wrapper">
                <input type="password" id="new_password" name="new_password" class="input-field">
                <span class="toggle-password" id="toggleNew" onclick="toggleVisibility('new_password', 'toggleNew')">👁️</span>
            </div>

            <label for="confirm_password">Confirm Password</label>
            <?php if (!empty($confirm_password_err)) echo '<div class="alert">' . $confirm_password_err . '</div>'; ?>
            <div class="input-wrapper">
                <input type="password" id="confirm_password" name="confirm_password" class="input-field">
                <span class="toggle-password" id="toggleConfirm" onclick="toggleVisibility('confirm_password', 'toggleConfirm')">👁️</span>
            </div>

            <button type="submit" class="btn btn-green">Reset Password</button>
            <a href="profile.php" class="btn btn-blue">Cancel</a>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>

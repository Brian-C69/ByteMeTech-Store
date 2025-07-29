<?php
require_once "../includes/config.php";
require_once "../includes/base.php";

// If the user is already logged in, redirect them
if (isset($_SESSION["user_id"])) {
    if ($_SESSION["role"] === "admin") {
        header("location: admin-dashboard.php");
        exit();
    } elseif ($_SESSION["role"] === "user") {
        header("location: welcome.php");
        exit();
    }
}

// Define variables and initialize with empty values
$username = $email = $password = $confirm_password = "";
$username_err = $email_err = $password_err = $confirm_password_err = "";
$error_message = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Trim and sanitize input
    $username = trim(filter_var($_POST["username"], FILTER_SANITIZE_STRING));
    $email = trim(filter_var($_POST["email"], FILTER_SANITIZE_EMAIL));
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Validate username
    if (empty($username)) {
        $username_err = "Please enter a username.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $username_err = "Username can only contain letters, numbers, and underscores.";
    } else {
        // Check if username already exists
        $sql = "SELECT UID FROM users WHERE USERNAME = :username";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $username_err = "This username is already taken.";
            }
        }
    }

    // Validate email
    if (empty($email)) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email.";
    } else {
        // Check if email already exists
        $sql = "SELECT UID FROM users WHERE EMAIL = :email";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $email_err = "This email is already registered.";
            }
        }
    }

    // Validate password
    if (empty($password)) {
        $password_err = "Please enter a password.";
    } elseif (strlen($password) < 6) {
        $password_err = "Password must have at least 6 characters.";
    }

    // Validate confirm password
    if (empty($confirm_password)) {
        $confirm_password_err = "Please confirm password.";
    } elseif ($password !== $confirm_password) {
        $confirm_password_err = "Passwords do not match.";
    }

    // If there are errors, set error message
    if (!empty($username_err) || !empty($email_err) || !empty($password_err) || !empty($confirm_password_err)) {
        $error_message = "Please fix the errors below.";
    }

    // Check input errors before inserting into database
    if (empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {

        // Insert user into database
        $sql = "INSERT INTO users (USERNAME, EMAIL, PASSWORD) VALUES (:username, :email, :password)";
        if ($stmt = $pdo->prepare($sql)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);

            if ($stmt->execute()) {
                // Store success message in session
                $_SESSION["success_message"] = "Signup successful! Please login to continue.";

                // Redirect to login page
                header("location: login.php");
                exit();
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <title>ByteMeTech.com | Register</title>
        <?php include '../includes/headers.php'; ?>
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
            <h1 class="white-text">Register</h1>
        </div>

        <div class="container">
            <form action="register.php" method="post" class="login-form">
                <?php if (!empty($error_message)): ?>
                    <div class="alert"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <label for="username">Username</label>
                <?php if (!empty($username_err)) echo '<div class="alert">' . $username_err . '</div>'; ?>
                <input type="text" id="username" name="username" class="input-field" value="<?php echo htmlspecialchars($username); ?>">

                <label for="email">Email</label>
                <?php if (!empty($email_err)) echo '<div class="alert">' . $email_err . '</div>'; ?>
                <input type="text" id="email" name="email" class="input-field" value="<?php echo htmlspecialchars($email); ?>">

                <label for="password">Password</label>
                <?php if (!empty($password_err)) echo '<div class="alert">' . $password_err . '</div>'; ?>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="input-field">
                    <span class="toggle-password" onclick="togglePasswordVisibility('password', this)">👁️</span>
                </div>

                <label for="confirm_password">Confirm Password</label>
                <?php if (!empty($confirm_password_err)) echo '<div class="alert">' . $confirm_password_err . '</div>'; ?>
                <div class="input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" class="input-field">
                    <span class="toggle-password" onclick="togglePasswordVisibility('confirm_password', this)">👁️</span>
                </div>
                <div>
                    <button type="submit" class="btn btn-blue">Sign Up</button>
                </div>
                <div class="signup-container">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </form>
        </div>

    </body>
</html>

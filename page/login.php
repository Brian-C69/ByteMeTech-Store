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

// Initialize variables
$username_email = $password = "";
$username_email_err = $password_err = "";
$error_message = "";
$success_message = "";

// Check if success message exists
if (isset($_SESSION["success_message"])) {
    $success_message = $_SESSION["success_message"];
    unset($_SESSION["success_message"]);
}

// Track failed attempts using session
if (!isset($_SESSION["failed_login_attempts"])) {
    $_SESSION["failed_login_attempts"] = 0;
}

// Check if user has a valid "Remember Me" cookie
if (isset($_COOKIE["remember_me"])) {
    $token = $_COOKIE["remember_me"];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE REMEMBER_ME_TOKEN = :token");
    $stmt->bindParam(":token", $token, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user) {
        $updateStmt = $pdo->prepare("UPDATE users SET LAST_LOGGEDIN = NOW() WHERE UID = :uid");
        $updateStmt->bindParam(":uid", $user["UID"], PDO::PARAM_INT);
        $updateStmt->execute();

        $_SESSION["user_id"] = $user["UID"];
        $_SESSION["username"] = $user["USERNAME"];
        $_SESSION["email"] = $user["EMAIL"];
        $_SESSION["role"] = "user";
        $_SESSION["last_loggedin"] = $user["LAST_LOGGEDIN"];
        $_SESSION["verified"] = $user["VERIFIED"];

        header("location: welcome.php");
        exit();
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if locked
    if ($_SESSION["failed_login_attempts"] >= 3) {
        $error_message = "Your account has been temporarily locked due to multiple failed login attempts. Please <a href='forgot-password.php'>reset your password</a> to continue.";
    } else {
        $username_email = trim($_POST["username_email"]);
        $password = trim($_POST["password"]);
        $remember_me = isset($_POST["remember_me"]);

        if (empty($username_email)) {
            $username_email_err = "Please enter your username or email.";
        }

        if (empty($password)) {
            $password_err = "Please enter your password.";
        }

        if (empty($username_email_err) && empty($password_err)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE USERNAME = :username_email OR EMAIL = :username_email");
            $stmt->bindParam(":username_email", $username_email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user["PASSWORD"])) {
                $updateStmt = $pdo->prepare("UPDATE users SET LAST_LOGGEDIN = NOW() WHERE UID = :uid");
                $updateStmt->bindParam(":uid", $user["UID"], PDO::PARAM_INT);
                $updateStmt->execute();

                $_SESSION["user_id"] = $user["UID"];
                $_SESSION["username"] = $user["USERNAME"];
                $_SESSION["email"] = $user["EMAIL"];
                $_SESSION["role"] = "user";
                $_SESSION["last_loggedin"] = date("Y-m-d H:i:s");
                $_SESSION["verified"] = $user["VERIFIED"];

                $_SESSION["failed_login_attempts"] = 0;

                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    setcookie("remember_me", $token, time() + (30 * 24 * 60 * 60), "/", "", true, true);
                    $storeToken = $pdo->prepare("UPDATE users SET REMEMBER_ME_TOKEN = :token WHERE UID = :uid");
                    $storeToken->bindParam(":token", $token, PDO::PARAM_STR);
                    $storeToken->bindParam(":uid", $user["UID"], PDO::PARAM_INT);
                    $storeToken->execute();
                }

                if (!empty($_SESSION["cart"])) {
                    foreach ($_SESSION["cart"] as $pid => $qty) {
                        $check = $pdo->prepare("SELECT QUANTITY FROM cart WHERE UID = :uid AND PID = :pid");
                        $check->execute(["uid" => $user["UID"], "pid" => $pid]);
                        if ($check->fetchColumn()) {
                            $update = $pdo->prepare("UPDATE cart SET QUANTITY = QUANTITY + :qty WHERE UID = :uid AND PID = :pid");
                        } else {
                            $update = $pdo->prepare("INSERT INTO cart (UID, PID, QUANTITY) VALUES (:uid, :pid, :qty)");
                        }
                        $update->execute(["uid" => $user["UID"], "pid" => $pid, "qty" => $qty]);
                    }
                    unset($_SESSION["cart"]);
                }

                header("location: welcome.php");
                exit();
            } else {
                $_SESSION["failed_login_attempts"] += 1;
                $remaining = 3 - $_SESSION["failed_login_attempts"];

                if ($_SESSION["failed_login_attempts"] >= 3) {
                    $error_message = "Your account has been temporarily locked. Please <a href='forgot-password.php'>reset your password</a>.";
                } else {
                    $error_message = "Incorrect username or password. You have $remaining attempt(s) left.";
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login | ByteMeTech.com</title>
    <?php include '../includes/headers.php'; ?>
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById("password");
            const toggle = document.querySelector(".toggle-password");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggle.textContent = "🙈";
            } else {
                passwordInput.type = "password";
                toggle.textContent = "👁️";
            }
        }
    </script>
</head>
<body class="bg-light">
<?php include '../includes/navbar.php'; ?>
<div class="container bg-dark">
    <h1>Login</h1>
</div>
<div class="container">
    <form action="login.php" method="post" class="login-form">
        <?php if (!empty($error_message)): ?><div class="alert"><?php echo $error_message; ?></div><?php endif; ?>
        <?php if (!empty($success_message)): ?><div class="alert-success"><?php echo $success_message; ?></div><?php endif; ?>
        <?php if ($msg = temp("info")): ?>
        <div class="alert-failed"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

        <label for="username_email">Username or Email</label>
        <?php if (!empty($username_email_err)) echo '<div class="alert">' . $username_email_err . '</div>'; ?>
        <input type="text" id="username_email" name="username_email" class="input-field" value="<?php echo htmlspecialchars($username_email); ?>">

        <label for="password">Password</label>
        <?php if (!empty($password_err)) echo '<div class="alert">' . $password_err . '</div>'; ?>
        <div class="input-wrapper">
            <input type="password" id="password" name="password" class="input-field">
            <span class="toggle-password" onclick="togglePasswordVisibility()">👁️</span>
        </div>

        <div class="login-options">
            <div class="checkbox-group">
                <input type="checkbox" id="remember_me" name="remember_me">
                <label for="remember_me">Keep me logged in</label>
            </div>
            <div class="forgot-password">
                <a href="forgot-password.php">Forgot password?</a>
            </div>
        </div>

        <button type="submit" class="btn btn-blue">Login</button>

        <div class="signup-container">
            <p>Don't have an account? <a href="register.php">Sign up</a></p>
        </div>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>

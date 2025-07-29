<?php
require_once "../includes/base.php";
require_once "../includes/config.php";
global $pdo;

// If user is already logged in, redirect them
if (isset($_SESSION["user_id"])) {
    header("location: welcome.php");
    exit;
}

// Reset failed login attempt count
unset($_SESSION["login_failures"]);
unset($_SESSION["failed_login_attempts"]);

if (is_post()) {
    $email = req("email");

    // Validate email
    if ($email == '') {
        $_err["email"] = "Please enter your email.";
    } elseif (!is_email($email)) {
        $_err["email"] = "Invalid email format.";
    } elseif (!is_exists($email, 'users', 'EMAIL')) {
        $_err["email"] = "Email not found.";
    }

    // If no errors
    if (!$_err) {
        // Fetch user info
        $stmt = $pdo->prepare("SELECT * FROM users WHERE EMAIL = :email");
        $stmt->execute(["email" => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate unique token
            $token_id = sha1(uniqid() . rand());

            // Delete any existing token for the user
            $pdo->prepare("DELETE FROM TOKEN WHERE USER_ID = :uid")->execute(["uid" => $user["UID"]]);

            // Insert new token with 10-minute expiry
            $stmt = $pdo->prepare("INSERT INTO TOKEN (TOKEN_ID, EXPIRE, USER_ID) VALUES (:id, ADDTIME(NOW(), '00:10:00'), :uid)");
            $stmt->execute([
                "id" => $token_id,
                "uid" => $user["UID"]
            ]);

            // Compose reset link
            $url = "http://" . $_SERVER["HTTP_HOST"] . "/ByteMeTech/page/reset-forgotten-password.php?id=$token_id";

            // Send email
            $mail = get_mail();
            $mail->addAddress($user["EMAIL"], $user["USERNAME"]);
            $mail->isHTML(true);
            $mail->Subject = "Password Reset Request";
            $mail->Body = "
                <h2>Password Reset</h2>
                <p>Hi <b>{$user['USERNAME']}</b>,</p>
                <p>Click the link below to reset your password:</p>
                <p><a href='$url'>Reset Password</a></p>
                <p>This link will expire in 10 minutes.</p>
                <br><p>ByteMeTech.com</p>
            ";
            $mail->send();

            temp("info", "A password reset link has been sent to your email.");
            redirect();
        }
    }
}

$_title = "Forgot Password";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password</title>
    <?php include '../includes/headers.php'; ?>
</head>
<body class="bg-light">
<?php include '../includes/navbar.php'; ?>

<div class="container bg-dark">
    <h1 class="white-text">Forgot Password</h1>
</div>

<div class="container">
    <?php if ($msg = temp('info')): ?>
        <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="post" class="login-form">
        <label for="email">Enter your registered email</label>
        <?= err('email') ?>
        <?= html_text('email', 'maxlength="100"') ?>

        <br>
        <button type="submit" class="btn btn-blue">Send Reset Link</button>
        <a href="login.php" class="btn btn-yellow">Back to Login</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>

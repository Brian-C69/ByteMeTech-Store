<?php
require_once '../includes/base.php';
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must log in to continue.";
    header("location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// Handle send verification email request
if (is_post() && isset($_POST['send_verification'])) {
    // Generate token
    $token = sha1(uniqid() . rand());

    // Delete old tokens
    $stmt = $pdo->prepare("DELETE FROM token WHERE USER_ID = :uid");
    $stmt->execute(['uid' => $uid]);

    // Insert new token (expire in 10 minutes)
    $stmt = $pdo->prepare("INSERT INTO token (TOKEN_ID, EXPIRE, USER_ID) VALUES (:token, ADDTIME(NOW(), '00:10:00'), :uid)");
    $stmt->execute(['token' => $token, 'uid' => $uid]);

    // Get user data
    $stmt = $pdo->prepare("SELECT EMAIL, FIRSTNAME FROM users WHERE UID = :uid");
    $stmt->execute(['uid' => $uid]);
    $user = $stmt->fetch();

    // Create verification URL
    $url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/verify-email.php?token=$token";

    // Send email
    $m = get_mail();
    $m->addAddress($user['EMAIL'], $user['FIRSTNAME']);
    $m->isHTML(true);
    $m->Subject = 'Verify Your Email';
    $m->Body = "<p>Hi {$user['FIRSTNAME']},</p>
                <p>Please click the link below to verify your email:</p>
                <p><a href='$url'>$url</a></p>
                <p>This link will expire in 10 minutes.</p>
                <p>Best regards,<br>ByteMeTech.com</p>";

    $m->send();
    temp('info', 'Verification email sent.');
    redirect();
}

// Handle token verification
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check token in DB
    $stmt = $pdo->prepare("SELECT * FROM token WHERE TOKEN_ID = :token AND EXPIRE > NOW()");
    $stmt->execute(['token' => $token]);
    $row = $stmt->fetch();

    if ($row) {
        // Mark user as verified
        $stmt = $pdo->prepare("UPDATE users SET VERIFIED = 1 WHERE UID = :uid");
        $stmt->execute(['uid' => $row['USER_ID']]);

        // Remove token
        $stmt = $pdo->prepare("DELETE FROM token WHERE USER_ID = :uid");
        $stmt->execute(['uid' => $row['USER_ID']]);

        temp('info', 'Email verification successful!');
        header("location: profile.php");
        exit;
    } else {
        temp('info', 'Invalid or expired token.');
        header("location: profile.php");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Verify Email</title>
    <?php include '../includes/headers.php'; ?>
</head>
<body class="bg-light">
    <?php include '../includes/navbar.php'; ?>

    <div class="container bg-dark">
        <h1 class="white-text">Email Verification</h1>
    </div>

    <div class="container">
        <?php if ($msg = temp('info')): ?>
            <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <p>Click the button below to receive a verification link to your email.</p>
            <button type="submit" name="send_verification" class="btn btn-blue">Send Verification Email</button>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>

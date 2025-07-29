<?php
require_once "../includes/config.php";
require_once "../includes/base.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    $_SESSION['error_message'] = "You must log in to continue.";
    header("location: login.php");
    exit;
}

// Fetch latest user info from DB
$stmt = $pdo->prepare("SELECT USERNAME, LAST_LOGGEDIN FROM users WHERE UID = :uid");
$stmt->bindParam(":uid", $_SESSION["user_id"], PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error_message'] = "User not found.";
    header("location: login.php");
    exit;
}

$username = $user["USERNAME"];
$lastLoggedIn = $user["LAST_LOGGEDIN"];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>ByteMeTech.com | Welcome</title>
    <?php include '../includes/headers.php'; ?>
</head>

<body class="bg-light">
    <?php include '../includes/navbar.php'; ?>

    <div class="container bg-dark">
        <h1>Welcome! <b><?php echo htmlspecialchars($username); ?></b></h1>
        <p class="white-text">Last login: <?php echo htmlspecialchars($lastLoggedIn); ?></p>
        <p id="session-timer" class="white-text"></p>
    </div>
</body>
</html>

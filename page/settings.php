<?php
require_once "../includes/base.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    $_SESSION['error_message'] = "You must log in to continue.";
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <title>ByteMeTech.com | Settings</title>
        <?php include '../includes/headers.php';?>
    </head>

    <body class="bg-light">
    <?php include '../includes/navbar.php';?>

        <div class="container bg-dark">
            <h1 class="white-text">Settings</h1>
        </div>
        
    <?php include '../includes/footer.php'; ?>
    </body>

</html>
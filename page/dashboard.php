<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Ensure admin is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

// Fetch admin data
$aid = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT FIRST_NAME, LAST_NAME, ROLE FROM admins WHERE AID = :aid");
$stmt->execute(["aid" => $aid]);
$admin = $stmt->fetch();

$fullName = htmlspecialchars($admin["FIRST_NAME"] . " " . $admin["LAST_NAME"]);
$role = htmlspecialchars($admin["ROLE"]);
$adminRole = $_SESSION["admin_role"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard | ByteMeTech.com</title>
    <?php include '../includes/headers.php'; ?>
</head>
<body class="bg-light">
    <?php include "../includes/sidebar.php"; ?>
    <div class="main-content">
        <h1>Welcome to the Admin Dashboard</h1>
        <p>Hello <strong><?= $fullName ?></strong> 👋</p>
        <p>Your role is: <strong><?= $adminRole ?></strong></p>
        <p>Manage your site with ease using the navigation on the left.</p>
    </div>
</body>
</html>

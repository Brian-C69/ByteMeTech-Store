<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Ensure admin is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("location: ../page/login.php");
    exit;
}

// Check if UID is provided
if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    temp("info", "Invalid user ID.");
    header("location: customers.php");
    exit;
}

$uid = $_GET["id"];

// Prevent admin from deleting themselves (optional safety)
if ($_SESSION["user_id"] == $uid) {
    temp("info", "You cannot delete your own account.");
    header("location: customers.php");
    exit;
}

// Fetch user to confirm existence
$stmt = $pdo->prepare("SELECT * FROM users WHERE UID = :uid");
$stmt->execute(["uid" => $uid]);
$user = $stmt->fetch();

if (!$user) {
    temp("info", "User not found.");
    header("location: customers.php");
    exit;
}

// Delete user's profile picture if exists
$default_picture = "../images/default-profile.png";
$profile_picture = $user["PROFILE_PICTURE"];

if ($profile_picture && $profile_picture !== $default_picture && file_exists($profile_picture)) {
    unlink($profile_picture);
}

// Delete user
$stmt = $pdo->prepare("DELETE FROM users WHERE UID = :uid");
$stmt->execute(["uid" => $uid]);

temp("info", "Customer deleted successfully.");
header("location: customers.php");
exit;

<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["addid"])) {
    $addid = (int) $_POST["addid"];

    // Ensure user owns the address
    $stmt = $pdo->prepare("DELETE FROM Address WHERE ADDID = :addid AND UID = :uid");
    $stmt->execute(["addid" => $addid, "uid" => $uid]);

    $_SESSION["success_message"] = "Address deleted successfully.";
}

header("Location: address.php");
exit;

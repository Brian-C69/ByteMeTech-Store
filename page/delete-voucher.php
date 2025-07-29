<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Only allow admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("location: ../page/login.php");
    exit;
}

// Get and validate ID
$vid = (int) ($_GET["id"] ?? 0);
if ($vid <= 0) {
    $_SESSION["error_message"] = "Invalid voucher ID.";
    header("location: voucher.php");
    exit;
}

// Delete voucher
$stmt = $pdo->prepare("DELETE FROM vouchers WHERE VID = :vid");
$stmt->execute(["vid" => $vid]);

$_SESSION["success_message"] = "Voucher deleted successfully.";
header("location: voucher.php");
exit;

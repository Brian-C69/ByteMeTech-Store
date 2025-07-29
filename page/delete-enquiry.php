<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

$cid = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($cid <= 0) {
    $_SESSION["error_message"] = "Invalid enquiry ID.";
    header("Location: customer-support.php");
    exit;
}

// Check if the message exists before deleting
$stmt = $pdo->prepare("SELECT * FROM contact_form WHERE CONTACT_ID = :cid");
$stmt->execute(["cid" => $cid]);
$enquiry = $stmt->fetch();

if (!$enquiry) {
    $_SESSION["error_message"] = "Enquiry not found.";
    header("Location: customer-support.php");
    exit;
}

// Delete the enquiry
$delete = $pdo->prepare("DELETE FROM contact_form WHERE CONTACT_ID = :cid");
$delete->execute(["cid" => $cid]);

$_SESSION["success_message"] = "Enquiry deleted successfully.";
header("Location: customer-support.php");
exit;
?>

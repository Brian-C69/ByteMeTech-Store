<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

$pid = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($pid <= 0) {
    $_SESSION["error_message"] = "Invalid product ID.";
    header("Location: product_manage.php");
    exit;
}

// Optional: Check if product exists
$stmt = $pdo->prepare("SELECT * FROM products WHERE PID = :pid");
$stmt->execute(["pid" => $pid]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION["error_message"] = "Product not found.";
    header("Location: product_manage.php");
    exit;
}

// Proceed with deletion
$stmt = $pdo->prepare("DELETE FROM products WHERE PID = :pid");
$stmt->execute(["pid" => $pid]);

$_SESSION["success_message"] = "Product deleted successfully.";
header("Location: product_manage.php");
exit;

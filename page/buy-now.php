<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["user_id"];
$pid = isset($_POST["pid"]) ? (int)$_POST["pid"] : 0;
$qty = isset($_POST["qty"]) ? (int)$_POST["qty"] : 1;

if ($pid <= 0 || $qty <= 0) {
    $_SESSION["error_message"] = "Invalid product or quantity.";
    header("Location: product.php");
    exit;
}

// Store product ID and quantity in session for the unified checkout
$_SESSION["buy_now"] = [
    "pid" => $pid,
    "qty" => $qty
];

// ✅ Redirect to unified checkout page
header("Location: checkout.php");
exit;

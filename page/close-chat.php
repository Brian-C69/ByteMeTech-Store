<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Ensure only admin can access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

$chat_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($chat_id <= 0) {
    $_SESSION["error_message"] = "Invalid chat ID.";
    header("Location: customer-support.php");
    exit;
}

// Check if chat exists
$stmt = $pdo->prepare("SELECT * FROM support_chats WHERE CHAT_ID = :cid");
$stmt->execute(["cid" => $chat_id]);
$chat = $stmt->fetch();

if (!$chat) {
    $_SESSION["error_message"] = "Chat not found.";
    header("Location: customer-support.php");
    exit;
}

// Update chat status to Closed
$update = $pdo->prepare("UPDATE support_chats SET STATUS = 'Closed' WHERE CHAT_ID = :cid");
$update->execute(["cid" => $chat_id]);

$_SESSION["success_message"] = "Chat has been closed.";
header("Location: customer-support.php");
exit;

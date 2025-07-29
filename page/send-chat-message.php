<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Unauthorized access.";
    header("Location: ../page/login.php");
    exit;
}

$chat_id = (int) ($_POST["chat_id"] ?? 0);
$sender_id = $_SESSION["user_id"];
$message = trim($_POST["message"] ?? "");

if ($chat_id <= 0 || $message === "") {
    $_SESSION["error_message"] = "Invalid chat or message.";
    header("Location: chat-admin-view.php?id=" . $chat_id);
    exit;
}

// Insert message as Admin
$stmt = $pdo->prepare("INSERT INTO support_messages (CHAT_ID, SENDER, SENDER_ID, MESSAGE) VALUES (:chat, 'Admin', :sid, :msg)");
$stmt->execute([
    "chat" => $chat_id,
    "sid" => $sender_id,
    "msg" => $message
]);

// Update last activity
$pdo->prepare("UPDATE support_chats SET UPDATED_AT = NOW() WHERE CHAT_ID = :cid")->execute(["cid" => $chat_id]);

header("Location: chat-admin-view.php?id=" . $chat_id);
exit;

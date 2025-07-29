<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$uid = $_SESSION["user_id"];

// Find open/in-progress chat
$stmt = $pdo->prepare("SELECT CHAT_ID FROM support_chats WHERE UID = :uid AND STATUS IN ('Open', 'In Progress') LIMIT 1");
$stmt->execute(["uid" => $uid]);
$chat = $stmt->fetch();

if (!$chat) {
    echo json_encode([]);
    exit;
}

$chat_id = $chat["CHAT_ID"];

// Get messages
$stmt = $pdo->prepare("SELECT SENDER, MESSAGE, TIMESTAMP FROM support_messages WHERE CHAT_ID = :chat_id ORDER BY TIMESTAMP ASC");
$stmt->execute(["chat_id" => $chat_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);

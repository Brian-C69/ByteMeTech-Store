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
$data = json_decode(file_get_contents("php://input"), true);
$message = trim($data["message"] ?? '');

if ($message === "") {
    http_response_code(400);
    echo json_encode(["error" => "Empty message"]);
    exit;
}

// Get or create open chat
$stmt = $pdo->prepare("SELECT CHAT_ID FROM support_chats WHERE UID = :uid AND STATUS IN ('Open', 'In Progress') LIMIT 1");
$stmt->execute(["uid" => $uid]);
$chat = $stmt->fetch();

if (!$chat) {
    $pdo->prepare("INSERT INTO support_chats (UID) VALUES (:uid)")->execute(["uid" => $uid]);
    $chat_id = $pdo->lastInsertId();
} else {
    $chat_id = $chat["CHAT_ID"];
}

// Insert the message
$pdo->prepare("
    INSERT INTO support_messages (CHAT_ID, SENDER, SENDER_ID, MESSAGE)
    VALUES (:chat_id, 'User', :uid, :msg)
")->execute([
    "chat_id" => $chat_id,
    "uid" => $uid,
    "msg" => $message
]);

echo json_encode(["success" => true]);

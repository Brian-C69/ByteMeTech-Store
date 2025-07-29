<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    http_response_code(403);
    exit("Access denied.");
}

$chat_id = isset($_GET["chat_id"]) ? (int)$_GET["chat_id"] : 0;
if ($chat_id <= 0) {
    http_response_code(400);
    exit("Invalid chat ID.");
}

// Fetch messages for the chat
$stmt = $pdo->prepare("
    SELECT * FROM support_messages
    WHERE CHAT_ID = :chat_id
    ORDER BY TIMESTAMP ASC
");
$stmt->execute(["chat_id" => $chat_id]);
$messages = $stmt->fetchAll();

// Render messages as HTML
foreach ($messages as $msg):
    $is_admin = $msg["SENDER"] === "Admin";
    $msg_class = $is_admin ? "admin-msg" : "user-msg";
?>
    <div class="message <?= $msg_class ?>">
        <strong><?= htmlspecialchars($msg["SENDER"]) ?>:</strong><br>
        <?= nl2br(htmlspecialchars($msg["MESSAGE"])) ?>
        <div style="font-size: 0.8em; color: #888;">
            <?= $msg["TIMESTAMP"] ?>
        </div>
    </div>
<?php endforeach; ?>

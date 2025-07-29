<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

$aid = $_SESSION["user_id"];
$chat_id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($chat_id <= 0) {
    $_SESSION["error_message"] = "Invalid chat ID.";
    header("Location: customer-support.php");
    exit;
}

// Assign admin if unassigned
$assign = $pdo->prepare("UPDATE support_chats SET AID = :aid, STATUS = 'In Progress' WHERE CHAT_ID = :cid AND AID IS NULL");
$assign->execute(["aid" => $aid, "cid" => $chat_id]);

$chat_stmt = $pdo->prepare("SELECT sc.*, u.USERNAME FROM support_chats sc JOIN users u ON sc.UID = u.UID WHERE sc.CHAT_ID = :cid");
$chat_stmt->execute(["cid" => $chat_id]);
$chat = $chat_stmt->fetch();

if (!$chat) {
    $_SESSION["error_message"] = "Chat not found.";
    header("Location: customer-support.php");
    exit;
}

include "../includes/headers.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Chat with <?= htmlspecialchars($chat["USERNAME"]) ?></title>
    <style>
        .chat-box {
            background: #fff;
            border: 1px solid #ccc;
            padding: 15px;
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        .user-msg {
            background-color: #f1f1f1;
        }
        .admin-msg {
            background-color: #d6f5d6;
            text-align: right;
        }
        .chat-form {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .chat-form label {
            font-weight: bold;
        }
        .chat-form textarea {
            width: 100%;
            height: 80px;
            padding: 10px;
            resize: vertical;
        }
        .login-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>

<div class="container">
    <h1>Chat with <?= htmlspecialchars($chat["USERNAME"]) ?> (Chat #<?= $chat["CHAT_ID"] ?>)</h1>

    <div class="chat-box" id="chat-box"></div>

    <form class="chat-form" id="chat-form" action="send-chat-message.php" method="POST">
        <input type="hidden" name="chat_id" value="<?= $chat_id ?>">
        <input type="hidden" name="sender" value="Admin">
        <input type="hidden" name="sender_id" value="<?= $aid ?>">
        <label for="message">Reply</label>
        <textarea name="message" id="message" required></textarea>
        <div class="login-options">
            <button type="submit" class="btn btn-green">Send</button>
            <a href="close-chat.php?id=<?= $chat_id ?>" class="btn btn-red" onclick="return confirm('Close this chat?');">Close Chat</a>
            <a href="customer-support.php" class="btn btn-grey">Back</a>
        </div>
    </form>
</div>

<script>
    const chatBox = document.getElementById("chat-box");

    function fetchMessages(scrollToBottom = true) {
        fetch("fetch-chat-messages.php?chat_id=<?= $chat_id ?>")
            .then(response => response.text())
            .then(html => {
                chatBox.innerHTML = html;
                if (scrollToBottom) {
                    setTimeout(() => {
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }, 100);
                }
            });
    }

    setInterval(() => fetchMessages(false), 1000);
    fetchMessages(true);

    window.addEventListener("load", () => {
        document.getElementById("message").focus();
    });
</script>
</body>
</html>

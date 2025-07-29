<?php
require_once "../includes/base.php";
require_once "../includes/config.php";
require_once "../lib/SimplePager.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Customer Support</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #f8f8f8;
        }
        .btn {
            padding: 5px 10px;
            margin: 2px;
            text-decoration: none;
        }
        .btn-blue {
            background: #3498db;
            color: white;
        }
        .btn-red {
            background: #e74c3c;
            color: white;
        }
        .pager {
            text-align: right;
            margin-top: 10px;
        }
        .pager a {
            margin: 0 5px;
            padding: 5px 10px;
            border: 1px solid #ccc;
            background: #f9f9f9;
            text-decoration: none;
            border-radius: 4px;
        }
        .pager a.active {
            background: #3498db;
            color: #fff;
            border-color: #3498db;
        }
    </style>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>

<div class="container">
    <h1>Customer Support</h1>

    <?php if (!empty($_SESSION["success_message"])): ?>
        <div class="alert-success">
            <?= htmlspecialchars($_SESSION["success_message"]) ?>
            <?php unset($_SESSION["success_message"]); ?>
        </div>
    <?php endif; ?>

    <!-- Live Chat Section -->
    <h2>Live Chat Requests</h2>
    <?php
    $live_chat_page = (int) ($_GET["chat_page"] ?? 1);
    $count_query = "SELECT COUNT(*) FROM support_chats sc JOIN users u ON sc.UID = u.UID";
    $live_chat_query = "
        SELECT sc.CHAT_ID, u.USERNAME, sc.STATUS, sc.UPDATED_AT
        FROM support_chats sc
        JOIN users u ON sc.UID = u.UID
        ORDER BY sc.UPDATED_AT DESC
    ";
    $live_chat_pager = new SimplePager($pdo, $live_chat_query, [], 10, $live_chat_page, $count_query);
    $chats = $live_chat_pager->result;
    ?>
    <p>
    <?= $live_chat_pager->count ?> of <?= $live_chat_pager->item_count ?> chat(s) found |
    Page <?= $live_chat_pager->page ?> of <?= $live_chat_pager->page_count ?>
</p>
    <table>
        <thead>
            <tr>
                <th>#</th>  
                <th>User</th>
                <th>Status</th>
                <th>Last Updated</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($chats as $i => $chat): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($chat["USERNAME"]) ?></td>
                    <td><?= $chat["STATUS"] ?></td>
                    <td><?= $chat["UPDATED_AT"] ?></td>
                    <td>
                        <a href="chat-admin-view.php?id=<?= $chat["CHAT_ID"] ?>" class="btn btn-blue">Open</a>
                        <a href="delete-chat.php?id=<?= $chat["CHAT_ID"] ?>" class="btn btn-red" onclick="return confirm('Delete this chat?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pager">
        <?php $live_chat_pager->html('', 'chat_page') ?>
    </div>

    <!-- Public Enquiry Section -->
    <h2 style="margin-top: 50px;">Public Enquiries</h2>
    <?php
    $stmt = $pdo->query("SELECT * FROM contact_form ORDER BY CONTACT_ID DESC");
    $enquiries = $stmt->fetchAll();
    ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>IP Address</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($enquiries as $i => $e): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($e["CONTACT_FIRSTNAME"] . " " . $e["CONTACT_LASTNAME"]) ?></td>
                    <td><?= htmlspecialchars($e["CONTACT_EMAIL"]) ?></td>
                    <td><?= $e["CONTACT_IP_ADDRESS"] ?></td>
                    <td>
                        <a href="contact-view-details.php?id=<?= $e["CONTACT_ID"] ?>" class="btn btn-blue">View</a>
                        <a href="delete-enquiry.php?id=<?= $e["CONTACT_ID"] ?>" class="btn btn-red" onclick="return confirm('Delete this enquiry?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>

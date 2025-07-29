<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

$cid = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

$stmt = $pdo->prepare("SELECT * FROM contact_form WHERE CONTACT_ID = :cid");
$stmt->execute(["cid" => $cid]);
$contact = $stmt->fetch();

if (!$contact) {
    $_SESSION["error_message"] = "Message not found.";
    header("Location: customer-support.php");
    exit;
}

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Enquiry Details</title>
    <style>
        .details-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .details-container h2 {
            margin-top: 0;
        }
        .details-container p {
            margin-bottom: 10px;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            background: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>

<div class="container details-container">
    <h2>Enquiry Details</h2>
    <p><strong>First Name:</strong> <?= htmlspecialchars($contact["CONTACT_FIRSTNAME"]) ?></p>
    <p><strong>Last Name:</strong> <?= htmlspecialchars($contact["CONTACT_LASTNAME"]) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($contact["CONTACT_EMAIL"]) ?></p>
    <p><strong>IP Address:</strong> <?= htmlspecialchars($contact["CONTACT_IP_ADDRESS"]) ?></p>
    <p><strong>Message:</strong></p>
    <pre><?= htmlspecialchars($contact["CONTACT_MESSAGE"]) ?></pre>
    
    <a href="customer-support.php" class="btn btn-grey">Back to Support</a>
</div>

</body>
</html>

<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

$aid = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT * FROM admins WHERE AID = :aid");
$stmt->execute(["aid" => $aid]);
$admin = $stmt->fetch();

if (!$admin) {
    $_SESSION["error_message"] = "Admin profile not found.";
    header("Location: ../page/login.php");
    exit;
}

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Profile</title>
    <style>
        .profile-container {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .profile-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-container .profile-row {
            margin-bottom: 15px;
        }

        .profile-container .profile-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }

        .btn-group {
            text-align: center;
            margin-top: 20px;
        }

        .btn-group a {
            margin: 0 10px;
        }
    </style>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>

<div class="container profile-container">
    <h2>Admin Profile</h2>
    <div class="profile-row"><span class="profile-label">Username:</span><?= htmlspecialchars($admin["USERNAME"]) ?></div>
    <div class="profile-row"><span class="profile-label">Email:</span><?= htmlspecialchars($admin["EMAIL"]) ?></div>
    <div class="profile-row"><span class="profile-label">First Name:</span><?= htmlspecialchars($admin["FIRST_NAME"]) ?></div>
    <div class="profile-row"><span class="profile-label">Last Name:</span><?= htmlspecialchars($admin["LAST_NAME"]) ?></div>
    <div class="profile-row"><span class="profile-label">Role:</span><?= htmlspecialchars($admin["ROLE"]) ?></div>
    <div class="profile-row"><span class="profile-label">Created At:</span><?= $admin["CREATED_AT"] ?></div>
    <div class="profile-row"><span class="profile-label">Last Logged In:</span><?= $admin["LAST_LOGGEDIN"] ?: "Never" ?></div>

    <div class="btn-group">
        <a href="edit-admin-profile.php" class="btn btn-blue">Edit Profile</a>
        <a href="admin-reset-password.php" class="btn btn-red">Reset Password</a>
    </div>
</div>

</body>
</html>

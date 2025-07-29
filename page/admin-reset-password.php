<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

$aid = $_SESSION["user_id"];
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current = $_POST["current_password"] ?? "";
    $new = $_POST["new_password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    if (empty($current) || empty($new) || empty($confirm)) {
        $error = "All fields are required.";
    } elseif ($new !== $confirm) {
        $error = "New password and confirmation do not match.";
    } elseif (strlen($new) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $stmt = $pdo->prepare("SELECT PASSWORD FROM admins WHERE AID = :aid");
        $stmt->execute(["aid" => $aid]);
        $hash = $stmt->fetchColumn();

        if ($hash && password_verify($current, $hash)) {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE admins SET PASSWORD = :pwd WHERE AID = :aid");
            $update->execute([
                "pwd" => $newHash,
                "aid" => $aid
            ]);
            $success = "Password updated successfully.";
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Admin Password</title>
    <style>
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="password"] {
            width: 100%;
            padding: 8px;
        }

        .btn-group {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>

<div class="container">
    <h2>Reset Password</h2>

    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" name="current_password" class="input-field" required>
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" name="new_password" class="input-field" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" name="confirm_password" class="input-field" required>
        </div>

        <div class="login-options">
            <button type="submit" class="btn btn-blue">Update Password</button>
            <a href="admin-profile.php" class="btn btn-grey">Cancel</a>
        </div>
    </form>
</div>


</body>
</html>

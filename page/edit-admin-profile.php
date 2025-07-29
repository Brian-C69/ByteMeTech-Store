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
    $_SESSION["error_message"] = "Admin not found.";
    header("Location: admin-profile.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);

    if ($email == "") {
        $_SESSION["error_message"] = "Email is required.";
    } else {
        $stmt = $pdo->prepare("UPDATE admins SET FIRST_NAME = :fname, LAST_NAME = :lname, EMAIL = :email WHERE AID = :aid");
        $stmt->execute([
            "fname" => $first_name,
            "lname" => $last_name,
            "email" => $email,
            "aid" => $aid
        ]);

        $_SESSION["success_message"] = "Profile updated successfully.";
        header("Location: admin-profile.php");
        exit;
    }
}

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Admin Profile</title>
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

        input[type="text"], input[type="email"] {
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
    <h2>Edit Profile</h2>

    <?php if (!empty($_SESSION["error_message"])): ?>
        <div class="alert"><?= htmlspecialchars($_SESSION["error_message"]) ?></div>
        <?php unset($_SESSION["error_message"]); ?>
    <?php endif; ?>

    <form method="post" class="login-form">
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" class="input-field" name="first_name" id="first_name" value="<?= htmlspecialchars($admin["FIRST_NAME"]) ?>">
        </div>
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" class="input-field" name="last_name" id="last_name" value="<?= htmlspecialchars($admin["LAST_NAME"]) ?>">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="input-field" name="email" id="email" value="<?= htmlspecialchars($admin["EMAIL"]) ?>" required>
        </div>

        <div class="login-options">
            <button type="submit" class="btn btn-blue">Save Changes</button>
            <a href="admin-profile.php" class="btn btn-grey">Cancel</a>
        </div>
    </form>
</div>


</body>
</html>

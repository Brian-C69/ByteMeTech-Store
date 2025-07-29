<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Admin only access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

// Get admin ID
$aid = (int) ($_GET["id"] ?? 0);
if ($aid <= 0) {
    $_SESSION["error_message"] = "Invalid admin ID.";
    header("Location: admins.php");
    exit;
}

// Fetch admin data
$stmt = $pdo->prepare("SELECT * FROM admins WHERE AID = :aid");
$stmt->execute(["aid" => $aid]);
$admin = $stmt->fetch();

if (!$admin) {
    $_SESSION["error_message"] = "Admin not found.";
    header("Location: admins.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $role = $_POST["role"];

    if (empty($username) || empty($email)) {
        $error = "Username and email are required.";
    } else {
        $stmt = $pdo->prepare("UPDATE admins SET USERNAME = :username, EMAIL = :email, FIRST_NAME = :first_name, LAST_NAME = :last_name, ROLE = :role WHERE AID = :aid");
        $stmt->execute([
            "username" => $username,
            "email" => $email,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "role" => $role,
            "aid" => $aid
        ]);

        $_SESSION["success_message"] = "Admin updated successfully.";
        header("Location: admins.php");
        exit;
    }
}

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Admin | Admin Panel</title>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>
<div class="container">
    <h1>Edit Admin</h1>

    <?php if (!empty($error)): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="login-form">
        <label for="username">Username</label>
        <input type="text" name="username" class="input-field" value="<?= htmlspecialchars($admin["USERNAME"]) ?>" required>

        <label for="email">Email</label>
        <input type="email" name="email" class="input-field" value="<?= htmlspecialchars($admin["EMAIL"]) ?>" required>

        <label for="first_name">First Name</label>
        <input type="text" name="first_name" class="input-field" value="<?= htmlspecialchars($admin["FIRST_NAME"]) ?>">

        <label for="last_name">Last Name</label>
        <input type="text" name="last_name" class="input-field" value="<?= htmlspecialchars($admin["LAST_NAME"]) ?>">

        <label for="role">Role</label>
        <select name="role" class="input-field" required>
            <?php
            $roles = ["Super Admin", "Logistics", "Sales", "Customer Support", "Marketing", "Moderator"];
            foreach ($roles as $r):
                $selected = ($admin["ROLE"] === $r) ? 'selected' : '';
                echo "<option value=\"$r\" $selected>$r</option>";
            endforeach;
            ?>
        </select>

        <button type="submit" class="btn btn-green">Save Changes</button>
        <a href="admins.php" class="btn btn-gray">Back</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>

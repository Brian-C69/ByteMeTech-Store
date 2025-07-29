<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Restrict to Super Admins
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

$admin = [
    "USERNAME"   => "",
    "EMAIL"      => "",
    "PASSWORD"   => "",
    "FIRST_NAME" => "",
    "LAST_NAME"  => "",
    "ROLE"       => ""
];

$roles = [
    "Super Admin"     => "Super Admin",
    "Logistics"       => "Logistics",
    "Sales"           => "Sales",
    "Customer Support"=> "Customer Support",
    "Marketing"       => "Marketing",
    "Moderator"       => "Moderator"
];

$_err = [];

if (is_post()) {
    foreach ($admin as $key => &$val) {
        $val = trim($_POST[$key] ?? "");
    }

    if ($admin["USERNAME"] === "") $_err["USERNAME"] = "Required.";
    if (!filter_var($admin["EMAIL"], FILTER_VALIDATE_EMAIL)) $_err["EMAIL"] = "Invalid email.";
    if ($admin["PASSWORD"] === "") $_err["PASSWORD"] = "Required.";
    if ($admin["ROLE"] === "") $_err["ROLE"] = "Please select a role.";

    // Check for unique username
    $check = $pdo->prepare("SELECT 1 FROM admins WHERE USERNAME = ?");
    $check->execute([$admin["USERNAME"]]);
    if ($check->fetchColumn()) $_err["USERNAME"] = "Username already exists.";

    if (!$_err) {
        $hashed = password_hash($admin["PASSWORD"], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO admins 
            (USERNAME, PASSWORD, EMAIL, FIRST_NAME, LAST_NAME, ROLE) 
            VALUES (:username, :password, :email, :first, :last, :role)");
        $stmt->execute([
            "username" => $admin["USERNAME"],
            "password" => $hashed,
            "email"    => $admin["EMAIL"],
            "first"    => $admin["FIRST_NAME"],
            "last"     => $admin["LAST_NAME"],
            "role"     => $admin["ROLE"]
        ]);

        temp("info", "Admin added successfully.");
        header("Location: admins.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Admin | ByteMeTech</title>
    <?php include "../includes/headers.php"; ?>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>

<div class="container">
    <h1>Add Admin</h1>
    <form method="post" class="login-form">

        <label>Username</label>
        <?= html_text("USERNAME", '', $admin["USERNAME"]) ?>
        <?= err("USERNAME") ?>

        <label>Email</label>
        <?= html_text("EMAIL", '', $admin["EMAIL"]) ?>
        <?= err("EMAIL") ?>

        <label>Password</label>
        <input type="password" name="PASSWORD" class="input-field">
        <?= err("PASSWORD") ?>

        <label>First Name</label>
        <?= html_text("FIRST_NAME", '', $admin["FIRST_NAME"]) ?>

        <label>Last Name</label>
        <?= html_text("LAST_NAME", '', $admin["LAST_NAME"]) ?>

        <label>Role</label>
        <?= html_select("ROLE", $roles, "- Select Role -", $admin["ROLE"]) ?>
        <?= err("ROLE") ?>

        <div class="full-width">
            <button type="submit" class="btn btn-green">Add Admin</button>
            <a href="admins.php" class="btn btn-blue">Back</a>
        </div>

    </form>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>

<?php
// edit-customer.php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Check admin access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("location: ../page/login.php");
    exit;
}

// Get customer ID
$id = $_GET["id"] ?? null;
if (!$id || !ctype_digit($id)) {
    temp("info", "Invalid customer ID.");
    header("location: customers.php");
    exit;
}

// Fetch customer
$stmt = $pdo->prepare("SELECT * FROM users WHERE UID = :uid");
$stmt->execute(["uid" => $id]);
$user = $stmt->fetch();

if (!$user) {
    temp("info", "Customer not found.");
    header("location: customers.php");
    exit;
}

$firstName = $user["FIRSTNAME"];
$lastName = $user["LASTNAME"];
$email = $user["EMAIL"];
$verified = $user["VERIFIED"];
$verified_err = $firstName_err = $lastName_err = $email_err = "";

// Handle form submit
if (is_post()) {
    $firstName = trim($_POST["firstName"]);
    $lastName = trim($_POST["lastName"]);
    $verified = isset($_POST["verified"]) ? 1 : 0;
    $email = trim($_POST["email"]);

    if ($firstName === "") {
        $firstName_err = "First name required.";
    }
    if ($lastName === "") {
        $lastName_err = "Last name required.";
    }
    
    if ($email === "") {
        $email_err = "Email required.";
    }

    if (!$firstName_err && !$lastName_err) {
        $stmt = $pdo->prepare("UPDATE users SET FIRSTNAME = :first, LASTNAME = :last, EMAIL = :email, VERIFIED = :verified WHERE UID = :uid");
        $stmt->execute([
            "first" => $firstName,
            "last" => $lastName,
            "email" => $email,
            "verified" => $verified,
            "uid" => $id
        ]);

        temp("info", "Customer updated.");
        header("location: customers.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Customer</title>
    <?php include '../includes/headers.php'; ?>
</head>
<body class="bg-light">
<?php include '../includes/sidebar.php'; ?>

<div class="container">
    <h1>Edit Customer</h1>
    <form method="post" class="login-form">
        <label>First Name</label>
        <?= err("firstName") ?>
        <?= html_text("firstName") ?>

        <label>Last Name</label>
        <?= err("lastName") ?>
        <?= html_text("lastName") ?>
        
        <label>Email</label>
        <?= err("email") ?>
        <?= html_text("email") ?>

        <label>Verified</label>
        <?= html_checkbox("verified", "Email Verified") ?>

        <section>
            <button class="btn btn-green">Update</button>
            <a href="customers.php" class="btn btn-blue">Back</a>
        </section>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>

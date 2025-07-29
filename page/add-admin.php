<?php
require_once "../includes/config.php";
require_once "../includes/base.php";

// Access control: Only admins can access
//if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
//    $_SESSION["error_message"] = "Access denied.";
//    header("location: ../login.php");
//    exit;
//}

// Define role options
$roles = [
    "Super Admin" => "Super Admin",
    "Logistics" => "Logistics",
    "Sales" => "Sales",
    "Customer Support" => "Customer Support",
    "Marketing" => "Marketing",
    "Moderator" => "Moderator",
];

// Initialize variables
$username = $email = $password = $confirm_password = $first_name = $last_name = $role = "";
$_err = [];

// Handle form submission
if (is_post()) {
    $username = req("username");
    $email = req("email");
    $password = req("password");
    $confirm_password = req("confirm_password");
    $first_name = req("first_name");
    $last_name = req("last_name");
    $role = req("role");

    // Validate inputs
    if ($username == '')
        $_err["username"] = "Username is required.";
    if ($email == '' || !is_email($email))
        $_err["email"] = "Valid email is required.";
    if ($password == '' || strlen($password) < 6)
        $_err["password"] = "Minimum 6 characters.";
    if ($password !== $confirm_password)
        $_err["confirm_password"] = "Passwords do not match.";
    if (!isset($roles[$role]))
        $_err["role"] = "Please select a valid role.";

    // Check if username or email already exists
    if (is_exists($username, 'admins', 'USERNAME'))
        $_err['username'] = "Username already exists.";
    if (is_exists($email, 'admins', 'EMAIL'))
        $_err['email'] = "Email already registered.";

    // Insert admin if no errors
    if (!$_err) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO admins (USERNAME, EMAIL, PASSWORD, FIRST_NAME, LAST_NAME, ROLE)
                               VALUES (:username, :email, :password, :fname, :lname, :role)");
        $stmt->execute([
            "username" => $username,
            "email" => $email,
            "password" => $hash,
            "fname" => $first_name,
            "lname" => $last_name,
            "role" => $role
        ]);

        temp("info", "Admin created successfully.");
        redirect();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Add Admin | ByteMeTech.com</title>
<?php include '../includes/headers.php'; ?>
        <script>
            function togglePasswordVisibility() {
                const input = document.getElementById("password");
                const toggle = document.querySelector(".toggle-password");
                if (input.type === "password") {
                    input.type = "text";
                    toggle.textContent = "🙈";
                } else {
                    input.type = "password";
                    toggle.textContent = "👁️";
                }
            }
        </script>
    </head>

    <body class="bg-light">
            <?php include '../includes/navbar.php'; ?>

        <div class="container bg-dark">
            <h1 class="white-text">Add New Admin</h1>
        </div>

        <div class="container">
                <?php if ($msg = temp("info")): ?>
                <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
                <?php endif; ?>

                <form method="post" class="login-form">
                    <label for="username">Username</label>
                    <?= err("username") ?>
                    <?= html_text("username", 'maxlength="50"') ?>

                    <label for="email">Email</label>
                    <?= err("email") ?>
                    <?= html_text("email", 'maxlength="100"') ?>

                    <label for="first_name">First Name</label>
                    <?= html_text("first_name") ?>

                    <label for="last_name">Last Name</label>
                    <?= html_text("last_name") ?>

                    <label for="role">Role</label>
                    <?= err("role") ?>
                    <?= html_select("role", $roles, "- Select Role -") ?>

                    <label for="password">Password</label>
                    <?= err("password") ?>
                    <?= html_password("password", 'maxlength="100"') ?>

                    <label for="confirm_password">Confirm Password</label>
                    <?= err("confirm_password") ?>
                    <?= html_password("confirm_password", 'maxlength="100"') ?>
                    
                    <div></div>
                    <button type="submit" class="btn btn-green">Add Admin</button>
                </form>
        </div>

<?php include '../includes/footer.php'; ?>
    </body>
</html>

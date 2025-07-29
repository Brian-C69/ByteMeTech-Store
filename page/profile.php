<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    $_SESSION["error_message"] = "You must log in to continue.";
    header("location: login.php");
    exit;
}

$uid = $_SESSION["user_id"];
$default_picture = "../images/default-profile.png";

// Get user details from DB
$stmt = $pdo->prepare("SELECT * FROM users WHERE UID = :uid");
$stmt->execute(["uid" => $uid]);
$user = $stmt->fetch();

if (!$user) {
    temp("info", "User not found.");
    header("location: logout.php");
    exit;
}

// User details
$username = $user["USERNAME"];
$firstName = $user["FIRSTNAME" ?? "Not set"];
$lastName = $user["LASTNAME"] ?? "Not set";
$gender = $user["GENDER"] ?? "Not set";
$birthdate = $user["BIRTHDATE"] ?? null;
$email = $user["EMAIL"];
$verified = $user["VERIFIED"] ? "Yes" : "No";
$profile_picture = $user["PROFILE_PICTURE"] ?: $default_picture;

$birthdate_display = "Not set";

if ($birthdate) {
    $birth = new DateTime($birthdate);
    $today = new DateTime();
    $age = $birth->diff($today)->y;
    $birthdate_display = "$birthdate (Age: $age)";
}

// Handle Profile Picture Upload

if (is_post() && isset($_FILES["profile_picture"])) {
    $f = get_file("profile_picture");

    if (!$f) {
        temp("info", "No file uploaded.");
    } elseif (!str_starts_with($f->type, "image/")) {
        temp("info", "Only image files are allowed.");
    } elseif ($f->size > 10 * 1024 * 1024) {
        temp("info", "Max file size is 10MB.");
    } else {
        // Save photo and update DB
        $photo = save_photo($f, "../uploads", 500, 500);
        $path = "../uploads/$photo";

        $stmt = $pdo->prepare("UPDATE users SET PROFILE_PICTURE = :pic WHERE UID = :uid");
        $stmt->execute(["pic" => $path, "uid" => $uid]);

        temp("info", "Profile picture updated.");
        redirect();
    }
}

// Handle Delete Profile Picture
if (is_post() && isset($_POST["delete_profile"])) {
    if ($profile_picture !== $default_picture && file_exists($profile_picture)) {
        unlink($profile_picture);
    }

    $stmt = $pdo->prepare("UPDATE users SET PROFILE_PICTURE = NULL WHERE UID = :uid");
    $stmt->execute(["uid" => $uid]);

    temp("info", "Profile picture deleted.");
    redirect();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>ByteMeTech.com | Profile</title>
    <?php include '../includes/headers.php'; ?>
    <script>
        function handleDragOver(event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = "copy";
        }

        function handleDrop(event) {
            event.preventDefault();
            const fileInput = document.getElementById("file-input");
            fileInput.files = event.dataTransfer.files;
            fileInput.form.submit();
        }
    </script>
</head>
<body class="bg-light">
<?php include '../includes/navbar.php'; ?>

<div class="container bg-dark">
    <h1>Profile</h1>
</div>

<div class="container">
    <?php if ($msg = temp("info")): ?>
        <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
       

    <!-- Profile Picture Upload Form -->
    <form method="post" enctype="multipart/form-data" class="profile-container">
        <img id="profile-img"
             src="<?= htmlspecialchars($profile_picture) ?>?t=<?= time(); ?>"
             class="profile-img"
             alt="Profile Picture">

        <div class="drop-zone" ondragover="handleDragOver(event)" ondrop="handleDrop(event)">
            Drag & Drop to Upload
        </div>

        <input type="file" id="file-input" name="profile_picture" hidden accept="image/png, image/jpeg"
               onchange="this.form.submit();">

        <button type="button" class="btn btn-blue" onclick="document.getElementById('file-input').click();">
            Select File
        </button>

        <button type="submit" name="delete_profile" class="btn btn-red"
                onclick="return confirm('Are you sure you want to delete your profile picture?');">
            Delete Picture
        </button>
    </form>
        <?php if (isset($_SESSION["error_message"])): ?>
    <div class="alert"><?= htmlspecialchars($_SESSION["error_message"]) ?></div>
    <?php unset($_SESSION["error_message"]); ?>
    <?php endif; ?>
    <!-- User Information Display -->
    <div class="profile-info mt-4">
        <p><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
        <p><strong>Name:</strong> <?= htmlspecialchars("$firstName $lastName") ?></p>
        <p><strong>Gender:</strong> <?= htmlspecialchars($gender) ?></p>
        <p><strong>Birthdate:</strong> <?= htmlspecialchars($birthdate_display) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
        <p><strong>Verified:</strong> <?= htmlspecialchars($verified) ?></p>
    </div>

    <!-- Action Buttons -->
    <div class="mt-3">
        <a href="edit-profile.php" class="btn btn-yellow">Edit</a>
        <a href="reset-password.php" class="btn btn-orange">Reset Password</a>
        <a href="address.php" class="btn btn-blue">Manage Address</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>

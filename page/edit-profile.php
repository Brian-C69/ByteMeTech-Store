<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    $_SESSION['error_message'] = "You must log in to continue.";
    header("location: login.php");
    exit;
}

// Fetch user details from DB
$stmt = $pdo->prepare("SELECT * FROM users WHERE UID = :uid");
$stmt->bindParam(":uid", $_SESSION["user_id"], PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch();

// Prefill form values
$firstName = $user["FIRSTNAME"] ?? "";
$lastName = $user["LASTNAME"] ?? "";
$gender = $user["GENDER"] ?? "";
$email = $user["EMAIL"] ?? "";
$birthdate = $user["BIRTHDATE"] ?? "";
$verifiedStatus = $user["VERIFIED"] ?? 0;

// Define error holders
$firstName_err = $lastName_err = $gender_err = $birthdate_err = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // First Name
    $firstName = trim($_POST["firstName"]);
    if (empty($firstName)) {
        $firstName_err = "Please enter your first name.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $firstName)) {
        $firstName_err = "First name can only contain letters and spaces.";
    }

    // Last Name
    $lastName = trim($_POST["lastName"]);
    if (empty($lastName)) {
        $lastName_err = "Please enter your last name.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $lastName)) {
        $lastName_err = "Last name can only contain letters and spaces.";
    }
    // Gender
    $gender = $_POST["gender"] ?? "";
    if (empty($gender)) {
        $gender_err = "Please select your gender.";
    }

    // Birthdate
    $birthdate = trim($_POST["birthdate"]);
    if (empty($birthdate)) {
        $birthdate_err = "Please enter your birthdate.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
        $birthdate_err = "Invalid birthdate format (use YYYY-MM-DD).";
    } else {
        $birth_timestamp = strtotime($birthdate);
        $current_timestamp = time();

        if ($birth_timestamp === false) {
            $birthdate_err = "Invalid date value.";
        } elseif ($birth_timestamp > $current_timestamp) {
            $birthdate_err = "Birthdate cannot be in the future.";
        } else {
            $age = floor(($current_timestamp - $birth_timestamp) / (365.25 * 24 * 60 * 60));
            if ($age > 150) {
                $birthdate_err = "Age cannot be more than 150 years.";
            }
        }
    }

    // If no errors, update the profile
    if (empty($firstName_err) && empty($lastName_err) && empty($gender_err) && empty($birthdate_err)) {
        $stmt = $pdo->prepare("UPDATE users SET FIRSTNAME = :firstName, LASTNAME = :lastName, GENDER = :gender, BIRTHDATE = :birthdate WHERE UID = :uid");
        $stmt->execute([
            "firstName" => $firstName,
            "lastName" => $lastName,
            "gender" => $gender,
            "birthdate" => $birthdate,
            "uid" => $_SESSION["user_id"]
        ]);

        $_SESSION["success_message"] = "Profile updated successfully!";
        header("location: profile.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>ByteMeTech.com | Edit Profile</title>
    <?php include '../includes/headers.php'; ?>
</head>

<body class="bg-light">
    <?php include '../includes/navbar.php'; ?>

    <div class="container bg-dark">
        <h1 class="white-text">Edit Profile</h1>
    </div>

    <div class="container">
        <form action="edit-profile.php" method="post" class="login-form">
            <!-- First Name -->
            <label for="firstName">First Name</label>
            <?php if (!empty($firstName_err)) echo '<div class="alert">' . $firstName_err . '</div>'; ?>
            <input type="text" id="firstName" name="firstName" class="input-field" value="<?php echo htmlspecialchars($firstName); ?>">

            <!-- Last Name -->
            <label for="lastName">Last Name</label>
            <?php if (!empty($lastName_err)) echo '<div class="alert">' . $lastName_err . '</div>'; ?>
            <input type="text" id="lastName" name="lastName" class="input-field" value="<?php echo htmlspecialchars($lastName); ?>">

            <!-- Gender -->
            <label for="gender">Gender</label>
            <?php if (!empty($gender_err)) echo '<div class="alert">' . $gender_err . '</div>'; ?>
            <div class="radio-group">
                <label>
                    <input type="radio" name="gender" value="Male" <?php if ($gender === "Male") echo "checked"; ?>>
                    Male
                </label>
                <label>
                    <input type="radio" name="gender" value="Female" <?php if ($gender === "Female") echo "checked"; ?>>
                    Female
                </label>
            </div>
            
            <br>
            
            <!-- Birthday -->
            <label for="birthdate">Birthdate</label>
            <?php if (!empty($birthdate_err)) echo '<div class="alert">' . $birthdate_err . '</div>'; ?>
            <?php html_date("birthdate"); ?>

            <!-- Email (Read-Only) -->
            <div><p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p></div>
            <?php if (!$verifiedStatus): ?>
                <p><a href="verify-email.php">Verify email here</a></p>
            <?php endif; ?>

            <!-- Action Buttons -->
            <button type="submit" class="btn btn-green">Save</button>
            <a href="profile.php" class="btn btn-blue">Back</a>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>

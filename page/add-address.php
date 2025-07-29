<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Fetch countries and states
$countries = $pdo->query("SELECT * FROM Countries ORDER BY COUNTRY_NAME ASC")->fetchAll();
$states = $pdo->query("SELECT * FROM States ORDER BY STATE_NAME ASC")->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $unit = trim($_POST["unit"] ?? "");
    $street = trim($_POST["street"] ?? "");
    $city = trim($_POST["city"] ?? "");
    $state_id = (int) ($_POST["state"] ?? 0);
    $country_id = (int) ($_POST["country"] ?? 0);
    $type = $_POST["type"] ?? "Delivery";
    $default = isset($_POST["default"]);

    if (!$unit || !$street || !$city || !$state_id || !$country_id) {
        $error = "All fields are required.";
    } else {
        $uid = $_SESSION['user_id'] ?? 0;

        if ($default) {
            $pdo->prepare("UPDATE Address SET DEFAULT_ADDRESS = FALSE WHERE UID = ? AND TYPE = ?")
                ->execute([$uid, $type]);
        }

        $stmt = $pdo->prepare("INSERT INTO Address (UNIT_NUMBER, STREET, CITY, STATE_ID, COUNTRY_ID, UID, TYPE, DEFAULT_ADDRESS)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$unit, $street, $city, $state_id, $country_id, $uid, $type, $default]);

        $_SESSION['success_message'] = "Address added successfully.";
        header("Location: address.php");
        exit;
    }
}

include "../includes/headers.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Address | ByteMeTech</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<?php include "../includes/navbar.php"; ?>
<div class="container bg-dark">
    <h1>Add New Address</h1>
</div>
<div class="container">
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="post" class="login-form">
        <label>Unit Number</label>
        <input type="text" name="unit" class="input-field">

        <label>Street</label>
        <input type="text" name="street" class="input-field">

        <label>City</label>
        <input type="text" name="city" class="input-field">

        <label>Country</label>
        <select name="country" id="country" class="input-field">
            <option value="">Select Country</option>
            <?php foreach ($countries as $country): ?>
                <option value="<?= $country['COUNTRY_ID'] ?>"><?= htmlspecialchars($country['COUNTRY_NAME']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>State</label>
        <select name="state" id="state" class="input-field" required>
            <option value="">Select State</option>
            <?php foreach ($states as $sid => $state): ?>
                <option value="<?= $sid ?>" data-country="<?= $state['COUNTRY_ID'] ?>">
                    <?= htmlspecialchars($state['STATE_NAME']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Type</label>
        <select name="type" class="input-field">
            <option value="Delivery">Delivery</option>
            <option value="Billing">Billing</option>
        </select>

        <label><input type="checkbox" name="default"> Set as default</label><br><br>

        <button type="submit" class="btn btn-blue">Add Address</button>
        <a href="address.php" class="btn">Cancel</a>
    </form>
</div>
<script>
$(function () {
    $('#country').on('change', function () {
        var selected = $(this).val();
        $('#state option').hide().filter(function () {
            return $(this).data('country') == selected;
        }).show();
        $('#state').val('');
    }).trigger('change');
});
</script>
<?php include "../includes/footer.php"; ?>
</body>
</html>

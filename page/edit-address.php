<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["user_id"];
$addid = (int) ($_GET["id"] ?? 0);

// Fetch the existing address
$stmt = $pdo->prepare("SELECT * FROM Address WHERE UID = :uid AND ADDID = :addid");
$stmt->execute(["uid" => $uid, "addid" => $addid]);
$address = $stmt->fetch();

if (!$address) {
    temp("info", "Address not found.");
    header("Location: address.php");
    exit;
}

// Fetch dropdown options
$countries = $pdo->query("SELECT COUNTRY_ID, COUNTRY_NAME FROM Countries ORDER BY COUNTRY_NAME")->fetchAll(PDO::FETCH_ASSOC);
$states = $pdo->query("SELECT STATE_ID, STATE_NAME, COUNTRY_ID FROM States ORDER BY STATE_NAME")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $unit = trim($_POST["unit"]);
    $street = trim($_POST["street"]);
    $city = trim($_POST["city"]);
    $state_id = (int) ($_POST["state_id"] ?? 0);
    $country_id = (int) ($_POST["country_id"] ?? 0);
    $type = $_POST["type"] ?? "Delivery";
    $is_default = isset($_POST["default_address"]) ? 1 : 0;

    if (!$unit || !$street || !$city || !$state_id || !$country_id) {
        $errors[] = "All fields are required.";
    }

    if (empty($errors)) {
        if ($is_default) {
            $pdo->prepare("UPDATE Address SET DEFAULT_ADDRESS = 0 WHERE UID = ?")->execute([$uid]);
        }

        $stmt = $pdo->prepare("UPDATE Address SET 
            UNIT_NUMBER = :unit, 
            STREET = :street, 
            CITY = :city,
            STATE_ID = :state_id,
            COUNTRY_ID = :country_id,
            TYPE = :type,
            DEFAULT_ADDRESS = :is_default
            WHERE UID = :uid AND ADDID = :addid
        ");
        $stmt->execute([
            "unit" => $unit,
            "street" => $street,
            "city" => $city,
            "state_id" => $state_id,
            "country_id" => $country_id,
            "type" => $type,
            "is_default" => $is_default,
            "uid" => $uid,
            "addid" => $addid
        ]);

        $_SESSION["success_message"] = "Address updated.";
        header("Location: address.php");
        exit;
    }
}

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Address | ByteMeTech</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<?php include "../includes/navbar.php"; ?>
<div class="container bg-dark">
    <h1 class="white-text">Edit Address</h1>
</div>
<div class="container">
    <?php if ($errors): ?>
        <div class="alert"><?= implode("<br>", $errors) ?></div>
    <?php endif; ?>

    <form method="post" class="login-form">
        <label>Unit Number</label>
        <input type="text" name="unit" class="input-field" value="<?= htmlspecialchars($address["UNIT_NUMBER"]) ?>">

        <label>Street</label>
        <input type="text" name="street" class="input-field" value="<?= htmlspecialchars($address["STREET"]) ?>">

        <label>City</label>
        <input type="text" name="city" class="input-field" value="<?= htmlspecialchars($address["CITY"]) ?>">

        <label>Country</label>
        <select name="country_id" id="country-select" class="input-field">
            <option value="">-- Select Country --</option>
            <?php foreach ($countries as $c): ?>
                <option value="<?= $c["COUNTRY_ID"] ?>" <?= $c["COUNTRY_ID"] == $address["COUNTRY_ID"] ? "selected" : "" ?>>
                    <?= htmlspecialchars($c["COUNTRY_NAME"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>State</label>
        <select name="state_id" id="state-select" class="input-field">
            <option value="">-- Select State --</option>
            <?php foreach ($states as $s): ?>
                <option value="<?= $s["STATE_ID"] ?>" 
                        data-country="<?= $s["COUNTRY_ID"] ?>" 
                        <?= $s["STATE_ID"] == $address["STATE_ID"] ? "selected" : "" ?>>
                    <?= htmlspecialchars($s["STATE_NAME"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Address Type</label>
        <select name="type" class="input-field">
            <option value="Delivery" <?= $address["TYPE"] === "Delivery" ? "selected" : "" ?>>Delivery</option>
            <option value="Billing" <?= $address["TYPE"] === "Billing" ? "selected" : "" ?>>Billing</option>
        </select>

        <label>
            <input type="checkbox" name="default_address" <?= $address["DEFAULT_ADDRESS"] ? "checked" : "" ?>>
            Set as Default
        </label>

        <button type="submit" class="btn btn-blue">Update Address</button>
        <a href="address.php" class="btn btn-grey">Cancel</a>
    </form>
</div>

<script>
    function filterStates() {
        const selectedCountry = $("#country-select").val();
        $("#state-select option").each(function () {
            const countryId = $(this).data("country");
            if (!countryId || countryId == selectedCountry || $(this).val() === "") {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        const currentState = $("#state-select option:selected").data("country");
        if (currentState !== parseInt(selectedCountry)) {
            $("#state-select").val('');
        }
    }

    $(document).ready(function () {
        filterStates();
        $("#country-select").on("change", filterStates);
    });
</script>

<?php include "../includes/footer.php"; ?>
</body>
</html>

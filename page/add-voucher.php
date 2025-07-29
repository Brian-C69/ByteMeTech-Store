<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("location: ../page/login.php");
    exit;
}

$voucher = [
    "CODE" => "",
    "DISCOUNT_TYPE" => "",
    "DISCOUNT_VALUE" => "",
    "EXPIRY_DATE" => "",
    "USAGE_LIMIT" => "",
    "STATUS" => ""
];

$_err = [];

if (is_post()) {
    foreach ($voucher as $key => &$val) {
        $val = trim($_POST[$key] ?? '');
    }

    // Validation
    if ($voucher["CODE"] === "") $_err["CODE"] = "Required.";
    if (!in_array($voucher["DISCOUNT_TYPE"], ["percent", "fixed"])) $_err["DISCOUNT_TYPE"] = "Invalid type.";
    if (!is_numeric($voucher["DISCOUNT_VALUE"])) $_err["DISCOUNT_VALUE"] = "Enter a valid discount value.";
    if ($voucher["STATUS"] === "") $_err["STATUS"] = "Required.";

    if (!$_err) {
        $stmt = $pdo->prepare("INSERT INTO vouchers (CODE, DISCOUNT_TYPE, DISCOUNT_VALUE, EXPIRY_DATE, USAGE_LIMIT, STATUS)
                                VALUES (:code, :type, :value, :expiry, :limit, :status)");

        $stmt->execute([
            "code" => $voucher["CODE"],
            "type" => $voucher["DISCOUNT_TYPE"],
            "value" => $voucher["DISCOUNT_VALUE"],
            "expiry" => $voucher["EXPIRY_DATE"] ?: null,
            "limit" => $voucher["USAGE_LIMIT"] ?: null,
            "status" => $voucher["STATUS"]
        ]);

        temp("info", "Voucher added successfully.");
        header("location: voucher.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Voucher | ByteMeTech.com</title>
    <?php include '../includes/headers.php'; ?>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>

<div class="container">
    <h1>Add Voucher</h1>
    <form method="post" class="login-form">
        <label for="CODE">Voucher Code</label>
        <?= html_text("CODE", '', $voucher["CODE"]) ?>
        <?= err("CODE") ?>

        <label for="DISCOUNT_TYPE">Discount Type</label>
        <?= html_select("DISCOUNT_TYPE", ["percent" => "Percentage", "fixed" => "Fixed"], "- Select -", $voucher["DISCOUNT_TYPE"]) ?>
        <?= err("DISCOUNT_TYPE") ?>

        <label for="DISCOUNT_VALUE">Discount Value</label>
        <?= html_text("DISCOUNT_VALUE", '', $voucher["DISCOUNT_VALUE"]) ?>
        <?= err("DISCOUNT_VALUE") ?>

        <label for="EXPIRY_DATE">Expiry Date</label>
        <?= html_date("EXPIRY_DATE", 'type="date"', $voucher["EXPIRY_DATE"]) ?>
        <?= err("EXPIRY_DATE") ?>

        <label for="USAGE_LIMIT">Usage Limit</label>
        <?= html_text("USAGE_LIMIT", '', $voucher["USAGE_LIMIT"]) ?>

        <label for="STATUS">Status</label>
        <?= html_select("STATUS", ["active" => "Active", "inactive" => "Inactive"], "- Select -", $voucher["STATUS"]) ?>
        <?= err("STATUS") ?>

        <div class="full-width">
            <button type="submit" class="btn btn-green">Add Voucher</button>
            <a href="voucher.php" class="btn btn-blue">Back</a>
        </div>
    </form>
</div>

</body>
</html>

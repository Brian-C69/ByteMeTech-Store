<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("location: ../page/login.php");
    exit;
}

$vid = (int) ($_GET["id"] ?? 0);
if (!$vid) {
    header("location: voucher.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM vouchers WHERE VID = :vid");
$stmt->execute(["vid" => $vid]);
$voucher = $stmt->fetch();

if (!$voucher) {
    $_SESSION["error_message"] = "Voucher not found.";
    header("location: voucher.php");
    exit;
}

$_err = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $voucher["CODE"] = trim($_POST["CODE"] ?? "");
    $voucher["DISCOUNT_TYPE"] = $_POST["DISCOUNT_TYPE"] ?? "percent";
    $voucher["DISCOUNT_VALUE"] = (float) ($_POST["DISCOUNT_VALUE"] ?? 0);
    $voucher["EXPIRY_DATE"] = $_POST["EXPIRY_DATE"] ?? null;
    $voucher["USAGE_LIMIT"] = $_POST["USAGE_LIMIT"] !== '' ? (int) $_POST["USAGE_LIMIT"] : null;
    $voucher["STATUS"] = $_POST["STATUS"] ?? "active";

    if ($voucher["CODE"] === "") $_err["CODE"] = "Code is required.";
    if (!in_array($voucher["DISCOUNT_TYPE"], ["percent", "fixed"])) $_err["DISCOUNT_TYPE"] = "Invalid type.";
    if ($voucher["DISCOUNT_VALUE"] <= 0) $_err["DISCOUNT_VALUE"] = "Must be greater than 0.";

    if (empty($_err)) {
        $stmt = $pdo->prepare("UPDATE vouchers SET
            CODE = :code,
            DISCOUNT_TYPE = :type,
            DISCOUNT_VALUE = :value,
            EXPIRY_DATE = :expiry,
            USAGE_LIMIT = :limit,
            STATUS = :status
            WHERE VID = :vid");

        $stmt->execute([
            "code" => $voucher["CODE"],
            "type" => $voucher["DISCOUNT_TYPE"],
            "value" => $voucher["DISCOUNT_VALUE"],
            "expiry" => $voucher["EXPIRY_DATE"] ?: null,
            "limit" => $voucher["USAGE_LIMIT"],
            "status" => $voucher["STATUS"],
            "vid" => $vid
        ]);

        $_SESSION["success_message"] = "Voucher updated successfully.";
        header("location: voucher.php");
        exit;
    }
}

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Voucher | ByteMeTech</title>
    <?php include '../includes/headers.php'; ?>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>

<div class="container">
    <h1>Edit Voucher</h1>
    <form method="post" class="login-form">

        <label for="CODE">Voucher Code</label>
        <input type="text" name="CODE" class="input-field" value="<?= htmlspecialchars($voucher['CODE']) ?>">
        <?= err("CODE") ?>

        <label for="DISCOUNT_TYPE">Discount Type</label>
        <select class="input-field" name="DISCOUNT_TYPE">
            <option value="percent" <?= $voucher['DISCOUNT_TYPE'] === 'percent' ? 'selected' : '' ?>>Percentage (%)</option>
            <option value="fixed" <?= $voucher['DISCOUNT_TYPE'] === 'fixed' ? 'selected' : '' ?>>Fixed (RM)</option>
        </select>

        <label for="DISCOUNT_VALUE">Discount Value</label>
        <input type="number" step="0.01" class="input-field" name="DISCOUNT_VALUE" value="<?= htmlspecialchars($voucher['DISCOUNT_VALUE']) ?>">
        <?= err("DISCOUNT_VALUE") ?>

        <label for="EXPIRY_DATE">Expiry Date</label>
        <input type="date" name="EXPIRY_DATE" class="input-field" value="<?= htmlspecialchars($voucher['EXPIRY_DATE']) ?>">

        <label for="USAGE_LIMIT">Usage Limit (optional)</label>
        <input type="number" class="input-field" name="USAGE_LIMIT" value="<?= htmlspecialchars($voucher['USAGE_LIMIT']) ?>">

        <label for="STATUS">Status</label>
        <select class="input-field" name="STATUS">
            <option value="active" <?= $voucher['STATUS'] === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $voucher['STATUS'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>

        <div class="full-width">
            <button type="submit" class="btn btn-green">Update Voucher</button>
            <a href="voucher.php" class="btn btn-blue">Back</a>
        </div>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>

<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$code = trim($_POST["voucher_code"] ?? "");

if ($code === "") {
    $_SESSION["error_message"] = "Please enter a voucher code.";
    header("Location: checkout.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM vouchers WHERE CODE = :code AND STATUS = 'active'");
$stmt->execute(["code" => $code]);
$voucher = $stmt->fetch();

if (!$voucher) {
    $_SESSION["error_message"] = "Invalid or inactive voucher code.";
    header("Location: checkout.php");
    exit;
}

// Check expiry date
if (!empty($voucher["EXPIRY_DATE"]) && strtotime($voucher["EXPIRY_DATE"]) < time()) {
    $_SESSION["error_message"] = "This voucher has expired.";
    header("Location: checkout.php");
    exit;
}

// Check usage limit
if (!empty($voucher["USAGE_LIMIT"]) && $voucher["USED_COUNT"] >= $voucher["USAGE_LIMIT"]) {
    $_SESSION["error_message"] = "This voucher has reached its usage limit.";
    header("Location: checkout.php");
    exit;
}

// Fetch user's cart items
$uid = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT c.*, p.PRODUCT_PRICE_SALE, p.PRODUCT_PRICE_REGULAR 
                       FROM cart c JOIN products p ON c.PID = p.PID 
                       WHERE c.UID = :uid");
$stmt->execute(["uid" => $uid]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    $_SESSION["error_message"] = "Your cart is empty.";
    header("Location: cart.php");
    exit;
}

// Calculate cart total
$total = 0;
foreach ($cart_items as $item) {
    $sale_price = (float) $item["PRODUCT_PRICE_SALE"];
    $reg_price = (float) $item["PRODUCT_PRICE_REGULAR"];
    $price = $sale_price > 0 ? $sale_price : $reg_price;

    if ($price <= 0) continue; // Skip invalid priced items

    $total += $price * $item["QUANTITY"];
}

// Calculate discount
$discount = 0;
if ($voucher["DISCOUNT_TYPE"] === "percent") {
    $percent = (float) $voucher["DISCOUNT_VALUE"];
    if ($percent > 0 && $percent <= 100) {
        $discount = $total * ($percent / 100);
    }
} else {
    $discount = (float) $voucher["DISCOUNT_VALUE"];
}

$discount = min($discount, $total); // Clamp to max total

// Store in session
$_SESSION["voucher"] = $voucher;
$_SESSION["voucher_code"] = $code;
$_SESSION["voucher_discount"] = round($discount, 2);

$_SESSION["success_message"] = "Voucher applied successfully.";
header("Location: checkout.php");
exit;

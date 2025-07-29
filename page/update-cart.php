<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

header("Content-Type: application/json");

$pid = (int) ($_POST["pid"] ?? 0);
$qty = (int) ($_POST["qty"] ?? 1);

if ($pid < 1 || $qty < 1) {
    echo json_encode(["success" => false, "error" => "Invalid input"]);
    exit;
}

$is_logged_in = isset($_SESSION["user_id"]);
$total_price = 0;

if ($is_logged_in) {
    $uid = $_SESSION["user_id"];

    // Check if item exists in cart
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE UID = :uid AND PID = :pid");
    $stmt->execute(["uid" => $uid, "pid" => $pid]);
    $exists = $stmt->fetch();

    if ($exists) {
        $stmt = $pdo->prepare("UPDATE cart SET QUANTITY = :qty WHERE UID = :uid AND PID = :pid");
        $stmt->execute(["qty" => $qty, "uid" => $uid, "pid" => $pid]);
    }

    // Recalculate total cart price
    $stmt = $pdo->prepare("
        SELECT c.QUANTITY, p.PRODUCT_PRICE_SALE, p.PRODUCT_PRICE_REGULAR
        FROM cart c
        JOIN products p ON c.PID = p.PID
        WHERE c.UID = :uid
    ");
    $stmt->execute(["uid" => $uid]);

    foreach ($stmt->fetchAll() as $item) {
        $item_qty = $item["QUANTITY"] ?? $item["quantity"] ?? 1;
        $price = $item["PRODUCT_PRICE_SALE"] > 0 ? $item["PRODUCT_PRICE_SALE"] : $item["PRODUCT_PRICE_REGULAR"];
        $total_price += $price * $item_qty;
    }

} else {
    // Guest user
    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = [];
    }

    $_SESSION["cart"][$pid] = $qty;

    $session_cart = $_SESSION["cart"];
    $placeholders = implode(',', array_fill(0, count($session_cart), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE PID IN ($placeholders)");
    $stmt->execute(array_keys($session_cart));
    $products = $stmt->fetchAll(PDO::FETCH_UNIQUE);

    foreach ($session_cart as $id => $quantity) {
        if (isset($products[$id])) {
            $price = $products[$id]["PRODUCT_PRICE_SALE"] > 0 ? $products[$id]["PRODUCT_PRICE_SALE"] : $products[$id]["PRODUCT_PRICE_REGULAR"];
            $total_price += $price * $quantity;
        }
    }
}

// Fetch current product price for updated subtotal
$stmt = $pdo->prepare("SELECT PRODUCT_PRICE_SALE, PRODUCT_PRICE_REGULAR FROM products WHERE PID = :pid");
$stmt->execute(["pid" => $pid]);
$product = $stmt->fetch();

$price = $product["PRODUCT_PRICE_SALE"] > 0 ? $product["PRODUCT_PRICE_SALE"] : $product["PRODUCT_PRICE_REGULAR"];
$subtotal = $price * $qty;

echo json_encode([
    "success" => true,
    "subtotal" => $subtotal,
    "total" => $total_price,
    "qty" => $qty // return updated quantity for UI sync
]);

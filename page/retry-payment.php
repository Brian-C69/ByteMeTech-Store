<?php
require_once "../includes/base.php";
require_once "../includes/config.php";
require_once "../vendor/autoload.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["user_id"];
$order_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($order_id <= 0) {
    $_SESSION["error_message"] = "Invalid order ID.";
    header("Location: orders.php");
    exit;
}

// Fetch the order
$stmt = $pdo->prepare("
    SELECT o.*
    FROM orders o
    WHERE o.ORDER_ID = :oid AND o.UID = :uid AND o.PAYMENT_STATUS = 'Unpaid'
");
$stmt->execute([
    "oid" => $order_id,
    "uid" => $uid
]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION["error_message"] = "Invalid or already-paid order.";
    header("Location: orders.php");
    exit;
}

// Amount in cents (Stripe requires integer)
$amount_cents = (int)round($order["TOTAL_AMOUNT"] * 100);

// Stripe
\Stripe\Stripe::setApiKey("sk_test_51RGYd0RMeNadig1hs416j0flYul1L9o8jKOSXcefF8xFAZr8dIY1V0sjBHiT090xjd4r1XLItpPGhKvVQs6QUHBr00PbyNd60r");

try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'myr',
                'product_data' => ['name' => 'Retry Payment for Order #' . $order_id],
                'unit_amount' => $amount_cents,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://bernard.onthewifi.com/ByteMeTech/page/payment-status.php?status=success&session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://bernard.onthewifi.com/ByteMeTech/page/payment-status.php?status=cancel&session_id={CHECKOUT_SESSION_ID}',
        'metadata' => [
            'order_id' => $order_id,
            'user_id' => $uid,
        ]
    ]);

    header("Location: " . $session->url);
    exit;

} catch (Exception $e) {
    $_SESSION["error_message"] = "Stripe Error: " . $e->getMessage();
    header("Location: orders.php");
    exit;
}

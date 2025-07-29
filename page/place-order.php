<?php
require_once "../includes/base.php";
require_once "../includes/config.php";
require_once "../vendor/autoload.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["user_id"];
$address_id = (int) ($_POST["address_id"] ?? 0);
$grand_total = (float) ($_POST["grand_total"] ?? 0);

if ($address_id <= 0) {
    $_SESSION["error_message"] = "Please select a valid delivery address.";
    header("Location: checkout.php");
    exit;
}

$is_free_order = ($grand_total <= 0);

// Determine source: Buy Now or Cart
$cart_items = [];
if (isset($_SESSION["buy_now"])) {
    $pid = (int) $_SESSION["buy_now"]["pid"];
    $qty = (int) $_SESSION["buy_now"]["qty"];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE PID = :pid");
    $stmt->execute(["pid" => $pid]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION["error_message"] = "Product not found.";
        header("Location: product.php");
        exit;
    }

    $price = $product["PRODUCT_PRICE_SALE"] > 0 ? $product["PRODUCT_PRICE_SALE"] : $product["PRODUCT_PRICE_REGULAR"];
    $cart_items[] = [
        "PID" => $pid,
        "PRODUCT_NAME" => $product["PRODUCT_NAME"],
        "QUANTITY" => $qty,
        "UNIT_PRICE" => $price,
        "SUBTOTAL" => $price * $qty
    ];
} else {
    $stmt = $pdo->prepare("
        SELECT c.*, p.PRODUCT_NAME, p.PRODUCT_PRICE_SALE, p.PRODUCT_PRICE_REGULAR
        FROM cart c
        JOIN products p ON c.PID = p.PID
        WHERE c.UID = :uid
    ");
    $stmt->execute(["uid" => $uid]);
    $cart_items = $stmt->fetchAll();

    if (empty($cart_items)) {
        $_SESSION["error_message"] = "Your cart is empty.";
        header("Location: cart.php");
        exit;
    }
}

// Voucher metadata
$voucher_code = $_SESSION["voucher_code"] ?? null;
$discount = $_SESSION["voucher_discount"] ?? 0;
$voucher_id = $_SESSION["voucher"]["VID"] ?? null;

$pdo->beginTransaction();

try {
    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO orders (UID, TOTAL_AMOUNT, STATUS, PAYMENT_METHOD, PAYMENT_STATUS, BILLING_ADDRESS_ID, SHIPPING_ADDRESS_ID)
        VALUES (:uid, :total, :status, :method, :pay_status, :addr, :addr)
    ");
    $stmt->execute([
        "uid" => $uid,
        "total" => $grand_total,
        "status" => $is_free_order ? "Processing" : "Pending",
        "method" => $is_free_order ? "N/A" : "Credit Card",
        "pay_status" => $is_free_order ? "Paid" : "Unpaid",
        "addr" => $address_id
    ]);

    $order_id = $pdo->lastInsertId();

    // Insert order items
    $item_stmt = $pdo->prepare("
        INSERT INTO order_items (ORDER_ID, PID, QUANTITY, UNIT_PRICE, SUBTOTAL)
        VALUES (:oid, :pid, :qty, :unit, :sub)
    ");

    foreach ($cart_items as $item) {
        $price = $item["UNIT_PRICE"] ?? ($item["PRODUCT_PRICE_SALE"] > 0 ? $item["PRODUCT_PRICE_SALE"] : $item["PRODUCT_PRICE_REGULAR"]);
        $qty = $item["QUANTITY"];
        $subtotal = $price * $qty;

        $item_stmt->execute([
            "oid" => $order_id,
            "pid" => $item["PID"],
            "qty" => $qty,
            "unit" => $price,
            "sub" => $subtotal
        ]);
    }

    // Update voucher usage count if applicable
    if ($voucher_id) {
        $update = $pdo->prepare("UPDATE vouchers SET USED_COUNT = USED_COUNT + 1 WHERE VID = :vid");
        $update->execute(["vid" => $voucher_id]);
    }

    $pdo->commit();

    // Clear cart/buy-now and voucher session
    unset($_SESSION["voucher_code"], $_SESSION["voucher_discount"], $_SESSION["voucher"]);

    if (isset($_SESSION["buy_now"])) {
        unset($_SESSION["buy_now"]);
    } else {
        $pdo->prepare("DELETE FROM cart WHERE UID = :uid")->execute(["uid" => $uid]);
    }

    // Free order? Skip payment
    if ($is_free_order) {
        $_SESSION["success_message"] = "Order placed successfully without payment.";
        header("Location: orders.php");
        exit;
    }

    // Stripe payment
    \Stripe\Stripe::setApiKey("sk_test_51RGYd0RMeNadig1hs416j0flYul1L9o8jKOSXcefF8xFAZr8dIY1V0sjBHiT090xjd4r1XLItpPGhKvVQs6QUHBr00PbyNd60r");

    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'myr',
                'product_data' => ['name' => 'ByteMeTech Order #' . $order_id],
                'unit_amount' => (int) round($grand_total * 100),
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://bernard.onthewifi.com/ByteMeTech/page/payment-status.php?status=success&session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://bernard.onthewifi.com/ByteMeTech/page/payment-status.php?status=cancel&session_id={CHECKOUT_SESSION_ID}',
        'metadata' => [
            'order_id' => $order_id,
            'user_id' => $uid,
            'voucher_code' => $voucher_code ?? '',
            'discount' => $discount,
        ]
    ]);
    $_SESSION["order_id"] = $order_id;
    header("Location: " . $session->url);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION["error_message"] = "Error placing order: " . $e->getMessage();
    header("Location: checkout.php");
    exit;
}
?>

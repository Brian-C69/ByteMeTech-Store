<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"])) {
    $_SESSION["error_message"] = "Please log in to continue.";
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["user_id"];

// Fetch user's saved addresses
$stmt = $pdo->prepare("
    SELECT a.*, s.STATE_NAME, c.COUNTRY_NAME
    FROM Address a
    JOIN States s ON a.STATE_ID = s.STATE_ID
    JOIN Countries c ON a.COUNTRY_ID = c.COUNTRY_ID
    WHERE a.UID = :uid
");
$stmt->execute(["uid" => $uid]);
$addresses = $stmt->fetchAll();

// Determine which cart items to show
$cart_items = [];
$total = 0;

if (isset($_SESSION["buy_now"])) {
    // Buy Now scenario
    $pid = (int) $_SESSION["buy_now"]["pid"];
    $qty = (int) $_SESSION["buy_now"]["qty"];

    $stmt = $pdo->prepare("SELECT * FROM products WHERE PID = :pid");
    $stmt->execute(["pid" => $pid]);
    $product = $stmt->fetch();

    if ($product) {
        $product["QUANTITY"] = $qty;
        $cart_items[] = $product;
    }

} elseif (isset($_SESSION["checkout_items"])) {
    // Checkout Selected scenario
    $selected_ids = array_map("intval", $_SESSION["checkout_items"]);
    $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));

    $stmt = $pdo->prepare("
        SELECT c.*, p.PRODUCT_NAME, p.PRODUCT_PRICE_SALE, p.PRODUCT_PRICE_REGULAR
        FROM cart c
        JOIN products p ON c.PID = p.PID
        WHERE c.UID = ? AND c.PID IN ($placeholders)
    ");
    $stmt->execute(array_merge([$uid], $selected_ids));
    $cart_items = $stmt->fetchAll();

} else {
    // Full cart checkout
    $stmt = $pdo->prepare("
        SELECT c.*, p.PRODUCT_NAME, p.PRODUCT_PRICE_SALE, p.PRODUCT_PRICE_REGULAR
        FROM cart c
        JOIN products p ON c.PID = p.PID
        WHERE c.UID = :uid
    ");
    $stmt->execute(["uid" => $uid]);
    $cart_items = $stmt->fetchAll();
}

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout | ByteMeTech</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #f9f9f9;
        }
        .summary {
            text-align: right;
            margin-top: 20px;
            font-size: 1.1em;
        }
        .voucher-box {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .voucher-box input[type="text"] {
            flex: 1;
        }
    </style>
</head>
<body class="bg-light">
<?php include "../includes/navbar.php"; ?>
<div class="container bg-dark">
    <h1 class="white-text">Checkout</h1>
</div>
<div class="container">
    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert-success">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <h2>Shipping Address</h2>
    <form method="post" action="place-order.php">
        <select name="address_id" class="input-field" required>
            <?php foreach ($addresses as $addr): ?>
                <option value="<?= $addr["ADDID"] ?>">
                    <?= htmlspecialchars("{$addr["UNIT_NUMBER"]}, {$addr["STREET"]}, {$addr["CITY"]}, {$addr["STATE_NAME"]}, {$addr["COUNTRY_NAME"]}") ?>
                </option>
            <?php endforeach; ?>
        </select>

        <h2>Order Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $total = 0;
            foreach ($cart_items as $item):
                $price = $item["PRODUCT_PRICE_SALE"] > 0 ? $item["PRODUCT_PRICE_SALE"] : $item["PRODUCT_PRICE_REGULAR"];
                $subtotal = $price * $item["QUANTITY"];
                $total += $subtotal;
            ?>
                <tr>
                    <td><?= htmlspecialchars($item["PRODUCT_NAME"]) ?></td>
                    <td><?= $item["QUANTITY"] ?></td>
                    <td>RM<?= number_format($price, 2) ?></td>
                    <td>RM<?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="voucher-box">
            <?php if (!isset($_SESSION["voucher_code"])): ?>
                <input type="text" name="voucher_code" class="input-field" placeholder="Enter voucher code">
                <button type="submit" formaction="validate-voucher.php" class="btn btn-blue">Apply Voucher</button>
            <?php else: ?>
                <p>Voucher <strong><?= htmlspecialchars($_SESSION["voucher_code"]) ?></strong> applied. Discount: <strong>RM<?= number_format($_SESSION["voucher_discount"], 2) ?></strong></p>
                <a href="cancel-voucher.php" class="btn btn-red">Cancel Voucher</a>
            <?php endif; ?>
        </div>

        <div class="summary">
            <p>Subtotal: RM<?= number_format($total, 2) ?></p>
            <?php if (isset($_SESSION["voucher_discount"])): ?>
                <p>Discount: RM<?= number_format($_SESSION["voucher_discount"], 2) ?></p>
                <p><strong>Grand Total: RM<?= number_format($total - $_SESSION["voucher_discount"], 2) ?></strong></p>
                <input type="hidden" name="grand_total" value="<?= number_format($total - $_SESSION["voucher_discount"], 2, '.', '') ?>">
            <?php else: ?>
                <p><strong>Grand Total: RM<?= number_format($total, 2) ?></strong></p>
                <input type="hidden" name="grand_total" value="<?= number_format($total, 2, '.', '') ?>">
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-green">Place Order</button>
    </form>
</div>
<?php include "../includes/footer.php"; ?>
</body>
</html>

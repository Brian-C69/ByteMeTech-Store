<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["user_id"];

// Cancel order logic
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cancel_order"])) {
    $cancel_id = (int) $_POST["cancel_order"];
    $stmt = $pdo->prepare("UPDATE orders SET STATUS = 'Cancelled' WHERE ORDER_ID = :oid AND UID = :uid");
    $stmt->execute(["oid" => $cancel_id, "uid" => $uid]);
    $_SESSION["success_message"] = "Order #$cancel_id has been cancelled.";
    header("Location: orders.php");
    exit;
}

// Fetch orders
$stmt = $pdo->prepare("
    SELECT o.*, 
           CONCAT(b.UNIT_NUMBER, ', ', b.STREET, ', ', b.CITY, ', ', sb.STATE_NAME, ', ', cb.COUNTRY_NAME) AS billing_address,
           CONCAT(s.UNIT_NUMBER, ', ', s.STREET, ', ', s.CITY, ', ', ss.STATE_NAME, ', ', cs.COUNTRY_NAME) AS shipping_address
    FROM orders o
    JOIN Address b ON o.BILLING_ADDRESS_ID = b.ADDID
    JOIN States sb ON b.STATE_ID = sb.STATE_ID
    JOIN Countries cb ON b.COUNTRY_ID = cb.COUNTRY_ID
    JOIN Address s ON o.SHIPPING_ADDRESS_ID = s.ADDID
    JOIN States ss ON s.STATE_ID = ss.STATE_ID
    JOIN Countries cs ON s.COUNTRY_ID = cs.COUNTRY_ID
    WHERE o.UID = :uid
    ORDER BY o.CREATED_AT DESC
");
$stmt->execute(["uid" => $uid]);
$orders = $stmt->fetchAll();

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Orders | ByteMeTech</title>
    <style>
        .order-box {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .order-summary {
            display: flex;
            justify-content: space-between;
            cursor: pointer;
        }
        .order-details {
            display: none;
            margin-top: 10px;
        }
        .order-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-details th, .order-details td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .order-details th {
            background-color: #f9f9f9;
        }
    </style>
    <script>
        function toggleDetails(id) {
            const details = document.getElementById("details-" + id);
            details.style.display = details.style.display === "none" ? "block" : "none";
        }
    </script>
</head>
<body class="bg-light">
<?php include "../includes/navbar.php"; ?>
<div class="container bg-dark">
    <h1 class="white-text">My Orders</h1>
</div>
<div class="container">
    <?php if (isset($_SESSION["success_message"])): ?>
        <div class="alert-success"><?= htmlspecialchars($_SESSION["success_message"]) ?></div>
        <?php unset($_SESSION["success_message"]); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION["error_message"])): ?>
        <div class="alert"><?= htmlspecialchars($_SESSION["error_message"]) ?></div>
        <?php unset($_SESSION["error_message"]); ?>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <p>You have no orders yet.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-box">
                <div class="order-summary" onclick="toggleDetails(<?= $order['ORDER_ID'] ?>)">
                    <div><strong>Order #<?= $order['ORDER_ID'] ?></strong> - <?= htmlspecialchars($order["STATUS"]) ?> (<?= htmlspecialchars($order["PAYMENT_STATUS"]) ?>)</div>
                    <div><strong>RM<?= number_format($order["TOTAL_AMOUNT"], 2) ?></strong></div>
                </div>
                <div class="order-details" id="details-<?= $order['ORDER_ID'] ?>">
                    <p><strong>Placed on:</strong> <?= $order["CREATED_AT"] ?></p>
                    <p><strong>Payment Method:</strong> <?= htmlspecialchars($order["PAYMENT_METHOD"]) ?></p>
                    <p><strong>Billing Address:</strong> <?= htmlspecialchars($order["billing_address"]) ?></p>
                    <p><strong>Shipping Address:</strong> <?= htmlspecialchars($order["shipping_address"]) ?></p>

                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $items_stmt = $pdo->prepare("
                            SELECT oi.*, p.PRODUCT_NAME
                            FROM order_items oi
                            JOIN products p ON oi.PID = p.PID
                            WHERE oi.ORDER_ID = :oid
                        ");
                        $items_stmt->execute(["oid" => $order["ORDER_ID"]]);
                        $items = $items_stmt->fetchAll();
                        foreach ($items as $item):
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($item["PRODUCT_NAME"]) ?></td>
                                <td><?= $item["QUANTITY"] ?></td>
                                <td>RM<?= number_format($item["UNIT_PRICE"], 2) ?></td>
                                <td>RM<?= number_format($item["SUBTOTAL"], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div style="margin-top: 10px; display: flex; justify-content: flex-end; gap: 10px;">
                        <a href="generate-receipt.php?id=<?= $order['ORDER_ID'] ?>" target="_blank" class="btn btn-blue">View Receipt (PDF)</a>
                        <form method="post" action="send-receipt.php" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?= $order['ORDER_ID'] ?>">
                            <button type="submit" class="btn btn-green">Send to Email</button>
                        </form>
                        <?php if (!in_array($order["STATUS"], ["Cancelled", "Delivered", "Shipped", "Refunded"])): ?>
                            <form method="post" action="orders.php" style="display:inline;" onsubmit="return confirm('Cancel this order?');">
                                <input type="hidden" name="cancel_order" value="<?= $order["ORDER_ID"] ?>">
                                <button type="submit" class="btn btn-red">Cancel Order</button>
                            </form>
                            <?php if ($order["PAYMENT_STATUS"] === "Unpaid"): ?>
                                <a href="retry-payment.php?id=<?= $order["ORDER_ID"] ?>" class="btn btn-yellow">Pay Now</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include "../includes/footer.php"; ?>
</body>
</html>

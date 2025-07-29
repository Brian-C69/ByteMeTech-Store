<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

$order_id = (int) ($_GET['id'] ?? 0);
if ($order_id <= 0) {
    $_SESSION['error_message'] = "Invalid order ID.";
    header("Location: orders-manage.php");
    exit;
}

// Fetch order
$stmt = $pdo->prepare("SELECT o.*, u.USERNAME, u.EMAIL FROM orders o JOIN users u ON o.UID = u.UID WHERE o.ORDER_ID = :oid");
$stmt->execute(["oid" => $order_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error_message'] = "Order not found.";
    header("Location: orders-manage.php");
    exit;
}

// Fetch items
$items_stmt = $pdo->prepare("SELECT oi.*, p.PRODUCT_NAME FROM order_items oi JOIN products p ON oi.PID = p.PID WHERE oi.ORDER_ID = :oid");
$items_stmt->execute(["oid" => $order_id]);
$order_items = $items_stmt->fetchAll();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['STATUS'] ?? 'Pending';
    $payment_status = $_POST['PAYMENT_STATUS'] ?? 'Unpaid';

    $update = $pdo->prepare("UPDATE orders SET STATUS = :status, PAYMENT_STATUS = :pay_status WHERE ORDER_ID = :oid");
    $update->execute([
        'status' => $status,
        'pay_status' => $payment_status,
        'oid' => $order_id
    ]);

    $_SESSION['success_message'] = "Order updated.";
    header("Location: view-order.php?id=$order_id");
    exit;
}

include "../includes/headers.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Order | Admin</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th, table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        table th { background-color: #f8f8f8; }
        .order-meta { margin-top: 20px; }
        .form-inline { display: flex; gap: 10px; align-items: center; margin-top: 10px; }
    </style>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>
<div class="container">
    <h1>Order #<?= $order_id ?></h1>

    <?php if (isset($_SESSION["success_message"])): ?>
        <div class="alert-success"> <?= htmlspecialchars($_SESSION["success_message"]) ?> </div>
        <?php unset($_SESSION["success_message"]); ?>
    <?php endif; ?>

    <div class="order-meta">
        <p><strong>User:</strong> <?= htmlspecialchars($order['USERNAME']) ?> (<?= htmlspecialchars($order['EMAIL']) ?>)</p>
        <p><strong>Status:</strong> <?= htmlspecialchars($order['STATUS']) ?></p>
        <p><strong>Payment:</strong> <?= htmlspecialchars($order['PAYMENT_METHOD']) ?> | <?= htmlspecialchars($order['PAYMENT_STATUS']) ?></p>
        <p><strong>Total:</strong> RM<?= number_format($order['TOTAL_AMOUNT'], 2) ?></p>
        <p><strong>Created At:</strong> <?= $order['CREATED_AT'] ?></p>
    </div>

    <form method="post" class="form-inline">
        <label>Status:</label>
        <select name="STATUS" class="input-field">
            <?php foreach (["Pending", "Processing", "Shipped", "Delivered", "Cancelled", "Refunded"] as $status): ?>
                <option value="<?= $status ?>" <?= $status === $order['STATUS'] ? 'selected' : '' ?>><?= $status ?></option>
            <?php endforeach; ?>
        </select>

        <label>Payment:</label>
        <select name="PAYMENT_STATUS" class="input-field">
            <?php foreach (["Paid", "Unpaid", "Refunded"] as $ps): ?>
                <option value="<?= $ps ?>" <?= $ps === $order['PAYMENT_STATUS'] ? 'selected' : '' ?>><?= $ps ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-green">Update</button>
    </form>

    <h2>Items</h2>
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
            <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['PRODUCT_NAME']) ?></td>
                    <td><?= $item['QUANTITY'] ?></td>
                    <td>RM<?= number_format($item['UNIT_PRICE'], 2) ?></td>
                    <td>RM<?= number_format($item['SUBTOTAL'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="orders-manage.php" class="btn btn-blue" style="margin-top:20px;">Back to Order List</a>
</div>
</body>
</html>
<?php
require_once "../includes/base.php";
require_once "../includes/config.php";
require_once "../vendor/autoload.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["user_id"];
$status = $_GET['status'] ?? null;
$session_id = $_GET['session_id'] ?? null;

if (!$status || !$session_id) {
    $_SESSION["error_message"] = "Invalid payment session.";
    header("Location: checkout.php");
    exit;
}

\Stripe\Stripe::setApiKey("sk_test_51RGYd0RMeNadig1hs416j0flYul1L9o8jKOSXcefF8xFAZr8dIY1V0sjBHiT090xjd4r1XLItpPGhKvVQs6QUHBr00PbyNd60r"); // Replace with your test secret key

try {
    $session = \Stripe\Checkout\Session::retrieve($session_id);
} catch (Exception $e) {
    $_SESSION["error_message"] = "Could not verify payment session.";
    header("Location: checkout.php");
    exit;
}

$order_id = $session->metadata->order_id ?? null;

if ($order_id) {
    if ($status === "success") {
        // Check if stock was already reduced
        $stmt = $pdo->prepare("SELECT PAYMENT_STATUS FROM orders WHERE ORDER_ID = :oid AND UID = :uid");
        $stmt->execute(["oid" => $order_id, "uid" => $uid]);
        $order = $stmt->fetch();

        if ($order && $order["PAYMENT_STATUS"] !== "Paid") {
            // Begin transaction
            $pdo->beginTransaction();
            try {
                // Fetch ordered items
                $stmt = $pdo->prepare("SELECT PID, QUANTITY FROM order_items WHERE ORDER_ID = :oid");
                $stmt->execute(["oid" => $order_id]);
                $items = $stmt->fetchAll();

                foreach ($items as $item) {
                    // Reduce stock from PRODUCT_QUANTITY
                    $stmt = $pdo->prepare("
                        UPDATE products 
                        SET PRODUCT_QUANTITY = PRODUCT_QUANTITY - :qty 
                        WHERE PID = :pid AND PRODUCT_QUANTITY >= :qty
                    ");
                    $stmt->execute([
                        "qty" => $item["QUANTITY"],
                        "pid" => $item["PID"]
                    ]);

                    if ($stmt->rowCount() === 0) {
                        throw new Exception("Insufficient stock for PID: " . $item["PID"]);
                    }
                }

                // Update order status to paid
                $stmt = $pdo->prepare("
                    UPDATE orders 
                    SET STATUS = 'Processing', PAYMENT_STATUS = 'Paid' 
                    WHERE ORDER_ID = :oid AND UID = :uid
                ");
                $stmt->execute(["oid" => $order_id, "uid" => $uid]);

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION["error_message"] = "Payment succeeded but stock update failed. Please contact support.";
                header("Location: checkout.php");
                exit;
            }
        }
    } elseif ($status === "cancel") {
        $stmt = $pdo->prepare("UPDATE orders SET STATUS = 'Pending', PAYMENT_STATUS = 'Unpaid' WHERE ORDER_ID = :oid AND UID = :uid");
        $stmt->execute(["oid" => $order_id, "uid" => $uid]);
    }
}


include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payment Status | ByteMeTech</title>
    <style>
        .status-box {
            max-width: 600px;
            margin: 60px auto;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .status-box h1 {
            margin-bottom: 10px;
        }
        .status-box p {
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body class="bg-light">
<?php include "../includes/navbar.php"; ?>

<div class="container">
    <div class="status-box">
        <?php if ($status === "success"): ?>
            <h1 style="color: green;">✅ Payment Successful</h1>
            <p>Your payment has been received. Your order is now being processed.</p>
        <?php else: ?>
            <h1 style="color: red;">❌ Payment Failed</h1>
            <p>Your order was created but payment failed. Please retry from your orders page.</p>
        <?php endif; ?>

        <a href="orders.php" class="btn">Go to My Orders</a>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>

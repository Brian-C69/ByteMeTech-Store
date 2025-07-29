<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Handle form submission (remove/checkout)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $selected = $_POST["selected_items"] ?? [];

    if (!is_array($selected)) $selected = [$selected];

    $selected = array_map("intval", $selected);

    if (!empty($action) && !empty($selected)) {
        if ($action === "remove") {
            if (isset($_SESSION["user_id"])) {
                $uid = $_SESSION["user_id"];
                $placeholders = implode(",", array_fill(0, count($selected), '?'));
                $stmt = $pdo->prepare("DELETE FROM cart WHERE UID = ? AND PID IN ($placeholders)");
                $stmt->execute(array_merge([$uid], $selected));
            } else {
                foreach ($selected as $pid) {
                    unset($_SESSION["cart"][$pid]);
                }
            }
            $_SESSION["success_message"] = "Selected items removed from cart.";
            header("Location: cart.php");
            exit;
        }

        if ($action === "checkout") {
            $_SESSION["checkout_items"] = $selected;
            header("Location: checkout.php");
            exit;
        }
    }
}

// Fetch cart data
$is_logged_in = isset($_SESSION["user_id"]);
$cart_items = [];
$total_price = 0.00;

if ($is_logged_in) {
    $uid = $_SESSION["user_id"];
    $stmt = $pdo->prepare("
        SELECT c.*, p.PRODUCT_NAME, p.PRODUCT_IMAGE_PATH, p.PRODUCT_PRICE_SALE, p.PRODUCT_PRICE_REGULAR
        FROM cart c
        JOIN products p ON c.PID = p.PID
        WHERE c.UID = :uid
    ");
    $stmt->execute(["uid" => $uid]);
    $cart_items = $stmt->fetchAll();
} else {
    $session_cart = $_SESSION["cart"] ?? [];
    if (!empty($session_cart)) {
        $placeholders = implode(',', array_fill(0, count($session_cart), '?'));
        $stmt = $pdo->prepare("SELECT * FROM products WHERE PID IN ($placeholders)");
        $stmt->execute(array_keys($session_cart));
        $products = $stmt->fetchAll(PDO::FETCH_UNIQUE);

        foreach ($session_cart as $pid => $qty) {
            if (isset($products[$pid])) {
                $p = $products[$pid];
                $p['QUANTITY'] = $qty;
                $p['PID'] = $pid;
                $cart_items[] = $p;
            }
        }
    }
}

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Your Cart | ByteMeTech</title>
    <style>
        .cart-item {
            display: flex;
            gap: 20px;
            align-items: center;
            border-bottom: 1px solid #ccc;
            padding: 15px 0;
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .cart-item .info {
            flex: 1;
        }
        .cart-summary {
            margin-top: 20px;
            text-align: right;
            font-size: 1.2em;
        }
        .quantity-adjust {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .quantity-adjust button {
            padding: 5px 10px;
        }
        .quantity-adjust input {
            width: 50px;
            text-align: center;
        }
        .btn-red {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-red:hover {
            background-color: #c82333;
        }
        .btn-green, .btn-blue {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-green {
            background-color: #28a745;
            color: white;
        }
        .btn-blue {
            background-color: #007bff;
            color: white;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<?php include "../includes/navbar.php"; ?>

<div class="container bg-dark">
    <h1 class="white-text">Your Cart</h1>
</div>

<div class="container">
    <?php if (!empty($_SESSION["success_message"])): ?>
        <div class="alert-success"><?= htmlspecialchars($_SESSION["success_message"]) ?></div>
        <?php unset($_SESSION["success_message"]); ?>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <form method="post" id="cart-form">
            <div style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                <label><input type="checkbox" id="select-all"> Select All</label>
                <div>
                    <button type="submit" name="action" value="remove" class="btn btn-red"
                        onclick="return confirm('Remove selected items?')">
                        Remove Selected
                    </button>
                    <button type="submit" name="action" value="checkout" class="btn btn-green">
                        Checkout Selected
                    </button>
                </div>
            </div>

            <?php foreach ($cart_items as $item): ?>
                <?php
                $pid = $item["PID"];
                $img = $item["PRODUCT_IMAGE_PATH"] ? "../" . $item["PRODUCT_IMAGE_PATH"] : "../images/default-image.jpg";
                $price = $item["PRODUCT_PRICE_SALE"] > 0 ? $item["PRODUCT_PRICE_SALE"] : $item["PRODUCT_PRICE_REGULAR"];
                $qty = $item["QUANTITY"] ?? $item["quantity"];
                $subtotal = $price * $qty;
                $total_price += $subtotal;
                ?>
                <div class="cart-item" data-pid="<?= $pid ?>">
                    <input type="checkbox" class="item-checkbox" name="selected_items[]" value="<?= $pid ?>">
                    <img src="<?= htmlspecialchars($img) ?>" alt="Product Image">
                    <div class="info">
                        <h3><?= htmlspecialchars($item["PRODUCT_NAME"]) ?></h3>
                        <p>Price: RM<?= number_format($price, 2) ?></p>
                        <div class="quantity-adjust">
                            <button type="button" class="btn btn-blue adjust-btn" data-change="-1">−</button>
                            <input type="text" value="<?= $qty ?>" readonly>
                            <button type="button" class="btn btn-blue adjust-btn" data-change="1">+</button>
                        </div>
                        <p>Subtotal: RM<span class="subtotal"><?= number_format($subtotal, 2) ?></span></p>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="cart-summary">
                <strong>Total: RM<span id="total-price"><?= number_format($total_price, 2) ?></span></strong><br><br>
                
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
$(function () {
    $("#select-all").on("change", function () {
        $(".item-checkbox").prop("checked", this.checked);
    });

    $(".adjust-btn").on("click", function () {
        const $item = $(this).closest(".cart-item");
        const $input = $item.find("input[type='text']");
        const pid = $item.data("pid");
        const change = parseInt($(this).data("change"));
        let current = parseInt($input.val());

        if (!isNaN(current)) {
            let newQty = Math.max(1, current + change);

            $.post("update-cart.php", {pid: pid, qty: newQty}, function (res) {
                if (res.success) {
                    $input.val(newQty);
                    $item.find(".subtotal").text(parseFloat(res.subtotal).toFixed(2));
                    $("#total-price").text(parseFloat(res.total).toFixed(2));
                }
            }, "json");
        }
    });
});

document.getElementById("cart-form").addEventListener("submit", function (e) {
    const checked = document.querySelectorAll(".item-checkbox:checked");
    if (checked.length === 0) {
        e.preventDefault();
        alert("Please select at least one item.");
    }
});
</script>

<?php include "../includes/footer.php"; ?>
</body>
</html>

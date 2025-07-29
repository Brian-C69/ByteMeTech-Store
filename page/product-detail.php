<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

$pid = (int) ($_GET['id'] ?? 0);
if (!$pid) {
    header("Location: product.php");
    exit;
}

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE PID = :pid");
$stmt->execute(['pid' => $pid]);
$product = $stmt->fetch();

$stock_status_labels = [
    "in_stock" => "In Stock",
    "out_of_stock" => "Out of Stock",
    "backorder" => "Backorder"
];

if (!$product) {
    temp("info", "Product not found.");
    header("Location: product.php");
    exit;
}

$imagePath = $product['PRODUCT_IMAGE_PATH'] ? "../" . $product['PRODUCT_IMAGE_PATH'] : "../images/default-image.jpg";

// Parse attributes
$attributes = [];
if (!empty($product['PRODUCT_ATTRIBUTES'])) {
    foreach (explode("\n", $product['PRODUCT_ATTRIBUTES']) as $line) {
        if (strpos($line, ',') !== false) {
            [$title, $opts] = explode(',', $line, 2);
            $attributes[trim($title)] = explode('|', trim($opts));
        }
    }
}

// Related products
function get_related_products($pdo, $ids) {
    $result = [];
    $idList = array_filter(array_map('intval', explode(',', $ids)));
    if ($idList) {
        $in = implode(',', array_fill(0, count($idList), '?'));
        $stmt = $pdo->prepare("SELECT * FROM products WHERE PID IN ($in)");
        $stmt->execute($idList);
        $result = $stmt->fetchAll();
    }
    return $result;
}

$upsells = get_related_products($pdo, $product['PRODUCT_UPSELLS']);
$cross_sells = get_related_products($pdo, $product['PRODUCT_CROSS_SELLS']);

// Check like status
$liked = false;
if (isset($_SESSION["user_id"])) {
    $uid = $_SESSION["user_id"];
    $checkLike = $pdo->prepare("SELECT 1 FROM product_likes WHERE UID = :uid AND PID = :pid");
    $checkLike->execute(["uid" => $uid, "pid" => $pid]);
    $liked = $checkLike->fetchColumn();
}

$_title = $product['PRODUCT_NAME'];
include '../includes/headers.php';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?= htmlspecialchars($_title) ?> | ByteMeTech</title>
        <style>
            .product-detail {
                display: flex;
                flex-wrap: wrap;
                gap: 80px;
                margin-top: 30px;
                background: white;
                border-radius: 10px;
                padding: 20px;
            }

            .product-detail .image {
                flex: 1;
                min-width: 300px;
            }

            .product-detail .image img {
                width: 100%;
                aspect-ratio: 1/1;
                object-fit: cover;
                border-radius: 10px;
                background-color: #f0f0f0;
            }

            .product-detail .info {
                flex: 2;
                min-width: 300px;
            }

            .product-detail .info h1 {
                margin-top: 0;
                margin-bottom: 10px;
            }

            .product-detail .info p {
                margin: 5px 0;
            }

            .product-detail select,
            .product-detail input[type="number"] {
                width: 200px;
                margin-bottom: 10px;
            }

            .product-detail .btn-row {
                display: flex;
                align-items: baseline;
                gap: 15px;
                margin-top: 15px;
            }

            .product-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-top: 30px;
            }

            .product-card {
                background: white;
                border: 1px solid #ccc;
                border-radius: 10px;
                text-align: center;
                padding: 10px;
            }

            .product-card img {
                width: 100%;
                aspect-ratio: 1/1;
                object-fit: cover;
                border-radius: 10px;
                background-color: #f0f0f0;
            }

            .product-card .title {
                font-weight: bold;
                margin: 10px 0 5px;
            }

            .product-card .price {
                color: green;
                font-weight: bold;
            }

            .temp-message {
                color: green;
                margin-bottom: 15px;
            }

            .related-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 15px;
                margin-top: 20px;
            }

            .related-grid .product-card {
                border: 1px solid #ccc;
                border-radius: 8px;
                padding: 10px;
                background: #fff;
                text-align: center;
                font-size: 0.85em;
            }

            .related-grid .product-card img {
                width: 100%;
                aspect-ratio: 1/1;
                object-fit: cover;
                border-radius: 6px;
                background-color: #f0f0f0;
            }

            .related-grid .product-card .title {
                font-weight: 600;
                margin: 8px 0 4px;
                font-size: 0.95em;
            }

            .related-grid .product-card .price {
                font-weight: bold;
                color: green;
                font-size: 0.9em;
            }

        </style>
    </head>
    <body class="bg-light">
        <?php include '../includes/navbar.php'; ?>
        <div class="container bg-dark">
            <h1>Product Details</h1>
        </div>  

        <div class="container">
            <a href="product.php" class="btn btn-blue0">Back</a>
        </div>  
        <div class="container">

            <div class="product-detail">
                <div class="image">
                    <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['PRODUCT_NAME']) ?>">

                    <div class="btn-row" style="margin-top: 10px;">
                        <p><strong><?= $product['PRODUCT_LIKES'] ?> like(s)</strong></p>
                        <form method="post" action="like-product.php">
                            <input type="hidden" name="pid" value="<?= $product['PID'] ?>">
                            <button type="submit" class="btn btn-red"><?= $liked ? "❤️ Liked" : "❤️ Like" ?></button>
                        </form>

                    </div>
                    <?php if ($msg = temp('info')): ?>
                        <div class="temp-message"><?= htmlspecialchars($msg) ?></div>
                    <?php endif; ?>
                </div>

                <div class="info">
                    <h1><?= htmlspecialchars($product['PRODUCT_NAME']) ?></h1>
                    <p><strong>Price:</strong>
                        <?php
                        $reg = (float) $product['PRODUCT_PRICE_REGULAR'];
                        $sale = (float) $product['PRODUCT_PRICE_SALE'];

                        if ($reg > 0 && $sale > 0 && $sale < $reg):
                            ?>
                            <span style="text-decoration: line-through; color: #999;">
                                RM<?= number_format($reg, 2) ?>
                            </span>
                            <span style="color: green; font-weight: bold; margin-left: 10px;">
                                RM<?= number_format($sale, 2) ?>
                            </span>
                        <?php elseif ($sale > 0): ?>
                            <span style="color: green; font-weight: bold;">
                                RM<?= number_format($sale, 2) ?>
                            </span>
                        <?php elseif ($reg > 0): ?>
                            <span style="color: green; font-weight: bold;">
                                RM<?= number_format($reg, 2) ?>
                            </span>
                        <?php else: ?>
                            <em style="color: red;">No price available</em>
                        <?php endif; ?>
                    </p>

                    <?php if ($reg > 0 && $sale > 0 && $sale < $reg): ?>
                        <p style="color: red; font-weight: bold;">Save RM<?= number_format($reg - $sale, 2) ?>!</p>
                    <?php endif; ?>
                    <p><strong>Stock:</strong> <?= $stock_status_labels[$product['PRODUCT_STOCK_STATUS']] ?? 'Unknown' ?></p>
                    <p><strong>Weight:</strong> <?= $product['PRODUCT_WEIGHT'] ?> kg</p>
                    <p><strong>Dimensions:</strong> <?= $product['PRODUCT_DIMENSION_LENGTH'] ?> × <?= $product['PRODUCT_DIMENSION_WIDTH'] ?> × <?= $product['PRODUCT_DIMENSION_HEIGHT'] ?> cm</p>

                    <?php foreach ($attributes as $title => $opts): ?>
                        <label><?= htmlspecialchars($title) ?></label>
                        <select class="input-field">
                            <?php foreach ($opts as $opt): ?>
                                <option><?= htmlspecialchars($opt) ?></option>
                            <?php endforeach; ?>
                        </select><br>
                    <?php endforeach; ?>

                    <div class="btn-row">
                        <label style="margin: 0;">Quantity</label>
                        <button type="button" class="btn btn-blue qty-minus">-</button>
                        <input type="number" id="qty" name="qty" value="1" min="1" class="input-field" style="width: 60px;">
                        <input type="hidden" id="cart-qty" value="1">
                        <input type="hidden" id="buy-qty" value="1">
                        <button type="button" class="btn btn-blue qty-plus">+</button>
                    </div>

                    <div class="btn-row">
                        <form method="post" action="add-to-cart.php">
                            <input type="hidden" name="pid" value="<?= $product['PID'] ?>">
                            <input type="hidden" id="cart-qty" name="qty" value="1">
                            <button class="btn btn-blue" type="submit">Add to Cart</button>
                        </form>

                        <form method="post" action="buy-now.php">
                            <input type="hidden" name="pid" value="<?= $product['PID'] ?>">
                            <input type="hidden" id="buy-qty" name="qty" value="1">
                            <button class="btn btn-green" type="submit">Buy Now</button>
                        </form>
                    </div>
                </div>
            </div>

            <?php if (!empty($upsells)): ?>
                <h2 class="mt-5">You may also like</h2>
                <div class="related-grid">
                    <?php foreach ($upsells as $p): ?>
                        <div class="product-card">
                            <a href="product-detail.php?id=<?= $p['PID'] ?>">
                                <img src="<?= $p['PRODUCT_IMAGE_PATH'] ? '../' . $p['PRODUCT_IMAGE_PATH'] : '../images/default-image.jpg' ?>" alt="">
                                <div class="title"><?= htmlspecialchars($p['PRODUCT_NAME']) ?></div>
                                <div class="price">
                                    <?php
                                    $reg = (float) $p['PRODUCT_PRICE_REGULAR'];
                                    $sale = (float) $p['PRODUCT_PRICE_SALE'];
                                    if ($reg > 0 && $sale > 0 && $sale < $reg):
                                        ?>
                                        <span style="text-decoration: line-through; color: #888;">RM<?= number_format($reg, 2) ?></span>
                                        <span style="color: green; font-weight: bold; margin-left: 5px;">RM<?= number_format($sale, 2) ?></span>
                                    <?php elseif ($sale > 0): ?>
                                        <span style="color: green; font-weight: bold;">RM<?= number_format($sale, 2) ?></span>
                                    <?php elseif ($reg > 0): ?>
                                        <span style="color: green; font-weight: bold;">RM<?= number_format($reg, 2) ?></span>
                                    <?php else: ?>
                                        <span style="color: red;">No price set</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            
            <script>
                $(function () {
                    function updateQuantity(change) {
                        let currentQty = parseInt($("#qty").val()) || 1;

                        $.post("adjustQuantity.php", {qty: currentQty, change: change}, function (response) {
                            $("#qty").val(response.qty);
                            $("#cart-qty").val(response.qty);
                            $("#buy-qty").val(response.qty);
                        }, "json");
                    }

                    $(".qty-minus").on("click", function () {
                        updateQuantity(-1);
                    });

                    $(".qty-plus").on("click", function () {
                        updateQuantity(1);
                    });

                    $("#qty").on("input", function () {
                        let qty = parseInt($(this).val()) || 1;
                        $("#cart-qty").val(qty);
                        $("#buy-qty").val(qty);
                    });
                });
            </script>
        </div>
        <?php include '../includes/footer.php'; ?>
    </body>
</html>

<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Redirect if not admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("location: ../page/login.php");
    exit;
}

// Initialize variables
$product = [
    "PRODUCT_NAME" => "",
    "PRODUCT_PRICE_REGULAR" => "",
    "PRODUCT_PRICE_SALE" => "",
    "PRODUCT_CATEGORY" => "",
    "PRODUCT_SKU" => "",
    "PRODUCT_STOCK_STATUS" => "",
    "PRODUCT_SOLD_INDIVIDUALLY" => 0,
    "PRODUCT_QUANTITY" => "",
    "PRODUCT_BACKORDER" => "",
    "PRODUCT_STOCK_ALERT" => "",
    "PRODUCT_WEIGHT" => "",
    "PRODUCT_DIMENSION_LENGTH" => "",
    "PRODUCT_DIMENSION_WIDTH" => "",
    "PRODUCT_DIMENSION_HEIGHT" => "",
    "PRODUCT_UPSELLS" => "",
    "PRODUCT_CROSS_SELLS" => "",
    "PRODUCT_ATTRIBUTES" => "",
    "PRODUCT_DESCRIPTION" => "",
    "PRODUCT_STATUS" => ""
];

$categories = [
    "Keyboard" => "Keyboard",
    "Mouse"    => "Mouse",
    "Monitor"  => "Monitor",
    "Laptop"   => "Laptop",
    "Phone"    => "Phone"
];

$_err = [];

if (is_post()) {
    foreach ($product as $key => &$val) {
        $val = trim($_POST[$key] ?? '');
        $val = htmlspecialchars($val);
    }

    $product["PRODUCT_PRICE_REGULAR"] = number_format((float) $product["PRODUCT_PRICE_REGULAR"], 2, '.', '');
    $product["PRODUCT_PRICE_SALE"] = number_format((float) $product["PRODUCT_PRICE_SALE"], 2, '.', '');
    $product["PRODUCT_SOLD_INDIVIDUALLY"] = isset($_POST["product_sold_individually"]) ? 1 : 0;

    if ($product["PRODUCT_NAME"] === "") $_err["PRODUCT_NAME"] = "Required.";
    if (!is_money($product["PRODUCT_PRICE_REGULAR"])) $_err["PRODUCT_PRICE_REGULAR"] = "Invalid price.";
    if ($product["PRODUCT_PRICE_SALE"] && !is_money($product["PRODUCT_PRICE_SALE"])) $_err["PRODUCT_PRICE_SALE"] = "Invalid price.";
    if ($product["PRODUCT_CATEGORY"] === "") $_err["PRODUCT_CATEGORY"] = "Required.";

    $image_path = "images/default-image.jpg";
    $f = get_file("product_image");
    if ($f && str_starts_with($f->type, "image/")) {
        $image_name = save_photo($f, "../uploads", 800, 800);
        $image_path = "uploads/$image_name";
    }

    if (!$_err) {
        $stmt = $pdo->prepare("INSERT INTO products (
            PRODUCT_NAME, PRODUCT_PRICE_REGULAR, PRODUCT_PRICE_SALE, PRODUCT_CATEGORY, PRODUCT_SKU,
            PRODUCT_STOCK_STATUS, PRODUCT_SOLD_INDIVIDUALLY, PRODUCT_QUANTITY, PRODUCT_BACKORDER,
            PRODUCT_STOCK_ALERT, PRODUCT_WEIGHT, PRODUCT_DIMENSION_LENGTH, PRODUCT_DIMENSION_WIDTH,
            PRODUCT_DIMENSION_HEIGHT, PRODUCT_UPSELLS, PRODUCT_CROSS_SELLS, PRODUCT_ATTRIBUTES, 
            PRODUCT_DESCRIPTION, PRODUCT_STATUS, PRODUCT_IMAGE_PATH
        ) VALUES (
            :name, :price_reg, :price_sale, :category, :sku,
            :stock_status, :sold_individually, :qty, :backorder,
            :stock_alert, :weight, :length, :width,
            :height, :upsells, :cross_sells, :attributes,
            :description, :product_status, :image
        )");

        $stmt->execute([
            "name" => $product["PRODUCT_NAME"],
            "price_reg" => $product["PRODUCT_PRICE_REGULAR"],
            "price_sale" => $product["PRODUCT_PRICE_SALE"],
            "category" => $product["PRODUCT_CATEGORY"],
            "sku" => $product["PRODUCT_SKU"],
            "stock_status" => $product["PRODUCT_STOCK_STATUS"],
            "sold_individually" => $product["PRODUCT_SOLD_INDIVIDUALLY"],
            "qty" => $product["PRODUCT_QUANTITY"],
            "backorder" => $product["PRODUCT_BACKORDER"],
            "stock_alert" => $product["PRODUCT_STOCK_ALERT"],
            "weight" => $product["PRODUCT_WEIGHT"],
            "length" => $product["PRODUCT_DIMENSION_LENGTH"],
            "width" => $product["PRODUCT_DIMENSION_WIDTH"],
            "height" => $product["PRODUCT_DIMENSION_HEIGHT"],
            "upsells" => $product["PRODUCT_UPSELLS"],
            "cross_sells" => $product["PRODUCT_CROSS_SELLS"],
            "attributes" => $product["PRODUCT_ATTRIBUTES"],
            "description" => $product["PRODUCT_DESCRIPTION"],
            "product_status" => $product["PRODUCT_STATUS"],
            "image" => $image_path
        ]);

        temp("info", "Product added successfully.");
        header("location: product_manage.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Product | ByteMeTech.com</title>
    <?php include '../includes/headers.php'; ?>
</head>
<body class="bg-light">
<?php include '../includes/sidebar.php'; ?>

<div class="container">
    <h1>Add Product</h1>
    <form method="post" enctype="multipart/form-data" class="product-form">

    <label for="PRODUCT_NAME">Product Name</label>
    <?= html_text("PRODUCT_NAME") ?>
    <?= err("PRODUCT_NAME") ?>

    <label for="PRODUCT_CATEGORY">Category</label>
    <?= html_select("PRODUCT_CATEGORY", $categories, "- Select Category -") ?>
    <?= err("PRODUCT_CATEGORY") ?>

    <label for="PRODUCT_PRICE_REGULAR">Price (RM)</label>
    <?= html_text("PRODUCT_PRICE_REGULAR") ?>
    <?= err("PRODUCT_PRICE_REGULAR") ?>

    <label for="PRODUCT_PRICE_SALE">Sale Price (RM)</label>
    <?= html_text("PRODUCT_PRICE_SALE") ?>
    <?= err("PRODUCT_PRICE_SALE") ?>

    <label for="PRODUCT_SKU">SKU</label>
    <?= html_text("PRODUCT_SKU") ?>
    <div></div>

    <label for="PRODUCT_STOCK_STATUS">Stock Status</label>
    <?= html_select("PRODUCT_STOCK_STATUS", [
        "in_stock" => "In Stock",
        "out_of_stock" => "Out of Stock",
        "backorder" => "Backorder"
    ]) ?>
    <div></div>

    <label>Sold Individually</label>
    <?= html_checkbox("product_sold_individually", "Limit to 1 per order") ?>
    <div></div>

    <label for="PRODUCT_QUANTITY">Quantity</label>
    <?= html_text("PRODUCT_QUANTITY") ?>
    <div></div>

    <label for="PRODUCT_BACKORDER">Backorder</label>
    <?= html_text("PRODUCT_BACKORDER") ?>
    <div></div>

    <label for="PRODUCT_STOCK_ALERT">Stock Alert</label>
    <?= html_text("PRODUCT_STOCK_ALERT") ?>
    <div></div>

    <label for="PRODUCT_WEIGHT">Weight (kg)</label>
    <?= html_text("PRODUCT_WEIGHT") ?>
    <div></div>

    <label>Dimensions (L×W×H cm)</label>
    <div style="display: flex; gap: 10px;">
        <?= html_text("PRODUCT_DIMENSION_LENGTH", 'placeholder="Length"') ?>
        <?= html_text("PRODUCT_DIMENSION_WIDTH", 'placeholder="Width"') ?>
        <?= html_text("PRODUCT_DIMENSION_HEIGHT", 'placeholder="Height"') ?>
    </div>
    <div></div>

    <label for="PRODUCT_UPSELLS">Upsells (ID)</label>
    <?= html_text("PRODUCT_UPSELLS") ?>
    <div></div>

    <label for="PRODUCT_CROSS_SELLS">Cross-sells (ID)</label>
    <?= html_text("PRODUCT_CROSS_SELLS") ?>
    <div></div>

    <label for="PRODUCT_ATTRIBUTES">Attributes</label>
    <?= html_textarea("PRODUCT_ATTRIBUTES") ?>
    <div></div>

    <label for="PRODUCT_DESCRIPTION">Description</label>
    <?= html_textarea("PRODUCT_DESCRIPTION") ?>
    <div></div>

    <label for="PRODUCT_STATUS">Status</label>
    <?= html_select("PRODUCT_STATUS", [
        "active" => "Active",
        "inactive" => "Inactive"
    ]) ?>
    <div></div>

    <label for="product_image">Image</label>
    <?= html_file("product_image", "image/*") ?>
    <div></div>

    <div class="full-width">
        <button type="submit" class="btn btn-green">Add Product</button>
        <a href="product_manage.php" class="btn btn-blue">Back</a>
    </div>
</form>

</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>

<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Redirect if not admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("location: ../page/login.php");
    exit;
}

$pid = (int) ($_GET['id'] ?? 0);

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE PID = :pid");
$stmt->execute(["pid" => $pid]);
$product = $stmt->fetch();

$categories = [
    "Keyboard" => "Keyboard",
    "Mouse"    => "Mouse",
    "Monitor"  => "Monitor",
    "Laptop"   => "Laptop",
    "Phone"    => "Phone"
];

if (!$product) {
    temp("info", "Product not found.");
    header("location: product_manage.php");
    exit;
}

$_err = [];

if (is_post()) {
    $product_name = trim(post("PRODUCT_NAME"));
    $price_regular = trim(post("PRODUCT_PRICE_REGULAR"));
    $price_sale = trim(post("PRODUCT_PRICE_SALE"));
    $category = trim(post("PRODUCT_CATEGORY"));
    $sku = trim(post("PRODUCT_SKU"));
    $stock_status = post("PRODUCT_STOCK_STATUS");
    $sold_individually = post("PRODUCT_SOLD_INDIVIDUALLY", 0);
    $qty = trim(post("PRODUCT_QUANTITY"));
    $backorder = trim(post("PRODUCT_BACKORDER"));
    $stock_alert = trim(post("PRODUCT_STOCK_ALERT"));
    $weight = trim(post("PRODUCT_WEIGHT"));
    $length = trim(post("PRODUCT_DIMENSION_LENGTH"));
    $width = trim(post("PRODUCT_DIMENSION_WIDTH"));
    $height = trim(post("PRODUCT_DIMENSION_HEIGHT"));
    $upsells = trim(post("PRODUCT_UPSELLS"));
    $cross_sells = trim(post("PRODUCT_CROSS_SELLS"));
    $attributes = trim(post("PRODUCT_ATTRIBUTES"));
    $description = trim(post("PRODUCT_DESCRIPTION"));
    $status = post("PRODUCT_STATUS");
    
    // Delete image if requested
    if (isset($_POST["delete_image"])) {
    $img_path = "../" . $product['PRODUCT_IMAGE_PATH'];
    if (file_exists($img_path)) {
        unlink($img_path);
    }

    $stmt = $pdo->prepare("UPDATE products SET PRODUCT_IMAGE_PATH = NULL WHERE PID = :pid");
    $stmt->execute(["pid" => $pid]);

    temp("info", "Product image deleted.");
    header("location: edit-product.php?id=$pid");
    exit;
    }
    
    // Handle new image upload
$f = get_file("product_image");
if ($f && str_starts_with($f->type, "image/")) {
    $image_name = save_photo($f, "../uploads", 800, 800);
    $image_path = "uploads/$image_name";

    // Delete old image if it exists
    if (!empty($product["PRODUCT_IMAGE_PATH"]) && file_exists("../" . $product["PRODUCT_IMAGE_PATH"])) {
        unlink("../" . $product["PRODUCT_IMAGE_PATH"]);
    }

    // Update image path
    $stmt = $pdo->prepare("UPDATE products SET PRODUCT_IMAGE_PATH = :img WHERE PID = :pid");
    $stmt->execute([
        "img" => $image_path,
        "pid" => $pid
    ]);

    // Refresh product data after image update
    $product["PRODUCT_IMAGE_PATH"] = $image_path;
}
    
    if ($product_name === '') $_err["PRODUCT_NAME"] = "Required";
    if (!is_money($price_regular)) $_err["PRODUCT_PRICE_REGULAR"] = "Invalid price";

    if (!$_err) {
        $stmt = $pdo->prepare("UPDATE products SET
            PRODUCT_NAME = :name,
            PRODUCT_PRICE_REGULAR = :price_reg,
            PRODUCT_PRICE_SALE = :price_sale,
            PRODUCT_CATEGORY = :category,
            PRODUCT_SKU = :sku,
            PRODUCT_STOCK_STATUS = :stock_status,
            PRODUCT_SOLD_INDIVIDUALLY = :sold_individually,
            PRODUCT_QUANTITY = :qty,
            PRODUCT_BACKORDER = :backorder,
            PRODUCT_STOCK_ALERT = :stock_alert,
            PRODUCT_WEIGHT = :weight,
            PRODUCT_DIMENSION_LENGTH = :length,
            PRODUCT_DIMENSION_WIDTH = :width,
            PRODUCT_DIMENSION_HEIGHT = :height,
            PRODUCT_UPSELLS = :upsells,
            PRODUCT_CROSS_SELLS = :cross_sells,
            PRODUCT_ATTRIBUTES = :attributes,
            PRODUCT_DESCRIPTION = :description,
            PRODUCT_STATUS = :status
            WHERE PID = :pid");

        $stmt->execute([
            "name" => $product_name,
            "price_reg" => $price_regular,
            "price_sale" => $price_sale,
            "category" => $category,
            "sku" => $sku,
            "stock_status" => $stock_status,
            "sold_individually" => $sold_individually,
            "qty" => $qty,
            "backorder" => $backorder,
            "stock_alert" => $stock_alert,
            "weight" => $weight,
            "length" => $length,
            "width" => $width,
            "height" => $height,
            "upsells" => $upsells,
            "cross_sells" => $cross_sells,
            "attributes" => $attributes,
            "description" => $description,
            "status" => $status,
            "pid" => $pid
        ]);

        temp("info", "Product updated.");
        header("location: product_manage.php");
        exit;
    }
}

$_title = "Edit Product";
include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Product</title>
</head>
<body class="bg-light">
<?php include '../includes/sidebar.php'; ?>

<div class="container">
    <h1>Edit Product</h1>
    <form method="post" enctype="multipart/form-data" class="product-form">
        <label>Name</label>
        <input type="text" name="PRODUCT_NAME" value="<?= htmlspecialchars($product['PRODUCT_NAME']) ?>" class="input-field">
        <?= err("PRODUCT_NAME") ?>
        
        <label>Category</label>
        <select name="PRODUCT_CATEGORY" class="input-field">
            <option value="">- Select Category -</option>
            <?php foreach ($categories as $key => $label): ?>
                <option value="<?= $key ?>" <?= $product["PRODUCT_CATEGORY"] === $key ? "selected" : "" ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?= err("PRODUCT_CATEGORY") ?>
        
        <label>Regular Price</label>
        <input type="text" name="PRODUCT_PRICE_REGULAR" value="<?= $product['PRODUCT_PRICE_REGULAR'] ?>" class="input-field">
        <?= err("PRODUCT_PRICE_REGULAR") ?>
        
        <label>Sale Price</label>
        <input type="text" name="PRODUCT_PRICE_SALE" value="<?= $product['PRODUCT_PRICE_SALE'] ?>" class="input-field">
        <?= err("PRODUCT_PRICE_SALE") ?>
        
        <label>SKU</label>
        <input type="text" name="PRODUCT_SKU" value="<?= htmlspecialchars($product['PRODUCT_SKU']) ?>" class="input-field">
        <div></div>
        
        <label>Stock Status</label>
        <?= html_select("PRODUCT_STOCK_STATUS", [
            "in_stock" => "In Stock",
            "out_of_stock" => "Out of Stock",
            "backorder" => "Backorder"
        ], null, "", $product['PRODUCT_STOCK_STATUS']) ?>
        <div></div>

        <label>Sold Individually</label>
        <input type="checkbox" name="PRODUCT_SOLD_INDIVIDUALLY" value="1" <?= $product['PRODUCT_SOLD_INDIVIDUALLY'] ? 'checked' : '' ?> class="input-field">
        <div></div>
        
        <label>Quantity</label>
        <input type="number" name="PRODUCT_QUANTITY" value="<?= $product['PRODUCT_QUANTITY'] ?>" class="input-field">
        <div></div>
        
        <label>Backorder</label>
        <input type="number" name="PRODUCT_BACKORDER" value="<?= $product['PRODUCT_BACKORDER'] ?>" class="input-field">
        <div></div>
        
        <label>Stock Alert</label>
        <input type="number" name="PRODUCT_STOCK_ALERT" value="<?= $product['PRODUCT_STOCK_ALERT'] ?>" class="input-field">
        <div></div>
        
        <label>Weight (kg)</label>
        <input type="text" name="PRODUCT_WEIGHT" value="<?= $product['PRODUCT_WEIGHT'] ?>" class="input-field">
        <div></div>
        
        <label>Dimensions (L x W x H)</label>
        <div style="display: flex; gap: 10px;">
        <input type="text" name="PRODUCT_DIMENSION_LENGTH" value="<?= $product['PRODUCT_DIMENSION_LENGTH'] ?>" class="input-field">
        <input type="text" name="PRODUCT_DIMENSION_WIDTH" value="<?= $product['PRODUCT_DIMENSION_WIDTH'] ?>" class="input-field">
        <input type="text" name="PRODUCT_DIMENSION_HEIGHT" value="<?= $product['PRODUCT_DIMENSION_HEIGHT'] ?>" class="input-field">
        </div>
        <div></div>
        
        <label>Upsells (IDs)</label>
        <input type="text" name="PRODUCT_UPSELLS" value="<?= $product['PRODUCT_UPSELLS'] ?>" class="input-field">
        <div></div>
        
        <label>Cross-sells (IDs)</label>
        <input type="text" name="PRODUCT_CROSS_SELLS" value="<?= $product['PRODUCT_CROSS_SELLS'] ?>" class="input-field">
        <div></div>
        
        <label>Attributes</label>
        <textarea name="PRODUCT_ATTRIBUTES" class="input-field"><?= htmlspecialchars($product['PRODUCT_ATTRIBUTES']) ?></textarea>
        <div></div>
        
        <label>Description</label>
        <textarea name="PRODUCT_DESCRIPTION" class="input-field"><?= htmlspecialchars($product['PRODUCT_DESCRIPTION']) ?></textarea>
        <div></div>
        
        <label>Status</label>
        <?= html_select("PRODUCT_STATUS", [
            "Active" => "Active",
            "Inactive" => "Inactive"
        ], null, "", $product['PRODUCT_STATUS']) ?>
        <div></div>
        
        <?php if (!empty($product['PRODUCT_IMAGE_PATH']) && file_exists("../" . $product['PRODUCT_IMAGE_PATH'])): ?>
    <label>Current Image</label>
    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 15px;">
        <img src="../<?= htmlspecialchars($product['PRODUCT_IMAGE_PATH']) ?>" alt="Product Image" width="100" style="border-radius: 8px; object-fit: cover;">
        <button type="submit" name="delete_image" class="btn btn-red" onclick="return confirm('Delete current product image?')">Delete Image</button>
    </div>
    <div></div>
<?php endif; ?>
    
    
        <label for="product_image">Replace Image</label>
        <input type="file" name="product_image" accept="image/*" class="input-field">
    <div></div>
    
        <div class="full-width">
        <button type="submit" class="btn btn-green">Update Product</button>
        <a href="product_manage.php" class="btn btn-gray">Cancel</a>
        </div>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>

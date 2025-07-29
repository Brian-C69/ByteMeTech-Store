<?php
require_once "../includes/base.php";
require_once "../includes/config.php";
require_once "../lib/SimplePager.php";

// Categories
$categories = [
    "Keyboard" => "Keyboard",
    "Mouse" => "Mouse",
    "Monitor" => "Monitor",
    "Laptop" => "Laptop",
    "Phone" => "Phone"
];

// Filters & Sort
$search = req("search", "");
$category = req("category", "");
$sort = req("sort", "name");
$page = req("page", 1);

$valid_sorts = [
    "name" => "PRODUCT_NAME",
    "price" => "PRODUCT_PRICE_REGULAR",
    "likes" => "PRODUCT_LIKES"
];
$order_by = $valid_sorts[$sort] ?? "PRODUCT_NAME";

// Build query
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(PRODUCT_NAME LIKE :search OR PRODUCT_DESCRIPTION LIKE :search)";
    $params["search"] = "%$search%";
}
if (!empty($category)) {
    $where[] = "PRODUCT_CATEGORY = :category";
    $params["category"] = $category;
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

$query = "SELECT * FROM products $where_sql ORDER BY $order_by ASC";
$pager = new SimplePager($pdo, $query, $params, 16, $page);
$products = $pager->result;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Products | ByteMeTech.com</title>
    <?php include "../includes/headers.php"; ?>
    <style>
        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .product-card {
            background: white;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: 0.3s ease;
        }

        .product-card:hover {
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }

        .product-card img {
            width: 100%;
            aspect-ratio: 1/1;
            object-fit: cover;
            background-color: #f0f0f0;
        }

        .product-card .title {
            font-size: 1.1em;
            font-weight: bold;
            margin: 10px 0;
            text-align: center;
        }

        .product-card .price {
            color: green;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .product-card .likes {
            font-size: 0.9em;
            color: #888;
        }

        .product-card .btn-group {
            margin-top: auto;
            width: 100%;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .product-card button {
            width: 100%;
        }

        .product-name-link {
            color: inherit;
            text-decoration: none;
        }

        .product-name-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="bg-light">
<?php include "../includes/navbar.php"; ?>
<div class="container bg-dark">
    <h1>All Products</h1>
</div>    
<div class="container">

    <!-- Search & Filter -->
    <form method="get" class="login-form" style="display: flex; gap: 20px; flex-wrap: wrap;">
        <?= html_text("search", 'placeholder="Search products..." style="flex: 1;"') ?>
        <?= html_select("category", $categories, "- All Categories -") ?>
        <?= html_select("sort", ["name" => "Name", "price" => "Price", "likes" => "Likes"], "- Sort By -") ?>
        <button type="submit" class="btn btn-blue">Filter</button>
    </form>

    <!-- Products -->
    <?php if (empty($products)): ?>
        <div class="alert">No products found.</div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <?php
                    $imagePath = $p['PRODUCT_IMAGE_PATH'] ? "../" . htmlspecialchars($p['PRODUCT_IMAGE_PATH']) : "../images/default-image.jpg";
                    ?>
                    <a href="product-detail.php?id=<?= $p['PID'] ?>">
                        <img src="<?= $imagePath ?>" alt="Product Image">
                    </a>
                    <a href="product-detail.php?id=<?= $p['PID'] ?>" class="product-name-link">
                        <div class="title"><?= htmlspecialchars($p['PRODUCT_NAME']) ?></div>
                    </a>
                    <div class="price">
                        <?php
                        $reg = (float) $p['PRODUCT_PRICE_REGULAR'];
                        $sale = (float) $p['PRODUCT_PRICE_SALE'];

                        if ($reg > 0 && $sale > 0 && $sale < $reg):
                        ?>
                            <span style="text-decoration: line-through; color: #888;">
                                RM<?= number_format($reg, 2) ?>
                            </span>
                            <span style="color: green; font-weight: bold; margin-left: 5px;">
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
                            <span style="color: red;">No price set</span>
                        <?php endif; ?>
                    </div>
                    <div class="likes">❤️ <?= $p['PRODUCT_LIKES'] ?> likes</div>

                    <div class="btn-group">
                        <form method="post" action="add-to-cart.php">
                            <input type="hidden" name="pid" value="<?= $p['PID'] ?>">
                            <input type="hidden" name="qty" value="1">
                            <button class="btn btn-blue" type="submit">Add to Cart</button>
                        </form>

                        <form method="post" action="buy-now.php">
                            <input type="hidden" name="pid" value="<?= $p['PID'] ?>">
                            <input type="hidden" name="qty" value="1">
                            <button class="btn btn-green" type="submit">Buy Now</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="pagination mt-3">
        <?php $pager->html("search=" . urlencode($search) . "&category=$category&sort=$sort") ?>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>

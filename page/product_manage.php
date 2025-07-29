<?php
require_once "../includes/base.php";
require_once "../includes/config.php";
require_once "../lib/SimplePager.php";

// Redirect if not admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("location: ../page/login.php");
    exit;
}

// Handle Bulk Actions
if (is_post()) {
    $action = post("bulk_action");
    $selected = $_POST["selected_ids"] ?? [];

    if (!$action || empty($selected)) {
        temp("info", "Please select products and a valid bulk action.");
        redirect();
    }

    $ids = implode(",", array_map("intval", $selected));

    if ($action === "activate") {
        $pdo->exec("UPDATE products SET PRODUCT_STATUS = 'Active' WHERE PID IN ($ids)");
        temp("info", "Selected products activated.");
    } elseif ($action === "deactivate") {
        $pdo->exec("UPDATE products SET PRODUCT_STATUS = 'Inactive' WHERE PID IN ($ids)");
        temp("info", "Selected products deactivated.");
    } elseif ($action === "delete") {
        $pdo->exec("DELETE FROM products WHERE PID IN ($ids)");
        temp("info", "Selected products deleted.");
    }

    redirect();
}

// Sorting Config
$fields = [
    'PID'              => 'ID',
    'PRODUCT_NAME'     => 'Name',
    'PRODUCT_PRICE_REGULAR' => 'Regular Price',
    'PRODUCT_PRICE_SALE' => 'Sale Price',
    'PRODUCT_CATEGORY'  => 'Category',
    'PRODUCT_STOCK_STATUS'  => 'Stock Status',
    'PRODUCT_QUANTITY' => 'Quantity',
    'PRODUCT_STATUS' => 'Product Status',
    'PRODUCT_STOCK_ALERT' => 'Alert Threadshold',
    'PRODUCT_ATTRIBUTES' => 'Attributes',
    'PRODUCT_DESCRIPTION' => 'Description',
    'PRODUCT_IMAGE_PATH' => 'Image',
    'PRODUCT_LIKES' => 'Likes'
];

$sort = req('sort');
$dir = req('dir');
$page = req('page', 1);
$search = req('search', '');

if (!array_key_exists($sort, $fields)) $sort = 'PID';
if (!in_array($dir, ['asc', 'desc'])) $dir = 'asc';

// Bulk Action Options
$bulk_actions = [
    'activate'   => 'Activate',
    'deactivate' => 'Deactivate',
    'delete'     => 'Delete'
];

$stock_status_labels = [
    "in_stock"     => "In Stock",
    "out_of_stock" => "Out of Stock",
    "backorder"    => "Backorder"
];

$status_labels = [
    "Active" => "Active",
    "Inactive" => "Inactive"
];

// Search condition
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE PRODUCT_NAME LIKE :s OR PRODUCT_CATEGORY LIKE :s";
    $params = ['s' => "%$search%"];
}

$query = "SELECT * FROM products $where ORDER BY $sort $dir";
$pager = new SimplePager($pdo, $query, $params, 10, $page);
$products = $pager->result;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Products | ByteMeTech.com</title>
    <?php include '../includes/headers.php'; ?>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>

<div class="container">
    <h1>Manage Products</h1>

    <?php if ($msg = temp('info')): ?>
        <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Search -->
    <form method="get" class="login-form">
        <label for="search">Search</label>
        <?= html_text("search", 'placeholder="Search by name, category..."') ?>
        <div></div>
        <button type="submit" class="btn btn-blue">Search</button>
        <a href="product_manage.php" class="btn btn-gray">Reset</a>
    </form>

    <!-- Bulk -->
    <form method="post">
        <div class="login-form">
            <label for="bulk_action">Bulk Action</label>
            <?= html_select("bulk_action", $bulk_actions, "- Select Action -") ?>
            <button type="submit" class="btn btn-green"
                    onclick="return confirm('Apply this action to selected products?');">
                Apply
            </button>
        </div>

        <p><?= $pager->count ?> of <?= $pager->item_count ?> product(s) | Page <?= $pager->page ?> of <?= $pager->page_count ?></p>
        <a href="add-product.php" class="btn btn-blue">Add Products</a>
        <div class="table-scroll">
        <table class="table">
            <thead>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <?= table_headers($fields, $sort, $dir, "search=$search&page=$page") ?>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="100%" style="text-align:center;">No products found.</td></tr>
            <?php endif; ?>
            <?php foreach ($products as $index => $p): ?>
            <tr>
                <td><input type="checkbox" name="selected_ids[]" value="<?= $p['PID'] ?>"></td>
                <td><?= ($pager->limit * ($pager->page - 1)) + $index + 1 ?></td>
                <td><?= htmlspecialchars($p['PRODUCT_NAME']) ?></td>
                <td>RM<?= number_format($p['PRODUCT_PRICE_REGULAR'], 2) ?></td>
                <td>RM<?= number_format($p['PRODUCT_PRICE_SALE'], 2) ?></td>
                <td><?= htmlspecialchars($p['PRODUCT_CATEGORY']) ?></td>
                <td><?= htmlspecialchars($stock_status_labels[$p['PRODUCT_STOCK_STATUS']] ?? 'Unknown') ?></td>
                <td><?= $p['PRODUCT_QUANTITY'] ?></td>
                <td><?= htmlspecialchars($status_labels[$p['PRODUCT_STATUS']] ?? '-') ?></td>
                <td><?= $p['PRODUCT_STOCK_ALERT'] ?></td>
                <td><?= nl2br(htmlspecialchars($p['PRODUCT_ATTRIBUTES'])) ?></td>
                <td><?= nl2br(htmlspecialchars($p['PRODUCT_DESCRIPTION'])) ?></td>
                <td>
                    <?php if ($p['PRODUCT_IMAGE_PATH']): ?>
                        <img src="../<?= htmlspecialchars($p['PRODUCT_IMAGE_PATH']) ?>" width="50" height="50" style="object-fit: cover; border-radius: 6px;">
                    <?php else: ?>
                        <span class="badge badge-gray">No Image</span>
                    <?php endif; ?>
                </td>
                <td><?= $p['PRODUCT_LIKES'] ?></td>
                <td>
                    <a href="edit-product.php?id=<?= $p['PID'] ?>" class="btn btn-yellow">Edit</a>
                    <a href="delete-product.php?id=<?= $p['PID'] ?>" class="btn btn-red"
                       onclick="return confirm('Delete this product?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>

            </tbody>
        </table>
    </form>
</div>
    <div class="pagination">
        <?php $pager->html("sort=$sort&dir=$dir&search=" . urlencode($search)) ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const master = document.getElementById("select-all");
    const boxes = document.querySelectorAll("input[name='selected_ids[]']");
    if (master) {
        master.addEventListener("change", () => {
            boxes.forEach(box => box.checked = master.checked);
        });
    }
});
</script>
</body>
</html>

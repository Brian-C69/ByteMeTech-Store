<?php
require_once "../includes/base.php";
require_once "../includes/config.php";
require_once "../lib/SimplePager.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("location: ../page/login.php");
    exit;
}

if (is_post()) {
    $action = post("bulk_action");
    $selected = $_POST["selected_ids"] ?? [];

    if (!$action || empty($selected)) {
        temp("info", "Please select vouchers and a valid bulk action.");
        redirect();
    }

    $ids = implode(",", array_map("intval", $selected));

    if ($action === "activate") {
        $pdo->exec("UPDATE vouchers SET STATUS = 'active' WHERE VID IN ($ids)");
        temp("info", "Selected vouchers activated.");
    } elseif ($action === "deactivate") {
        $pdo->exec("UPDATE vouchers SET STATUS = 'inactive' WHERE VID IN ($ids)");
        temp("info", "Selected vouchers deactivated.");
    } elseif ($action === "delete") {
        $pdo->exec("DELETE FROM vouchers WHERE VID IN ($ids)");
        temp("info", "Selected vouchers deleted.");
    }

    redirect();
}

$fields = [
    'VID' => 'ID',
    'CODE' => 'Code',
    'DISCOUNT_TYPE' => 'Type',
    'DISCOUNT_VALUE' => 'Value',
    'EXPIRY_DATE' => 'Expiry',
    'USAGE_LIMIT' => 'Limit',
    'USED_COUNT' => 'Used',
    'STATUS' => 'Status',
    'CREATED_AT' => 'Created'
];

$sort = req('sort', 'VID');
$dir = req('dir', 'asc');
$page = req('page', 1);
$search = req('search', '');

if (!array_key_exists($sort, $fields)) $sort = 'VID';
if (!in_array($dir, ['asc', 'desc'])) $dir = 'asc';

$where = '';
$params = [];
if (!empty($search)) {
    $where = "WHERE CODE LIKE :s";
    $params = ['s' => "%$search%"];
}

$query = "SELECT * FROM vouchers $where ORDER BY $sort $dir";
$pager = new SimplePager($pdo, $query, $params, 10, $page);
$vouchers = $pager->result;

$bulk_actions = [
    'activate' => 'Activate',
    'deactivate' => 'Deactivate',
    'delete' => 'Delete'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Vouchers | ByteMeTech.com</title>
    <?php include '../includes/headers.php'; ?>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>

<div class="container">
    <h1>Manage Vouchers</h1>

    <?php if ($msg = temp('info')): ?>
        <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="get" class="login-form">
        <label for="search">Search</label>
        <?= html_text("search", 'placeholder="Search by code..."') ?>
        <div></div>
        <button type="submit" class="btn btn-blue">Search</button>
        <a href="voucher.php" class="btn btn-gray">Reset</a>
    </form>

    <form method="post">
        <div class="login-form">
            <label for="bulk_action">Bulk Action</label>
            <?= html_select("bulk_action", $bulk_actions, "- Select Action -") ?>
            <button type="submit" class="btn btn-green" onclick="return confirm('Apply action to selected vouchers?')">Apply</button>
        </div>

        <p><?= $pager->count ?> of <?= $pager->item_count ?> voucher(s) | Page <?= $pager->page ?> of <?= $pager->page_count ?></p>
        <a href="add-voucher.php" class="btn btn-blue">Add Voucher</a>

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
                <?php if (empty($vouchers)): ?>
                    <tr><td colspan="100%">No vouchers found.</td></tr>
                <?php else: ?>
                    <?php foreach ($vouchers as $v): ?>
                        <tr>
                            <td><input type="checkbox" name="selected_ids[]" value="<?= $v['VID'] ?>"></td>
                            <td><?= $v['VID'] ?></td>
                            <td><?= htmlspecialchars($v['CODE']) ?></td>
                            <td><?= ucfirst($v['DISCOUNT_TYPE']) ?></td>
                            <td><?= $v['DISCOUNT_TYPE'] === 'percent' ? $v['DISCOUNT_VALUE'] . '%' : 'RM' . number_format($v['DISCOUNT_VALUE'], 2) ?></td>
                            <td><?= $v['EXPIRY_DATE'] ?? '-' ?></td>
                            <td><?= $v['USAGE_LIMIT'] ?? '∞' ?></td>
                            <td><?= $v['USED_COUNT'] ?></td>
                            <td><?= ucfirst($v['STATUS']) ?></td>
                            <td><?= date("Y-m-d", strtotime($v['CREATED_AT'])) ?></td>
                            <td>
                                <a href="edit-voucher.php?id=<?= $v['VID'] ?>" class="btn btn-yellow">Edit</a>
                                <a href="delete-voucher.php?id=<?= $v['VID'] ?>" class="btn btn-red" onclick="return confirm('Delete this voucher?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </form>

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

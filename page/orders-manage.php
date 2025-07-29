<?php
require_once "../includes/base.php";
require_once "../includes/config.php";
require_once "../lib/SimplePager.php";

// Restrict to admin only
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("location: ../page/login.php");
    exit;
}

$page = req("page", 1);
$search = req("search", "");
$sort = req("sort", "CREATED_AT");
$dir = req("dir", "desc");

$valid_sorts = [
    "USERNAME", "EMAIL", "TOTAL_AMOUNT", "STATUS", "PAYMENT_STATUS", "CREATED_AT"
];
if (!in_array($sort, $valid_sorts)) $sort = "CREATED_AT";
if (!in_array($dir, ["asc", "desc"])) $dir = "desc";

$where = "";
$params = [];

if (!empty($search)) {
    $where = "WHERE u.USERNAME LIKE :s OR u.EMAIL LIKE :s";
    $params = ["s" => "%$search%"]; 
}

$query = "
    SELECT o.*, u.USERNAME, u.EMAIL,
           CONCAT(s.UNIT_NUMBER, ', ', s.STREET, ', ', s.CITY, ', ', st.STATE_NAME, ', ', c.COUNTRY_NAME) AS shipping_address
    FROM orders o
    JOIN users u ON o.UID = u.UID
    JOIN Address s ON o.SHIPPING_ADDRESS_ID = s.ADDID
    JOIN States st ON s.STATE_ID = st.STATE_ID
    JOIN Countries c ON s.COUNTRY_ID = c.COUNTRY_ID
    $where
    ORDER BY $sort $dir
";

$pager = new SimplePager($pdo, $query, $params, 10, $page);
$orders = $pager->result;

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Orders | Admin Panel</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #f8f8f8;
        }
        th.sortable a {
            color: inherit;
            text-decoration: none;
        }
    </style>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>
<div class="container">
    <h1>Manage Orders</h1>

    <?php if ($msg = temp('info')): ?>
        <div class="alert-success"> <?= htmlspecialchars($msg) ?> </div>
    <?php endif; ?>

    <form method="get" class="login-form">
        <label for="search">Search by Username or Email</label>
        <?= html_text("search", 'placeholder="Search..."') ?>
        <button type="submit" class="btn btn-blue">Search</button>
    </form>

    <p><?= $pager->count ?> of <?= $pager->item_count ?> order(s) | Page <?= $pager->page ?> of <?= $pager->page_count ?></p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th class="sortable"><a href="?<?= http_build_query(["sort" => "USERNAME", "dir" => ($sort === "USERNAME" && $dir === "asc" ? "desc" : "asc"), "search" => $search]) ?>">User</a></th>
                <th class="sortable"><a href="?<?= http_build_query(["sort" => "EMAIL", "dir" => ($sort === "EMAIL" && $dir === "asc" ? "desc" : "asc"), "search" => $search]) ?>">Email</a></th>
                <th class="sortable"><a href="?<?= http_build_query(["sort" => "TOTAL_AMOUNT", "dir" => ($sort === "TOTAL_AMOUNT" && $dir === "asc" ? "desc" : "asc"), "search" => $search]) ?>">Amount</a></th>
                <th class="sortable"><a href="?<?= http_build_query(["sort" => "STATUS", "dir" => ($sort === "STATUS" && $dir === "asc" ? "desc" : "asc"), "search" => $search]) ?>">Status</a></th>
                <th class="sortable"><a href="?<?= http_build_query(["sort" => "PAYMENT_STATUS", "dir" => ($sort === "PAYMENT_STATUS" && $dir === "asc" ? "desc" : "asc"), "search" => $search]) ?>">Payment</a></th>
                <th>Address</th>
                <th class="sortable"><a href="?<?= http_build_query(["sort" => "CREATED_AT", "dir" => ($sort === "CREATED_AT" && $dir === "asc" ? "desc" : "asc"), "search" => $search]) ?>">Date</a></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($orders)): ?>
            <tr><td colspan="100%">No orders found.</td></tr>
        <?php endif; ?>
        <?php foreach ($orders as $i => $o): ?>
            <tr>
                <td><?= ($pager->limit * ($pager->page - 1)) + $i + 1 ?></td>
                <td><?= htmlspecialchars($o["USERNAME"]) ?></td>
                <td><?= htmlspecialchars($o["EMAIL"]) ?></td>
                <td>RM<?= number_format($o["TOTAL_AMOUNT"], 2) ?></td>
                <td><?= $o["STATUS"] ?></td>
                <td><?= $o["PAYMENT_STATUS"] ?> (<?= $o["PAYMENT_METHOD"] ?>)</td>
                <td><?= htmlspecialchars($o["shipping_address"]) ?></td>
                <td><?= $o["CREATED_AT"] ?></td>
                <td><a href="view-order.php?id=<?= $o['ORDER_ID'] ?>" class="btn btn-blue">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php $pager->html("search=" . urlencode($search) . "&sort=$sort&dir=$dir") ?>
    </div>
</div>
</body>
</html>

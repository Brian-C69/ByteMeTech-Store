<?php
require_once "../includes/base.php";
require_once "../includes/config.php";
require_once "../lib/SimplePager.php";

// Restrict to Super Admins
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

// Parameters
$page = req("page", 1);
$sort = req("sort", "AID");
$dir = req("dir", "asc");
$search = req("search", "");

// Column mapping
$fields = [
    "AID" => "ID",
    "USERNAME" => "Username",
    "EMAIL" => "Email",
    "FIRST_NAME" => "First Name",
    "LAST_NAME" => "Last Name",
    "ROLE" => "Role",
    "CREATED_AT" => "Created",
    "LAST_LOGGEDIN" => "Last Login"
];

if (!array_key_exists($sort, $fields)) $sort = "AID";
if (!in_array($dir, ["asc", "desc"])) $dir = "asc";

// Search logic
$where = "";
$params = [];

if (!empty($search)) {
    $where = "WHERE USERNAME LIKE :s OR EMAIL LIKE :s";
    $params = ["s" => "%$search%"];
}

// Handle bulk delete
if (is_post() && isset($_POST["bulk_delete"]) && !empty($_POST["selected_ids"])) {
    $ids = array_map("intval", $_POST["selected_ids"]);
    $placeholders = implode(",", array_fill(0, count($ids), "?"));
    $stmt = $pdo->prepare("DELETE FROM admins WHERE AID IN ($placeholders)");
    $stmt->execute($ids);
    temp("info", "Selected admins deleted.");
    redirect();
}

// Fetch admins
$query = "SELECT * FROM admins $where ORDER BY $sort $dir";
$pager = new SimplePager($pdo, $query, $params, 10, $page);
$admins = $pager->result;

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Admins | ByteMeTech Admin</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th, table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        table th { background-color: #f4f4f4; }
    </style>
</head>
<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>

<div class="container">
    <h1>Manage Admins</h1>

    <?php if ($msg = temp('info')): ?>
        <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="get" class="login-form">
        <label>Search Username or Email</label>
        <?= html_text("search", 'placeholder="Search..."') ?>
        <button type="submit" class="btn btn-blue">Search</button>
    </form>

    <form method="post">
        <div class="login-form">
            <a href="add-admins.php" class="btn btn-green">Add Admin</a>
            <button type="submit" name="bulk_delete" class="btn btn-red" onclick="return confirm('Delete selected admins?')">Delete Selected</button>
        </div>

        <p><?= $pager->count ?> of <?= $pager->item_count ?> admin(s) | Page <?= $pager->page ?> of <?= $pager->page_count ?></p>

        <table>
            <thead>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <?php foreach ($fields as $key => $label): ?>
                    <th>
                        <a href="?sort=<?= $key ?>&dir=<?= $sort === $key && $dir === "asc" ? "desc" : "asc" ?>&search=<?= urlencode($search) ?>">
                            <?= $label ?>
                            <?= $sort === $key ? ($dir === "asc" ? "▲" : "▼") : "" ?>
                        </a>
                    </th>
                <?php endforeach; ?>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($admins)): ?>
                <tr><td colspan="100%">No admins found.</td></tr>
            <?php else: ?>
                <?php foreach ($admins as $a): ?>
                    <tr>
                        <td><input type="checkbox" name="selected_ids[]" value="<?= $a["AID"] ?>"></td>
                        <td><?= $a["AID"] ?></td>
                        <td><?= htmlspecialchars($a["USERNAME"]) ?></td>
                        <td><?= htmlspecialchars($a["EMAIL"]) ?></td>
                        <td><?= htmlspecialchars($a["FIRST_NAME"]) ?></td>
                        <td><?= htmlspecialchars($a["LAST_NAME"]) ?></td>
                        <td><?= $a["ROLE"] ?></td>
                        <td><?= $a["CREATED_AT"] ?></td>
                        <td><?= $a["LAST_LOGGEDIN"] ?? "-" ?></td>
                        <td>
                            <a href="edit-admins.php?id=<?= $a["AID"] ?>" class="btn btn-yellow">Edit</a>
                            <a href="delete-admins.php?id=<?= $a["AID"] ?>" class="btn btn-red" onclick="return confirm('Delete this admin?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </form>

    <div class="pagination">
        <?php $pager->html("sort=$sort&dir=$dir&search=" . urlencode($search)) ?>
    </div>
</div>

<script>
document.getElementById("select-all").addEventListener("change", function () {
    document.querySelectorAll("input[name='selected_ids[]']").forEach(cb => cb.checked = this.checked);
});
</script>


</body>
</html>

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
        temp("info", "Please select users and a valid bulk action.");
        redirect();
    }

    $ids = implode(",", array_map("intval", $selected));

    if ($action === "verify") {
        $pdo->exec("UPDATE users SET VERIFIED = 1 WHERE UID IN ($ids)");
        temp("info", "Selected users verified.");
    } elseif ($action === "delete") {
        $pdo->exec("DELETE FROM users WHERE UID IN ($ids)");
        temp("info", "Selected users deleted.");
    }

    redirect();
}

// Sorting Config
$fields = [
    'UID'       => 'ID',
    'USERNAME'  => 'Username',
    'EMAIL'     => 'Email',
    'FIRSTNAME' => 'First Name',
    'LASTNAME'  => 'Last Name',
    'VERIFIED'  => 'Verified',
    'BIRTHDATE' => 'birthdate',
    'CREATED'   => 'created',
    'LAST_LOGGEDIN' => 'last logged in',
    'PROFILE_PICTURE' => 'profile picture'
];

$sort = req('sort');
$dir = req('dir');
$page = req('page', 1);
$search = req('search', '');

if (!array_key_exists($sort, $fields)) $sort = 'UID';
if (!in_array($dir, ['asc', 'desc'])) $dir = 'asc';

// Bulk Action Options
$bulk_actions = [
    'verify' => 'Verify',
    'delete' => 'Delete'
];

// Search condition
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE USERNAME LIKE :s OR EMAIL LIKE :s OR FIRSTNAME LIKE :s OR LASTNAME LIKE :s";
    $params = ['s' => "%$search%"];
}

// Query with search and sorting
$query = "SELECT * FROM users $where ORDER BY $sort $dir";
$pager = new SimplePager($pdo, $query, $params, 10, $page);
$users = $pager->result;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Customers | ByteMeTech.com</title>
    <?php include '../includes/headers.php'; ?>
</head>

<body class="bg-light">
<?php include "../includes/sidebar.php"; ?>

<div class="container">
    <h1>Customers</h1>

    <?php if ($msg = temp('info')): ?>
        <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

        <!-- Search Form -->
        <form method="get" class="login-form">
            <label for="search">Search</label>
            <input type="search" id="search-input" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, email, etc..." class="input-field">
            <div></div>
            <button type="submit" class="btn btn-blue">Search</button>
            <a href="customers.php" class="btn btn-gray">Reset</a>
        </form>

    <form method="post">
        <!-- Bulk Action -->
        <div class="login-form">
            <label for="bulk_action">Bulk Action</label>
            <?= html_select("bulk_action", $bulk_actions, "- Select Action -") ?>
            <button type="submit" class="btn btn-green"
                    onclick="return confirm('Are you sure you want to apply this action?');">
                Apply
            </button>
        </div>

        <p><?= $pager->count ?> of <?= $pager->item_count ?> user(s) found | Page <?= $pager->page ?> of <?= $pager->page_count ?></p>
        
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
            <?php if (empty($users)): ?>
                <tr><td colspan="100%" style="text-align:center;">No users found.</td></tr>
            <?php endif; ?>    
            
                <?php foreach ($users as $loop_index => $u): ?>
                <tr>
                    <td><input type="checkbox" name="selected_ids[]" value="<?= $u['UID'] ?>"></td>
                    <td><?= ($pager->limit * ($pager->page - 1)) + $loop_index + 1 ?></td>
                    <td><?= htmlspecialchars($u['USERNAME']) ?></td>
                    <td><?= htmlspecialchars($u['EMAIL']) ?></td>
                    <td><?= htmlspecialchars($u['FIRSTNAME']) ?></td>
                    <td><?= htmlspecialchars($u['LASTNAME']) ?></td>
                    <td><?= $u['VERIFIED'] ? '<span class="badge badge-green">Yes</span>' : '<span class="badge badge-red">No</span>' ?></td>
                    <td>
                        <?php
                            if ($u['BIRTHDATE']) {
                                $bdate = new DateTime($u['BIRTHDATE']);
                                $today = new DateTime();
                                $age = $today->diff($bdate)->y;
                                echo htmlspecialchars($u['BIRTHDATE']) . " (Age: $age)";
                            } else {
                                echo "-";
                            }
                        ?>
                    </td>
                    <td><?= htmlspecialchars($u['CREATED']) ?></td>
                    <td><?= htmlspecialchars($u['LAST_LOGGEDIN']) ?></td>
                    <td>
                        <?php
                            $pic = $u['PROFILE_PICTURE'] ?: '../images/default-profile.png';
                            echo "<img src='" . htmlspecialchars($pic) . "' alt='Profile Picture' width='50' height='50' style='object-fit:cover; border-radius:50%;'>";
                        ?>
                    </td>
                    <td>
                        <a href="edit-customer.php?id=<?= $u['UID'] ?>" class="btn btn-yellow">Edit</a>
                        <a href="delete-customer.php?id=<?= $u['UID'] ?>" class="btn btn-red"
                           onclick="return confirm('Are you sure you want to delete this customer?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
       
    </form>
</div>

    <!-- Pagination -->
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

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    const tableBody = document.querySelector('tbody');
    const selectAll = document.getElementById("select-all");

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value;

            fetch('../ajax/search-customers.php?search=' + encodeURIComponent(query))
                .then(res => res.text())
                .then(html => {
                    tableBody.innerHTML = html;
                });
        });
    }

    if (selectAll) {
        selectAll.addEventListener("change", () => {
            document.querySelectorAll("input[name='selected_ids[]']").forEach(box => box.checked = selectAll.checked);
        });
    }
});
</script>

</body>
</html>

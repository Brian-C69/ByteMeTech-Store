<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["user_id"];

// Handle batch delete if submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_selected"]) && !empty($_POST["selected_addresses"])) {
    $ids = array_map('intval', $_POST["selected_addresses"]);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("DELETE FROM Address WHERE UID = ? AND ADDID IN ($placeholders)");
    $stmt->execute(array_merge([$uid], $ids));
    $_SESSION["success_message"] = "Selected addresses deleted.";
    header("Location: address.php");
    exit;
}

// Fetch addresses
$stmt = $pdo->prepare("SELECT a.*, s.STATE_NAME, c.COUNTRY_NAME
                       FROM Address a
                       LEFT JOIN States s ON a.STATE_ID = s.STATE_ID
                       LEFT JOIN Countries c ON a.COUNTRY_ID = c.COUNTRY_ID
                       WHERE a.UID = :uid");
$stmt->execute(["uid" => $uid]);
$addresses = $stmt->fetchAll();

include "../includes/headers.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Addresses | ByteMeTech</title>
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
        .actions {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body class="bg-light">
<?php include "../includes/navbar.php"; ?>
<div class="container bg-dark">
    <h1 class="white-text">My Addresses</h1>
</div>
<div class="container">
    <?php if (isset($_SESSION["success_message"])): ?>
        <div class="alert-success"><?= htmlspecialchars($_SESSION["success_message"]) ?></div>
        <?php unset($_SESSION["success_message"]); ?>
    <?php endif; ?>

    <form method="post" action="address.php">
        <div style="margin-bottom: 15px; display: flex; justify-content: space-between;">
            <a href="add-address.php" class="btn btn-blue">Add New Address</a>
            <button type="submit" name="delete_selected" class="btn btn-red" style="height: 40px" onclick="return confirm('Delete selected addresses?')">Delete Selected</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>Type</th>
                    <th>Full Address</th>
                    <th>State</th>
                    <th>Country</th>
                    <th>Default</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($addresses)): ?>
                <tr><td colspan="7">No addresses found.</td></tr>
            <?php else: ?>
                <?php foreach ($addresses as $address): ?>
                    <tr>
                        <td><input type="checkbox" class="item-checkbox" name="selected_addresses[]" value="<?= $address["ADDID"] ?>"></td>
                        <td><?= htmlspecialchars($address["TYPE"]) ?></td>
                        <td><?= htmlspecialchars($address["UNIT_NUMBER"] . ", " . $address["STREET"] . ", " . $address["CITY"]) ?></td>
                        <td><?= htmlspecialchars($address["STATE_NAME"] ?? '-') ?></td>
                        <td><?= htmlspecialchars($address["COUNTRY_NAME"] ?? '-') ?></td>
                        <td><?= $address["DEFAULT_ADDRESS"] ? 'Yes' : 'No' ?></td>
                        <td class="actions">
                            <a href="edit-address.php?id=<?= $address["ADDID"] ?>" class="btn btn-green">Edit</a>
                            <form method="post" action="delete-address.php" style="height: 20px" onsubmit="return confirm('Delete this address?')">
                                <input type="hidden" name="addid" value="<?= $address["ADDID"] ?>">
                                <button type="submit" style="height: 40px" class="btn btn-red">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>

<script>
    document.getElementById("select-all").addEventListener("change", function () {
        document.querySelectorAll(".item-checkbox").forEach(cb => cb.checked = this.checked);
    });
</script>

<?php include "../includes/footer.php"; ?>
</body>
</html>

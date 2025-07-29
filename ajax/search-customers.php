<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    http_response_code(403);
    exit;
}

$search = $_GET['search'] ?? '';

$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE USERNAME LIKE :s OR EMAIL LIKE :s OR FIRSTNAME LIKE :s OR LASTNAME LIKE :s";
    $params = ['s' => "%$search%"];
}

$query = "SELECT * FROM users $where ORDER BY UID DESC LIMIT 20";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

if (empty($users)) {
    echo '<tr><td colspan="100%" style="text-align:center;">No users found.</td></tr>';
    exit;
}

foreach ($users as $index => $u):
?>
<tr>
    <td><input type="checkbox" name="selected_ids[]" value="<?= $u['UID'] ?>"></td>
    <td><?= $index + 1 ?></td>
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
        <a href="edit-customer.php?id=<?= $u['UID'] ?>" class="btn btn-yellow">Edit</a>
        <a href="delete-customer.php?id=<?= $u['UID'] ?>" class="btn btn-red"
           onclick="return confirm('Are you sure you want to delete this customer?');">Delete</a>
    </td>
</tr>
<?php endforeach; ?>

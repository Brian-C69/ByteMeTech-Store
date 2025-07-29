<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

$pid = (int) ($_POST["pid"] ?? 0);
if ($pid < 1) {
    header("Location: cart.php");
    exit;
}

if (isset($_SESSION["user_id"])) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE UID = :uid AND PID = :pid");
    $stmt->execute([
        "uid" => $_SESSION["user_id"],
        "pid" => $pid
    ]);
} else {
    if (isset($_SESSION["cart"][$pid])) {
        unset($_SESSION["cart"][$pid]);
    }
}

header("Location: cart.php");
exit;

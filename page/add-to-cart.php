<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

$pid = (int) ($_POST['pid'] ?? 0);
$qty = max(1, (int) ($_POST['qty'] ?? 1));

if (!$pid) {
    temp("info", "Invalid product.");
    header("Location: product.php");
    exit;
}

if (isset($_SESSION["user_id"])) {
    // Permanent cart
    $uid = $_SESSION["user_id"];
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE UID = :uid AND PID = :pid");
    $stmt->execute(["uid" => $uid, "pid" => $pid]);

    if ($row = $stmt->fetch()) {
        $pdo->prepare("UPDATE cart SET QUANTITY = QUANTITY + :qty WHERE UID = :uid AND PID = :pid")
            ->execute(["qty" => $qty, "uid" => $uid, "pid" => $pid]);
    } else {
        $pdo->prepare("INSERT INTO cart (UID, PID, QUANTITY) VALUES (:uid, :pid, :qty)")
            ->execute(["uid" => $uid, "pid" => $pid, "qty" => $qty]);
    }

    temp("info", "Item added to cart.");
} else {
    // Session cart
    if (!isset($_SESSION["cart"])) $_SESSION["cart"] = [];

    if (isset($_SESSION["cart"][$pid])) {
        $_SESSION["cart"][$pid] += $qty;
    } else {
        $_SESSION["cart"][$pid] = $qty;
    }

    temp("info", "Item added to cart (session).");
}

header("Location: product.php");
exit;

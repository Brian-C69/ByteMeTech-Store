<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    temp("info", "Please login to like this product.");
    header("Location: ../page/login.php");
    exit;
}

$uid = $_SESSION['user_id'];
$pid = (int) ($_POST['pid'] ?? 0);
if (!$pid) {
    temp("info", "Invalid product.");
    header("Location: product.php");
    exit;
}

// Check if the user already liked the product
$stmt = $pdo->prepare("SELECT * FROM product_likes WHERE UID = :uid AND PID = :pid");
$stmt->execute(['uid' => $uid, 'pid' => $pid]);
$existing = $stmt->fetch();

if ($existing) {
    // User already liked it, so unlike
    $stmt = $pdo->prepare("DELETE FROM product_likes WHERE UID = :uid AND PID = :pid");
    $stmt->execute(['uid' => $uid, 'pid' => $pid]);

    $pdo->prepare("UPDATE products SET PRODUCT_LIKES = PRODUCT_LIKES - 1 WHERE PID = :pid")
        ->execute(['pid' => $pid]);

    temp("info", "You unliked this product.");
} else {
    // Like it
    $stmt = $pdo->prepare("INSERT INTO product_likes (UID, PID) VALUES (:uid, :pid)");
    $stmt->execute(['uid' => $uid, 'pid' => $pid]);

    $pdo->prepare("UPDATE products SET PRODUCT_LIKES = PRODUCT_LIKES + 1 WHERE PID = :pid")
        ->execute(['pid' => $pid]);

    temp("info", "You liked this product.");
}

header("Location: product-detail.php?id=$pid");
exit; ?>

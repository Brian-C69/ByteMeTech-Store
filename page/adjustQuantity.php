<?php
require_once "../includes/base.php";

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Only POST allowed");
}

$qty = (int) ($_POST['qty'] ?? 1);
$change = (int) ($_POST['change'] ?? 0);

// Ensure minimum of 1
$qty = max(1, $qty + $change);

// Return JSON
header('Content-Type: application/json');
echo json_encode(['qty' => $qty]);

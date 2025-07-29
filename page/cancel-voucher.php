<?php
require_once "../includes/base.php";

// Remove all voucher session data
unset($_SESSION["voucher_code"]);
unset($_SESSION["voucher_discount"]);
unset($_SESSION["voucher"]);

$_SESSION["success_message"] = "Voucher has been cancelled.";
header("Location: checkout.php");
exit;

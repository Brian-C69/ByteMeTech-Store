<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Admin-only access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "Access denied.";
    header("Location: ../page/login.php");
    exit;
}

$aid = (int) ($_GET["id"] ?? 0);

// Prevent self-deletion or invalid ID
if ($aid <= 0) {
    $_SESSION["error_message"] = "Invalid admin ID.";
    header("Location: admins.php");
    exit;
}

// Optional: prevent deleting the only super admin
$stmt_check = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE ROLE = 'Super Admin'");
$stmt_check->execute();
$super_admin_count = $stmt_check->fetchColumn();

// Get role of the admin to delete
$stmt_role = $pdo->prepare("SELECT ROLE FROM admins WHERE AID = :aid");
$stmt_role->execute(["aid" => $aid]);
$role_to_delete = $stmt_role->fetchColumn();

if (!$role_to_delete) {
    $_SESSION["error_message"] = "Admin not found.";
    header("Location: admins.php");
    exit;
}

if ($role_to_delete === "Super Admin" && $super_admin_count <= 1) {
    $_SESSION["error_message"] = "Cannot delete the only Super Admin.";
    header("Location: admins.php");
    exit;
}

// Perform delete
$stmt = $pdo->prepare("DELETE FROM admins WHERE AID = :aid");
$stmt->execute(["aid" => $aid]);

$_SESSION["success_message"] = "Admin deleted successfully.";
header("Location: admins.php");
exit;

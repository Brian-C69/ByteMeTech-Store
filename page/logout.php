<?php
session_start();

// Determine redirection based on role before clearing session
$redirect_target = "login.php"; // Default

if (isset($_SESSION["role"])) {
    if ($_SESSION["role"] === "admin") {
        $redirect_target = "admin.php";
    } else {
        $redirect_target = "login.php";
    }
}

// Clear all session variables
$_SESSION = [];

// Destroy the session completely
session_destroy();

// Delete the "remember_me" cookie if it exists
if (isset($_COOKIE["remember_me"])) {
    setcookie("remember_me", "", time() - 3600, "/");
}

// Start a new session to flash success message
session_start();
$_SESSION["success_message"] = "Logout successful. You have been logged out.";

// Redirect based on role
header("Location: $redirect_target");
exit;

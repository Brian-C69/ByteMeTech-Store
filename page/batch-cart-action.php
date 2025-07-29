<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

$is_logged_in = isset($_SESSION["user_id"]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for batch remove or checkout
    if (isset($_POST["action"])) {
        $action = $_POST["action"];

        // Handle individual item removal like: remove_single_5
        if (strpos($action, "remove_single_") === 0) {
            $pid = (int) str_replace("remove_single_", "", $action);

            if ($is_logged_in) {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE UID = :uid AND PID = :pid");
                $stmt->execute(["uid" => $_SESSION["user_id"], "pid" => $pid]);
            } else {
                unset($_SESSION["cart"][$pid]);
            }

            $_SESSION["success_message"] = "Item removed from cart.";
            header("Location: cart.php");
            exit;
        }

        // Handle batch remove or checkout
        if (isset($_POST["selected_items"]) && is_array($_POST["selected_items"])) {
            $selected = array_map("intval", $_POST["selected_items"]);

            if ($action === "remove") {
                if ($is_logged_in) {
                    $uid = $_SESSION["user_id"];
                    $placeholders = implode(",", array_fill(0, count($selected), "?"));
                    $stmt = $pdo->prepare("DELETE FROM cart WHERE UID = ? AND PID IN ($placeholders)");
                    $stmt->execute(array_merge([$uid], $selected));
                } else {
                    foreach ($selected as $pid) {
                        unset($_SESSION["cart"][$pid]);
                    }
                }

                $_SESSION["success_message"] = "Selected items removed from cart.";
                header("Location: cart.php");
                exit;
            }

            if ($action === "checkout") {
                $_SESSION["checkout_items"] = $selected;
                header("Location: checkout.php");
                exit;
            }
        }
    }
}

// If no valid action
header("Location: cart.php");
exit;

<?php
$adminRole = $_SESSION["admin_role"] ?? "";
?>

<div class="sidebar">
    <div class="logo">
        <img src="../images/ByteMe-Logo.png" alt="ByteMeTech Logo">
    </div>
    <nav>
        <a href="dashboard.php">Dashboard</a>

        <?php if ($adminRole === "Super Admin" || $adminRole === "Sales" || $adminRole === "Moderator"): ?>
            <a href="customers.php">Customers</a>
        <?php endif; ?>

        <?php if ($adminRole === "Super Admin" || $adminRole === "Logistics" || $adminRole === "Moderator"): ?>
            <a href="product_manage.php">Products</a>
        <?php endif; ?>

        <?php if ($adminRole === "Super Admin" || $adminRole === "Marketing"): ?>
            <a href="voucher.php">Vouchers</a>
        <?php endif; ?>

        <?php if ($adminRole === "Super Admin" || $adminRole === "Logistics"): ?>
            <a href="orders-manage.php">Orders</a>
        <?php endif; ?>

        <?php if ($adminRole === "Super Admin" || $adminRole === "Sales"): ?>
            <a href="reports.php">Reports</a>
        <?php endif; ?>

        <?php if ($adminRole === "Super Admin"): ?>
            <a href="admins.php">Admins</a>
        <?php endif; ?>

        <?php if ($adminRole === "Super Admin" || $adminRole === "Customer Support"): ?>
            <a href="customer-support.php">Customer Support</a>
        <?php endif; ?>

        <br>
        <a href="admin-profile.php">My Profile</a>
        <a href="logout.php" style="color: #e74c3c;">Logout</a>
    </nav>
</div>

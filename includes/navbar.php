<?php
require_once "../includes/base.php";
require_once "../includes/config.php";

// Default profile image
$default_profile = "../images/default-profile.png";
$profile_picture = $default_profile;

// Check if the user is logged in
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // Admins always use the default profile image
    $profile_picture = $default_profile;
} elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
    // Fetch user's profile picture from the users table
    $stmt = $pdo->prepare("SELECT PROFILE_PICTURE FROM users WHERE UID = :uid");
    $stmt->bindParam(":uid", $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch();

    if (!empty($user["PROFILE_PICTURE"])) {
        $profile_picture = $user["PROFILE_PICTURE"];
    }

    $_SESSION["profile_picture"] = $profile_picture;
}
?>
<?php
$cart_count = 0;

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user') {
    $stmt = $pdo->prepare("SELECT SUM(QUANTITY) AS total FROM cart WHERE UID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetchColumn() ?? 0;
}
?>
<nav class="container navbar">
    <div class="logo">
        <a href="index.php">
            <img src="../images/ByteMe-Logo.png" alt="ByteMeTech Logo">
        </a>
        <p class="tagline white-text">Bite into the best tech deals</p>
    </div>

    <ul class="nav-links">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li><a href="admin-dashboard.php">Admin Dashboard</a></li>
            <li><a href="manage-users.php">Manage Users</a></li>
            <li><a href="manage-products.php">Manage Products</a></li>
            <li><a href="orders.php">Orders</a></li>
        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
            <li><a href="welcome.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="product.php">Products</a></li>
        <?php else: ?>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="product.php">Products</a></li>
            <li><a href="cart.php">Cart</a></li>
        <?php endif; ?>
    </ul>

    <div class="navbar-right">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
            <a href="cart.php" class="cart-link">
                🛒 Cart <span class="cart-count"><?= $cart_count ?></span>
            </a>
        <?php endif; ?>

        <?php if (isset($_SESSION['role'])): ?>
            <div class="profile-section">
                <img src="<?= htmlspecialchars($profile_picture) ?>?t=<?= time(); ?>" 
                     class="profile-icon" id="profile-icon" alt="Profile">
                <div class="profile-dropdown" id="profile-dropdown">
                    <ul class="dropdown-menu">
                        <li><a href="profile.php">My Profile</a></li>
                        <li><a href="orders.php">My Orders</a></li>
                        <li><a href="logout.php">Sign Out</a></li>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php" class="btn btn-blue">Login</a>
        <?php endif; ?>
        <div class="menu-toggle">☰</div>
    </div>


</nav>

<div>
    <!-- Chat Bubble -->
    <div id="chat-bubble">
        <img src="../images/chat-icon.png" alt="Chat" />
    </div>

    <!-- Chat Window -->
    <div id="chat-window" style="display: none;">
        <div id="chat-header">Support Chat <span id="close-chat">×</span></div>
        <div id="chat-messages"></div>
        <form id="chat-form">
            <input type="text" id="chat-input" placeholder="Type a message..." autocomplete="off" />
            <button type="submit">Send</button>
        </form>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const profileIcon = document.getElementById("profile-icon");
        const profileDropdown = document.getElementById("profile-dropdown");

        if (profileIcon) {
            profileIcon.addEventListener("click", function (event) {
                event.stopPropagation();
                profileDropdown.classList.toggle("active");
            });

            document.addEventListener("click", function (event) {
                if (!profileDropdown.contains(event.target)) {
                    profileDropdown.classList.remove("active");
                }
            });
        }
    });
</script>

<script>
    document.getElementById("chat-bubble").onclick = () => {
        document.getElementById("chat-window").style.display = "block";
    };
    document.getElementById("close-chat").onclick = () => {
        document.getElementById("chat-window").style.display = "none";
    };

    document.getElementById("chat-form").addEventListener("submit", async function (e) {
        e.preventDefault();
        const input = document.getElementById("chat-input");
        const message = input.value.trim();
        if (!message)
            return;

        const response = await fetch("submit-chat.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({message})
        });
        
        if (response.status === 401) {
        alert("Please login to use live chat.");
        window.location.href = "login.php";
        return;
    }

        if (response.ok) {
            input.value = "";
            input.focus();
            loadMessages();
        }
    });

async function loadMessages() {
    const res = await fetch("get-chat.php");

    if (res.status === 401) {
        document.getElementById("chat-messages").innerHTML = `
            <p style="padding: 10px; color: red;">Please <a href='login.php'>login</a> to access live chat support.</p>
        `;
        return;
    }

    const data = await res.json();
    const chatBox = document.getElementById("chat-messages");
    chatBox.innerHTML = "";

    data.forEach(msg => {
        const time = new Date(msg.TIMESTAMP).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });

        const senderColor = msg.SENDER === 'User' ? 'green' : 'blue';
        const senderLabel = msg.SENDER === 'User' ? 'You' : 'Admin';

        const div = document.createElement("div");
        div.innerHTML = `
            <div style="text-align: center; font-size: 0.75em; color: #999;">${time}</div>
            <div>
                <strong><span style="color: ${senderColor};">${senderLabel}:</span></strong> ${msg.MESSAGE}
            </div>
        `;
        chatBox.appendChild(div);
    });

    chatBox.scrollTop = chatBox.scrollHeight;
}



    setInterval(loadMessages, 1000);
    loadMessages();
</script>


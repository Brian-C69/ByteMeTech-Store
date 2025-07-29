<?php
// Database Configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ByteMeTech');

try {
    // Establish a PDO Connection
    $pdo = new PDO(
        "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD
    );

    // Set PDO attributes for better error handling & fetching
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Fetch as associative array

} catch (PDOException $e) {
    // Handle connection error
    die("ERROR: Could not connect. " . $e->getMessage());
}

define('STRIPE_SECRET_KEY', 'sk_test_51RGYd0RMeNadig1hs416j0flYul1L9o8jKOSXcefF8xFAZr8dIY1V0sjBHiT090xjd4r1XLItpPGhKvVQs6QUHBr00PbyNd60r');  // Secret key
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_51RGYd0RMeNadig1hCVStbViDNu2wopAmo9hfsbLTAShv24FCoxVA9jLTAW53TpHUrq5Uk5b1ftx1nhxDRwJ6SMF100VFgmDp6B');  // Public key
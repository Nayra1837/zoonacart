<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$dbname = 'zoonacosmetics';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Dynamic Table Check
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(255) PRIMARY KEY,
        setting_value TEXT
    )");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Global configurations
session_start();
define('BASE_URL', '/zoonacart/');
define('ASSETS_URL', BASE_URL . 'assets/');
?>

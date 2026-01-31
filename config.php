<?php
// Environment Detection
$is_local = (php_sapi_name() === 'cli') || 
            (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) || 
            (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost');

if ($is_local) {
    // LOCAL XAMPP SETTINGS
    $host = 'localhost';
    $user = 'root';
    $pass = ''; 
    $dbname = 'zoonacosmetics';
} else {
    // LIVE SERVER SETTINGS
    $host = 'sql305.iceiy.com';
    $user = 'icei_41023241';
    $pass = 'VEBeZ8c5UPzA';
    $dbname = 'icei_41023241_zoonacart_db';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Dynamic Table Check (Only for Local to auto-setup)
    if ($is_local) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            setting_key VARCHAR(255) PRIMARY KEY,
            setting_value TEXT
        )");
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Global configurations
session_start();

// Robust BASE_URL Detection
if (!defined('BASE_URL')) {
    $project_root = dirname(__FILE__); // Points to the zoonacart folder
    $doc_root = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']);
    $base_dir = str_replace($doc_root, '', $project_root);
    $base_dir = str_replace(DIRECTORY_SEPARATOR, '/', $base_dir);
    $base_dir = '/' . trim($base_dir, '/') . '/';
    // If base_dir is just //, make it /
    if($base_dir === '//') $base_dir = '/';
    define('BASE_URL', $base_dir);
}

define('ASSETS_URL', BASE_URL . 'assets/');

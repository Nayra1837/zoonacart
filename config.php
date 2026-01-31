<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Robust BASE_URL Detection
if (!defined('BASE_URL')) {
    $project_root = dirname(__FILE__);
    $doc_root = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']);
    $base_dir = str_replace($doc_root, '', $project_root);
    $base_dir = str_replace(DIRECTORY_SEPARATOR, '/', $base_dir);
    $base_dir = '/' . trim($base_dir, '/') . '/';
    if($base_dir === '//') $base_dir = '/';
    define('BASE_URL', $base_dir);
}

// MASTER ACCESS LOCK
$current_page = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['app_unlocked']) && $current_page !== 'unlock.php') {
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        http_response_code(403);
        die(json_encode(["error" => "System Locked. Access Denied."]));
    }
    header("Location: " . BASE_URL . "unlock.php");
    exit();
}

// Environment Detection
$is_local = (php_sapi_name() === 'cli') || 
            (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) || 
            (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost');

if ($is_local) {
    $host = 'localhost';
    $user = 'root';
    $pass = ''; 
    $dbname = 'zoonacosmetics';
} else {
    $host = 'sql305.iceiy.com';
    $user = 'icei_41023241';
    $pass = 'VEBeZ8c5UPzA';
    $dbname = 'icei_41023241_zoonacart_db';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if ($is_local) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            setting_key VARCHAR(255) PRIMARY KEY,
            setting_value TEXT
        )");
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

define('ASSETS_URL', BASE_URL . 'assets/');
?>

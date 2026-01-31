<?php
// live_api_test.php
// Standalone API to test Product fetching bypassing config.php includes
// Use this to debug if api/main.php is failing due to 'require' paths.

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$response = [];

// 1. Direct DB Connection (No Config File)
$host = 'sql305.iceiy.com';
$user = 'icei_41023241';
$pass = 'VEBeZ8c5UPzA';
$dbname = 'icei_41023241_zoonacart_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $response['db_status'] = 'Connected';
} catch (PDOException $e) {
    echo json_encode(['error' => 'DB Connection Failed: ' . $e->getMessage()]);
    exit;
}

// 2. Fetch Products
try {
    $stmt = $pdo->query("SELECT id, name, price, image, category FROM products LIMIT 5");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['product_count'] = count($products);
    $response['products'] = $products;
    
    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['error' => 'Query Failed: ' . $e->getMessage()]);
}
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    echo "Testing Database Connection...\n";
    
    // Simulate get_products logic
    $sql = "
            SELECT p.*, GROUP_CONCAT(pi.image_path) as images 
            FROM products p 
            LEFT JOIN product_images pi ON p.id = pi.product_id 
            GROUP BY p.id 
            ORDER BY p.created_at DESC
        ";
    
    echo "Running Query:\n$sql\n";
    
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Products fetched: " . count($products) . "\n";
    
    // Test processing
    foreach ($products as &$product) {
        if ($product['images']) {
            $product['images'] = array_unique(explode(',', $product['images']));
        } else {
            $product['images'] = [];
        }
        if ($product['image'] && !in_array($product['image'], $product['images'])) {
            array_unshift($product['images'], $product['image']);
        }
    }
    
    echo "JSON Encode Test:\n";
    $json = json_encode($products);
    if ($json === false) {
        echo "JSON Error: " . json_last_error_msg() . "\n";
    } else {
        echo "JSON valid (Snippet): " . substr($json, 0, 100) . "...\n";
    }

} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
?>

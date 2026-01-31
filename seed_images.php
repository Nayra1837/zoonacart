<?php
require_once 'config.php';

// Get top 8 products
$stmt = $pdo->query("SELECT id, name FROM products ORDER BY created_at DESC LIMIT 8");
$products = $stmt->fetchAll();

$imagesPool = [
    'matte_lipstick.png', 
    'lipstick_pink.png', 
    'lipstick_nude.png', 
    'liquid_eyeliner.png', 
    'volumizing_mascara.png', 
    'eyeshadow_palette.png', 
    'blush_compact.png', 
    'foundation_bottle.png', 
    'perfume_golden.png', 
    'skincare_cream.png'
];

echo "Seeding extra images for top 8 products...\n";

$insertStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, 0)");

foreach ($products as $p) {
    echo "Processing '{$p['name']}'...\n";
    
    // Add 2 random extra images
    for ($i = 0; $i < 2; $i++) {
        $randomImg = $imagesPool[array_rand($imagesPool)];
        try {
            $insertStmt->execute([$p['id'], $randomImg]);
        } catch (Exception $e) {
            // Ignore duplication or other minor errors
        }
    }
}

echo "Done! Refresh the page to see changes (after API update).";
?>

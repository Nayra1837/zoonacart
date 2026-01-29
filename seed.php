<?php
require_once 'config.php';

// Disable foreign key checks and clear existing products
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    $tables = ['order_items', 'products'];
    foreach ($tables as $t) {
        $stmt = $pdo->query("SHOW TABLES LIKE '" . $t . "'");
        if ($stmt && $stmt->rowCount() > 0) {
            $pdo->exec("TRUNCATE TABLE `" . $t . "`");
        } else {
            // table missing; skip truncation
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
} catch (Exception $e) {
    echo "Warning (seed): " . $e->getMessage() . "\n";
}

$products = [
    [
        'name' => 'Velvet Red Lipstick',
        'price' => 1299.00,
        'description' => 'A luxury red velvet lipstick with a matte finish and gold-infused moisture.',
        'image' => 'lipstick.png',
        'stock' => 50,
        'category' => 'Makeup'
    ],
    [
        'name' => 'Midnight Bloom Perfume',
        'price' => 4500.00,
        'description' => 'An enchanting blend of night-blooming jasmine and dark vanilla.',
        'image' => 'perfume.png',
        'stock' => 30,
        'category' => 'Fragrance'
    ],
    [
        'name' => '24K Gold Face Serum',
        'price' => 2499.00,
        'description' => 'Pure 24K gold flakes in a hydrating serum for a radiant, youthful glow.',
        'image' => 'serum.png',
        'stock' => 25,
        'category' => 'Skincare'
    ],
    [
        'name' => 'Rose Quartz Palette',
        'price' => 1899.00,
        'description' => 'Highly pigmented eyeshadows inspired by the soft hues of rose quartz.',
        'image' => 'palette.png',
        'stock' => 40,
        'category' => 'Makeup'
    ],
    [
        'name' => 'Botanical Glow Primer',
        'price' => 999.00,
        'description' => 'Infused with botanical extracts to prep and brighten your skin.',
        'image' => 'primer.png',
        'stock' => 60,
        'category' => 'Skincare'
    ],
    [
        'name' => 'Silk Finish Foundation',
        'price' => 1599.00,
        'description' => 'Weightless, buildable coverage with a flawless silk-like finish.',
        'image' => 'foundation.png',
        'stock' => 45,
        'category' => 'Makeup'
    ],
    [
        'name' => 'Lavender Night Cream',
        'price' => 1250.00,
        'description' => 'Calming lavender oil and shea butter for overnight skin rejuvenation.',
        'image' => 'night_cream.png',
        'stock' => 35,
        'category' => 'Skincare'
    ],
    [
        'name' => 'Amber Wood Cologne',
        'price' => 3800.00,
        'description' => 'A sophisticated, woody fragrance with deep notes of amber and oak.',
        'image' => 'cologne.png',
        'stock' => 20,
        'category' => 'Fragrance'
    ]
];

foreach ($products as $p) {
    $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image, stock, category) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$p['name'], $p['price'], $p['description'], $p['image'], $p['stock'], $p['category']]);
}

echo "Database seeded with 8 luxury products successfully!";
?>

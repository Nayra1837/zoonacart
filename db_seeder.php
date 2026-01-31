<?php
require_once 'config.php';

echo "Starting product seeding...\n";

// Clear existing products to avoid duplicates and ensure variety
// Disable FK checks to allow truncation
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("TRUNCATE TABLE products");
$pdo->exec("TRUNCATE TABLE order_items"); // Clear dependent data too for clean slate
$pdo->exec("TRUNCATE TABLE orders");      // Clear dependent data too for clean slate
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
echo "Cleared existing products.\n";

$adjectives = ['Velvet', 'Midnight', 'Radiant', 'Sunset', 'Golden', 'Mystic', 'Crystal', 'Silk', 'Diamond', 'Rose', 'Eternal', 'Luminous', 'Electric', 'Nude', 'Vibrant', 'Sheer', 'Matte', 'Glossy', 'Satin', 'Sparkling', 'Opulent', 'Divine', 'Pure', 'Royal'];
$nouns = ['Glow', 'Touch', 'Essence', 'Allure', 'Charm', 'Mist', 'Shade', 'Temptation', 'Secret', 'Dream', 'Fusion', 'Elixir', 'Bloom', 'Sparkle', 'Finish', 'Miracle', 'Aura', 'Veil', 'Magic', 'Muse', 'Fantasia', 'Whisper'];

// Enhanced product types with multiple image variants
$types = [
    [
        'category' => 'Lips',
        'base_name' => 'Lipstick',
        'images' => ['matte_lipstick.png', 'lipstick_pink.png', 'lipstick_nude.png'], // 3 Variants
        'price_range' => [15, 45],
        'descriptions' => [
            'Long-wearing color with a comfortable creamy finish.',
            'Intense pigmentation for a statement lip look.',
            'Hydrating formula enriched with vitamin E.',
            'Soft matte finish that never feels dry.',
            'A luxurious lipstick that glides on effortlessy.'
        ]
    ],
    [
        'category' => 'Eyes',
        'base_name' => 'Liquid Eyeliner',
        'images' => ['liquid_eyeliner.png'],
        'price_range' => [12, 30],
        'descriptions' => [
            'Precision tip for perfect wing application.',
            'Waterproof formula that lasts all day.',
            'Deepest black pigments for dramatic definition.'
        ]
    ],
    [
        'category' => 'Eyes',
        'base_name' => 'Volume Mascara',
        'images' => ['volumizing_mascara.png'],
        'price_range' => [18, 35],
        'descriptions' => [
            'Instant volume without clumping.',
            'Lash-extending fibers for maximum length.',
            'Curling formula that opens up the eyes.'
        ]
    ],
    [
        'category' => 'Eyes',
        'base_name' => 'Shadow Palette',
        'images' => ['eyeshadow_palette.png'],
        'price_range' => [35, 85],
        'descriptions' => [
            'Highly mixable shades for endless looks.',
            'Buttery soft texture with high payoff.',
            'A mix of mattes and shimmers for day to night.'
        ]
    ],
    [
        'category' => 'Face',
        'base_name' => 'Blush Compact',
        'images' => ['blush_compact.png'],
        'price_range' => [20, 40],
        'descriptions' => [
            'Natural flush of color for all skin tones.',
            'Silky smooth powder that blends effortlessly.',
            'Buildable pigmentation for custom intensity.'
        ]
    ],
    [
        'category' => 'Face',
        'base_name' => 'Foundation',
        'images' => ['foundation_bottle.png'],
        'price_range' => [40, 90],
        'descriptions' => [
            'Full coverage liquid foundation with a natural finish.',
            'Lightweight formula that lasts 24 hours.',
            'Infused with skincare benefits for a radiant complexion.'
        ]
    ],
    [
        'category' => 'Fragrance',
        'base_name' => 'Eau de Parfum',
        'images' => ['perfume_golden.png'],
        'price_range' => [80, 200],
        'descriptions' => [
            'An enchanting blend of floral and musk notes.',
            'A sophisticated scent that captures the essence of luxury.',
            'Long-lasting fragrance in a collectible crystal bottle.'
        ]
    ],
    [
        'category' => 'Skincare',
        'base_name' => 'Restorative Cream',
        'images' => ['skincare_cream.png'],
        'price_range' => [50, 120],
        'descriptions' => [
            'Rich moisturizing cream for overnight repair.',
            'Deeply hydrating formula for plump, youthful skin.',
            'Luxury face cream with anti-aging botanicals.'
        ]
    ]
];

// Generate 120 products
$count = 0;
$target = 120;

$stmt = $pdo->prepare("INSERT INTO products (name, price, description, image, stock, category) VALUES (?, ?, ?, ?, ?, ?)");

for ($i = 0; $i < $target; $i++) {
    // Pick a random product type
    $type = $types[array_rand($types)];
    
    // Pick a random image from the available variants for this type
    $image = $type['images'][array_rand($type['images'])];

    // Generate Name: Adjective + Noun + BaseName (e.g., "Radiant Glow Lipstick")
    $adj = $adjectives[array_rand($adjectives)];
    $noun = $nouns[array_rand($nouns)];
    $name = "$adj $noun {$type['base_name']}";
    
    // Generate Price
    $price = rand($type['price_range'][0] * 10, $type['price_range'][1] * 10) / 10; // Round to 1 decimal place randomly
    
    // Description
    $desc = $type['descriptions'][array_rand($type['descriptions'])];
    
    // Stock
    $stock = rand(10, 100);
    
    try {
        $stmt->execute([
            $name,
            $price,
            $desc,
            $image,
            $stock,
            $type['category']
        ]);
        $count++;
    } catch (Exception $e) {
        // echo "Failed to insert $name: " . $e->getMessage() . "\n";
    }
}

echo "Successfully re-seeded with $count diverse products!\n";
?>

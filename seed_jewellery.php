<?php
require_once 'config.php';

$adjectives = ['Royal', 'Elegant', 'Dazzling', 'Vintage', 'Modern', 'Classic', 'Radiant', 'Sparkling', 'Exquisite', 'Luxury'];
$materials = ['Gold', 'Silver', 'Rose Gold', 'Platinum', 'White Gold'];
$stones = ['Diamond', 'Ruby', 'Emerald', 'Sapphire', 'Pearl', 'Crystal', 'Topaz'];
$types = ['Necklace', 'Ring', 'Earrings', 'Bracelet', 'Pendant', 'Anklet', 'Choker', 'Bangle'];

$count = 0;
echo "Starting seeding...\n";

for ($i = 0; $i < 100; $i++) {
    $adj = $adjectives[array_rand($adjectives)];
    $mat = $materials[array_rand($materials)];
    $stone = $stones[array_rand($stones)];
    $type = $types[array_rand($types)];
    
    $name = "$adj $mat $stone $type";
    $price = rand(500, 50000); // 500 to 50,000 INR
    $stock = rand(5, 50);
    $description = "Experience the elegance of this $name. Handcrafted with precision using high-quality $mat and adorned with beautiful $stone. Perfect for weddings, parties, or everyday luxury.";
    
    $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image, category, stock) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $name,
        $price,
        $description,
        'jewellery_placeholder.png', // Using the generated placeholder
        'Jewellery',
        $stock
    ]);
    $count++;
}

echo "Successfully added $count jewellery products!";
?>

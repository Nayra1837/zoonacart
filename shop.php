<?php
// LIVE DEBUG MODE (Isse Error 500 ki jagah asli error dikhega)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Config Include Check
$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    die("Error: config.php not found at $configPath");
}
require_once $configPath;

// 2. Functions Include Check
$funcPath = __DIR__ . '/includes/functions.php';
if (!file_exists($funcPath)) {
    // Try lowercase path just in case
    $funcPath = __DIR__ . '/includes/Functions.php';
    if (!file_exists($funcPath)) die("Error: includes/functions.php not found");
}
require_once $funcPath;

// 3. Header Include
include 'includes/header.php';

$category = $_GET['category'] ?? '';
$search = $_GET['q'] ?? '';
$title = 'The Beauty <span class="gradient-text">Vault</span>';

try {
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR category LIKE ? OR description LIKE ? ORDER BY id DESC");
        $stmt->execute(["%$search%", "%$search%", "%$search%"]);
        $title = 'Results for "<span class="gradient-text">' . htmlspecialchars($search) . '</span>"';
    } elseif ($category == 'Cosmetics') {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category IN ('Lips', 'Eyes', 'Face', 'Skincare', 'Fragrance') ORDER BY id DESC");
        $stmt->execute();
        $title = 'Cosmetics <span class="gradient-text">Collection</span>';
    } elseif ($category == 'Jewellery') {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'Jewellery' ORDER BY id DESC");
        $stmt->execute();
        $title = 'Jewellery <span class="gradient-text">Collection</span>';
    } elseif ($category) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? ORDER BY id DESC");
        $stmt->execute([$category]);
        $title = htmlspecialchars($category) . ' <span class="gradient-text">Collection</span>';
    } else {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    }
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("<div style='color:red; text-align:center; padding:5rem;'>Database Error: " . $e->getMessage() . "</div>");
}
?>

<main class="container" style="padding: 2rem 5%;">
    <div class="text-center" style="margin-bottom: 3rem; text-align: center;">
        <!-- FIX: Updated to use class instead of inline style for responsiveness -->
        <h1 class="page-title"><?php echo $title; ?></h1>
        <p style="color: #64748b; font-size: 1.1rem;">Curated luxury for the modern elegance.</p>
    </div>

    <?php if (empty($products)): ?>
        <div style="text-align:center; padding: 5rem; color: #94a3b8;">
            <i class="fa-solid fa-box-open" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <p>No products found in this category yet.</p>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach($products as $p): 
                $img = !empty($p['image']) ? $p['image'] : 'placeholder.png';
            ?>
                <div class="card animate" onclick="window.location.href='product.php?id=<?php echo $p['id']; ?>'" style="cursor: pointer;">
                    <div class="card-img" style="height: 300px; overflow: hidden;">
                        <img src="assets/img/<?php echo $img; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="padding: 1rem;">
                        <span style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; display:block; margin-bottom:0.5rem;"><?php echo $p['category']; ?></span>
                        <h3 style="margin: 0 0 1rem; font-size: 1.1rem; min-height: 2.2em;"><?php echo htmlspecialchars($p['name']); ?></h3>
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                            <span style="font-weight: 800; color: #f43f5e; font-size: 1.1rem;">₹<?php echo number_format($p['price'], 2); ?></span>
                            <button onclick="event.stopPropagation(); addToCart(<?php echo $p['id']; ?>, this)" class="btn-add">
                                <i class="fa-solid fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script src="js/app.js?v=<?php echo time(); ?>"></script>
<?php include 'includes/footer.php'; ?>

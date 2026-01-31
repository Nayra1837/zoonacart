<?php
require_once 'config.php';
require_once 'includes/functions.php';

$id = $_GET['id'] ?? null;
if (!$id) redirect('shop.php');

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) redirect('shop.php');

// Fetch images
$stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_primary DESC");
$stmt->execute([$id]);
$images = $stmt->fetchAll(PDO::FETCH_COLUMN);

// If no gallery images, use main image
if (empty($images) && $product['image']) {
    $images = [$product['image']];
} elseif (empty($images)) {
    $images = [];
}

include 'includes/header.php';
?>

<div class="container" style="padding: 4rem 5%;">
    <div style="margin-bottom: 2rem;">
        <a href="shop.php" style="text-decoration: none; color: #64748b; font-weight: 600; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-arrow-left"></i> Back to Shopping
        </a>
    </div>

    <style>
        .product-content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            align-items: start;
        }
        
        .main-image-frame {
            height: 350px; /* Smaller default for mobile */
        }

        @media (min-width: 768px) {
            .product-content-grid {
                grid-template-columns: 1fr 1fr;
                gap: 4rem;
            }
            .main-image-frame {
                height: 500px;
            }
        }
    </style>

    <div class="product-content-grid">
        
        <!-- Image Gallery -->
        <div class="gallery-container" style="width: 100%; overflow: hidden;">
            <div id="mainImageContainer" class="main-image-frame" style="margin-bottom: 1rem; border-radius: 0; overflow-x: auto; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; scroll-snap-type: x mandatory; scroll-behavior: smooth;">
                <?php if (!empty($images)): ?>
                    <?php foreach($images as $index => $img): ?>
                        <div style="min-width: 100%; height: 100%; scroll-snap-align: start; display: flex; justify-content: center; align-items: center;">
                            <img id="img-<?php echo $index; ?>" src="assets/img/<?php echo $img; ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                     <div style="min-width: 100%; height: 100%; scroll-snap-align: start; display: flex; justify-content: center; align-items: center;">
                        <img src="assets/img/<?php echo $product['image']; ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                     </div>
                <?php endif; ?>
            </div>
            
            <?php if (count($images) > 1): ?>
                <div class="thumbnail-scroll" style="display: flex; gap: 1rem; overflow-x: auto; padding-bottom: 1rem;">
                    <?php foreach($images as $index => $img): ?>
                        <div onclick="document.getElementById('img-<?php echo $index; ?>').parentNode.scrollIntoView({behavior: 'smooth', block: 'nearest', inline: 'start'})" 
                             style="flex: 0 0 60px; height: 60px; border: 1px solid #e2e8f0; cursor: pointer; transition: 0.2s;"
                             onmouseenter="this.style.borderColor='var(--primary)'" onmouseleave="this.style.borderColor='#e2e8f0'">
                            <img src="assets/img/<?php echo $img; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Details -->
        <div class="product-info animate">
            <span style="font-size: 0.8rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 2px;"><?php echo $product['category']; ?></span>
            <h1 style="font-size: 3rem; margin: 0.5rem 0 1.5rem; line-height: 1.1;"><?php echo $product['name']; ?></h1>
            
            <p style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-bottom: 2rem;">
                <?php echo formatPrice($product['price']); ?>
            </p>

            <p style="color: #64748b; line-height: 1.8; margin-bottom: 3rem; font-size: 1.1rem;">
                <?php echo nl2br($product['description']); ?>
            </p>

            <div style="display: flex; gap: 1rem; margin-bottom: 3rem;">
                <button onclick="addToCart(<?php echo $product['id']; ?>, this)" class="btn-add" style="padding: 1.2rem 4rem; font-size: 1.1rem; border-radius: 12px; width: 100%; max-width: 400px;">
                    <i class="fa-solid fa-plus"></i> Add to Bag
                </button>
            </div>

            <div style="border-top: 1px solid #e2e8f0; padding-top: 2rem; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <h4 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">Secure Delivery</h4>
                    <p style="font-size: 0.8rem; color: #64748b;">Free shipping on orders over ₹999.</p>
                </div>
                <div>
                    <h4 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">Guaranteed Authentic</h4>
                    <p style="font-size: 0.8rem; color: #64748b;">100% original luxury cosmetics.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/app.js?v=<?php echo time(); ?>"></script>

<!-- Inline script for cart since app.js might need update to handle non-module cart calls if not robust -->
<script>
// Reuse the addToCart logic from app.js using the global scope or simplistic version here for robustness
</script>

<?php include 'includes/footer.php'; ?>

<?php
require_once 'config.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>

<main class="container" style="padding: 2rem 5%;">
    <div class="text-center" style="margin-bottom: 2rem;">
        <h1 style="font-size: 4rem; margin-bottom: 0.5rem;">The Beauty <span class="gradient-text">Vault</span></h1>
        <p style="color: #64748b; font-size: 1.1rem;">Our complete collection of luxury cosmetics.</p>
    </div>

    <div class="product-grid">
        <?php foreach($products as $p): ?>
            <div class="card animate">
                <div class="card-img">
                    <img src="assets/img/<?php echo $p['image']; ?>" alt="<?php echo $p['name']; ?>">
                </div>
                <div style="padding: 0 0.5rem;">
                    <span style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;"><?php echo $p['category']; ?></span>
                    <h3 style="margin: 0.5rem 0; font-size: 1.2rem;"><?php echo $p['name']; ?></h3>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
                        <span style="font-weight: 800; color: var(--primary); font-size: 1.2rem;"><?php echo formatPrice($p['price']); ?></span>
                        <button onclick="addToCart(<?php echo $p['id']; ?>)" class="btn btn-dark" style="padding: 0.6rem 1rem; border-radius: 0;">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<script src="js/app.js"></script>

<?php include 'includes/footer.php'; ?>

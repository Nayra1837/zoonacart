<?php
require_once 'includes/functions.php';
include 'includes/header.php';
?>

<!-- Home Page Content -->
<section class="hero">
    <img src="assets/img/<?php echo getSetting('hero_image'); ?>" alt="Luxury Cosmetics">
    <div class="hero-content animate">
        <span style="font-size: 0.8rem; letter-spacing: 4px; text-transform: uppercase; color: var(--secondary); font-weight: 800; display: block; margin-bottom: 1rem;">Exclusive Collection</span>
        <h1><?php echo nl2br(getSetting('hero_title')); ?></h1>
        <p><?php echo getSetting('hero_subtitle'); ?></p>
        <div class="flex gap-4">
            <a href="shop.php" class="btn btn-primary">Shop Now</a>
            <a href="#products" class="btn" style="border: 1px solid white; color: white;">View Featured</a>
        </div>
    </div>
</section>

<section id="products" class="container" style="padding-top: 1rem;">
    <div class="text-center" style="margin-bottom: 2rem;">
        <h2 style="font-size: 2.5rem; font-weight: 800;">Bestsellers</h2>
        <p style="color: #64748b;">The products everyone is falling in love with.</p>
    </div>
    <div id="productGrid" class="product-grid" style="padding-top: 0;">
        <!-- Data will be loaded by app.js -->
    </div>
</section>

<script src="js/app.js"></script>

<?php include 'includes/footer.php'; ?>

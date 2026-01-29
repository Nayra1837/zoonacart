<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getSetting('site_name'); ?> | <?php echo getSetting('site_description'); ?></title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>assets/img/<?php echo getSetting('site_favicon'); ?>">
    <!-- Fonts and FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/style.css">
    <style>
        /* Admin specific adjustments */
        .admin-nav {
            background: #fff;
            border-bottom: 2px solid var(--primary);
        }
    </style>
</head>
<body>
    <header class="glass">
        <nav class="navbar">
            <div class="navbar-inner">
                <a href="<?php echo BASE_URL; ?>index.php" class="logo gradient-text"><?php echo getSetting('site_name'); ?></a>
                
                <div class="nav-group">
                    <ul class="nav-links">
                        <li><a href="<?php echo BASE_URL; ?>index.php" <?php echo strpos($_SERVER['PHP_SELF'], 'index') !== false ? 'class="active" style="color: var(--primary);"' : ''; ?>>Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>shop.php" <?php echo (strpos($_SERVER['PHP_SELF'], 'shop') !== false) ? 'class="active" style="color: var(--primary);"' : ''; ?>>Collection</a></li>
                        <?php if(isAdmin()): ?>
                            <li id="adminLink"><a href="<?php echo BASE_URL; ?>admin/dashboard.php" style="color: var(--primary); font-weight: 800; border-bottom: 2px solid var(--primary);">Admin Panel</a></li>
                        <?php endif; ?>
                    </ul>

                    <div class="nav-actions">
                        <a href="<?php echo BASE_URL; ?>cart.php" class="cart-btn" style="position: relative; text-decoration: none; color: inherit; display: flex; align-items: center;">
                            <i class="fa-solid fa-bag-shopping" style="font-size: 1.4rem;"></i>
                            <span id="cartCount" style="position: absolute; top: -10px; right: -10px; background: var(--primary); color: white; font-size: 0.75rem; padding: 2px 6px; border-radius: 0; min-width: 18px; text-align: center;"><?php echo getCartCount(); ?></span>
                        </a>
                        
                        <?php if(isLoggedIn()): ?>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <?php if(!isAdmin()): ?>
                                    <a href="<?php echo BASE_URL; ?>profile.php" class="btn btn-dark" style="padding: 0.5rem 1.5rem;">Profile</a>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-primary" style="padding: 0.5rem 1.5rem;">Logout</a>
                            </div>
                        <?php else: ?>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <a href="<?php echo BASE_URL; ?>login.php" style="text-decoration: none; color: inherit; font-weight: 500;">Login</a>
                                <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-primary" style="padding: 0.5rem 1.5rem;">Join</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <main>

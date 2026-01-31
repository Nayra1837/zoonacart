<?php
// Fetch User Details for Header (Profile Pic, etc)
$currentUser = null;
if(isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT profile_pic, name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch();
}
$isAdminPage = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
?>
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
        <?php if($isAdminPage): ?>
        .desktop-header .nav-links li:not(#adminLink),
        .mobile-app-header, 
        .mobile-bottom-nav { display: none !important; }
        .container, main.container { padding-top: 1.5rem !important; }
        <?php endif; ?>
    </style>
</head>
<body>
    <!-- DESKTOP HEADER (Hidden on Mobile) -->
    <header class="glass desktop-header">
        <nav class="navbar">
            <div class="navbar-inner">
                <a href="<?php echo BASE_URL; ?>index.php" class="logo gradient-text"><?php echo getSetting('site_name'); ?></a>
                
                <div class="nav-group">
                    <ul class="nav-links">
                        <li><a href="<?php echo BASE_URL; ?>index.php" <?php echo strpos($_SERVER['PHP_SELF'], 'index') !== false ? 'class="active" style="color: var(--primary); font-weight: 800;"' : ''; ?>>For You</a></li>
                        <li><a href="<?php echo BASE_URL; ?>shop.php" <?php echo (strpos($_SERVER['PHP_SELF'], 'shop') !== false && !isset($_GET['category'])) ? 'class="active" style="color: var(--primary); font-weight: 800;"' : ''; ?>>Shop</a></li>
                        <li><a href="<?php echo BASE_URL; ?>shop.php?category=Cosmetics" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Cosmetics') ? 'class="active" style="color: var(--primary);"' : ''; ?>>Beauty</a></li>
                        <li><a href="<?php echo BASE_URL; ?>shop.php?category=Jewellery" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Jewellery') ? 'class="active" style="color: var(--primary);"' : ''; ?>>Jewellery</a></li>
                        <?php if(isLoggedIn()): ?>
                            <li><a href="<?php echo BASE_URL; ?>wallet.php" <?php echo (strpos($_SERVER['PHP_SELF'], 'wallet') !== false) ? 'class="active" style="color: var(--primary);"' : ''; ?>>Wallet</a></li>
                            <li><a href="<?php echo BASE_URL; ?>orders.php" <?php echo (strpos($_SERVER['PHP_SELF'], 'orders') !== false) ? 'class="active" style="color: var(--primary);"' : ''; ?>>My Orders</a></li>
                        <?php endif; ?>
                        <?php if(isAdmin()): ?>
                            <li id="adminLink"><a href="<?php echo BASE_URL; ?>admin/dashboard.php" style="color: var(--primary); font-weight: 800; border-bottom: 2px solid var(--primary);">Admin</a></li>
                        <?php endif; ?>
                    </ul>

                    <div class="nav-actions">
                        <?php if(!$isAdminPage): ?>
                            <a href="<?php echo BASE_URL; ?>cart.php" class="cart-btn" style="position: relative; text-decoration: none; color: inherit; display: flex; align-items: center;">
                                <i class="fa-solid fa-bag-shopping" style="font-size: 1.4rem;"></i>
                                <span id="cartCount" style="position: absolute; top: -10px; right: -10px; background: var(--primary); color: white; font-size: 0.75rem; padding: 2px 6px; border-radius: 0; min-width: 18px; text-align: center;"><?php echo getCartCount(); ?></span>
                            </a>
                        <?php endif; ?>
                        
                        <?php if(isLoggedIn()): ?>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <?php if(!$isAdminPage && !isAdmin()): ?>
                                    <a href="<?php echo BASE_URL; ?>profile.php" style="display: flex; align-items: center; gap: 8px; text-decoration: none; color: inherit;">
                                        <?php if(!empty($currentUser['profile_pic'])): ?>
                                            <img src="<?php echo BASE_URL; ?>assets/img/profiles/<?php echo $currentUser['profile_pic']; ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);">
                                        <?php else: ?>
                                            <i class="fa-solid fa-circle-user" style="font-size: 1.8rem; color: #64748b;"></i>
                                        <?php endif; ?>
                                        <span style="font-weight: 600; font-size: 0.9rem;"><?php echo explode(' ', $currentUser['name'])[0]; ?></span>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem;">Logout</a>
                            </div>
                        <?php else: ?>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-primary" style="padding: 0.5rem 1.5rem;">Login</a>
                                <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-primary" style="padding: 0.5rem 1.5rem;">Join</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- MOBILE APP HEADER (Visible only on Mobile) -->
    <div class="mobile-app-header">
        <div class="mobile-top-row">
            <div class="d-flex align-items-center gap-2">
                <a href="<?php echo BASE_URL; ?>index.php" class="logo gradient-text" style="font-size: 1.4rem;"><?php echo getSetting('site_name'); ?></a>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <?php if(isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>profile.php" style="color: var(--dark);">
                        <?php if(!empty($currentUser['profile_pic'])): ?>
                            <img src="<?php echo BASE_URL; ?>assets/img/profiles/<?php echo $currentUser['profile_pic']; ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <?php else: ?>
                            <i class="fa-regular fa-user" style="font-size: 1.2rem;"></i>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Login</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mobile-search-area">
            <form action="<?php echo BASE_URL; ?>shop.php" method="GET" class="search-input-wrapper">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="q" placeholder="Search for Products" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" autocomplete="off">
            </form>
        </div>
        
        <div class="mobile-categories-tabs">
            <a href="<?php echo BASE_URL; ?>index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'index') !== false ? 'active' : ''; ?>">For You</a>
            <a href="<?php echo BASE_URL; ?>shop.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'shop') !== false && !isset($_GET['category'])) ? 'active' : ''; ?>">All</a>
            <a href="<?php echo BASE_URL; ?>shop.php?category=Cosmetics" class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Cosmetics') ? 'active' : ''; ?>">Beauty</a>
            <a href="<?php echo BASE_URL; ?>shop.php?category=Jewellery" class="<?php echo (isset($_GET['category']) && $_GET['category'] == 'Jewellery') ? 'active' : ''; ?>">Jewellery</a>
        </div>
    </div>

    <!-- BOTTOM NAVIGATION (Visible only on Mobile) -->
    <div class="mobile-bottom-nav">
        <a href="<?php echo BASE_URL; ?>index.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'index') !== false ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>
        <a href="<?php echo BASE_URL; ?>shop.php" class="nav-item <?php echo (strpos($_SERVER['PHP_SELF'], 'shop') !== false || strpos($_SERVER['PHP_SELF'], 'product') !== false) ? 'active' : ''; ?>">
            <i class="fa-solid fa-layer-group"></i>
            <span>Shop</span>
        </a>
        <?php if(isLoggedIn()): ?>
        <a href="<?php echo BASE_URL; ?>orders.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'orders') !== false ? 'active' : ''; ?>">
            <i class="fa-solid fa-box-open"></i>
            <span>Orders</span>
        </a>
        <a href="<?php echo BASE_URL; ?>wallet.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'wallet') !== false ? 'active' : ''; ?>">
            <i class="fa-solid fa-wallet"></i>
            <span>Wallet</span>
        </a>
        <?php endif; ?>
        <a href="<?php echo BASE_URL; ?>cart.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'cart') !== false ? 'active' : ''; ?>">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Cart</span>
            <span id="mobileCartCount" class="mobile-cart-badge" style="display: <?php echo getCartCount() > 0 ? 'flex' : 'none'; ?>;">
                <?php echo getCartCount(); ?>
            </span>
        </a>
        <a href="<?php echo BASE_URL; ?>profile.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'profile') !== false ? 'active' : ''; ?>">
            <i class="fa-regular fa-user"></i>
            <span>Account</span>
        </a>
    </div>
    <main>

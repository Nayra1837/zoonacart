<?php
// Get current page name for active state
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 2.5rem;">
    <a href="dashboard.php" class="btn" style="background: <?php echo $currentPage === 'dashboard.php' ? 'var(--dark)' : '#f1f5f9'; ?>; color: <?php echo $currentPage === 'dashboard.php' ? 'white' : '#64748b'; ?>; display: flex; align-items: center; gap: 0.5rem; border-radius: 0;">
        <i class="fa-solid fa-gauge"></i> Dashboard
    </a>
    <a href="settings.php" class="btn" style="background: <?php echo $currentPage === 'settings.php' ? 'var(--dark)' : '#f1f5f9'; ?>; color: <?php echo $currentPage === 'settings.php' ? 'white' : '#64748b'; ?>; display: flex; align-items: center; gap: 0.5rem; border-radius: 0;">
        <i class="fa-solid fa-gear"></i> Settings
    </a>
    <a href="users.php" class="btn" style="background: <?php echo $currentPage === 'users.php' ? 'var(--dark)' : '#f1f5f9'; ?>; color: <?php echo $currentPage === 'users.php' ? 'white' : '#64748b'; ?>; display: flex; align-items: center; gap: 0.5rem; border-radius: 0;">
        <i class="fa-solid fa-users"></i> Manage Users
    </a>
    <a href="products.php" class="btn" style="background: <?php echo $currentPage === 'products.php' ? 'var(--dark)' : '#f1f5f9'; ?>; color: <?php echo $currentPage === 'products.php' ? 'white' : '#64748b'; ?>; display: flex; align-items: center; gap: 0.5rem; border-radius: 0;">
        <i class="fa-solid fa-box-open"></i> Manage Products
    </a>
    <a href="orders.php" class="btn" style="background: <?php echo $currentPage === 'orders.php' ? 'var(--primary)' : '#f1f5f9'; ?>; color: <?php echo $currentPage === 'orders.php' ? 'white' : '#64748b'; ?>; display: flex; align-items: center; gap: 0.5rem; border-radius: 0;">
        <i class="fa-solid fa-cart-shopping"></i> View Orders
    </a>
</div>

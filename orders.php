<?php
require_once 'includes/functions.php';
if (!isLoggedIn()) redirect('login.php');
include 'includes/header.php';

// Fetch orders with product details for better UI
$stmt = $pdo->prepare("
    SELECT o.*, 
    (SELECT p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) as product_image,
    (SELECT p.id FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) as first_product_id
    FROM orders o 
    WHERE o.user_id = ? 
    ORDER BY o.order_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<main class="container orders-main">
    <div class="orders-header">
        <h1>My <span class="gradient-text">Orders</span></h1>
        <p>Track and manage your beauty hauls.</p>
    </div>

    <?php if (empty($orders)): ?>
        <div class="glass empty-state">
            <div class="empty-icon">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <h2>No orders yet</h2>
            <p>Treat yourself to something beautiful today!</p>
            <a href="shop.php" class="btn btn-primary">Explore Collection</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $o): 
                $statusColor = '#64748b';
                $statusBg = '#f1f5f9';
                if($o['status'] === 'completed') { $statusColor = '#10b981'; $statusBg = '#ecfdf5'; }
                elseif($o['status'] === 'pending') { $statusColor = '#f59e0b'; $statusBg = '#fffbeb'; }
                elseif($o['status'] === 'cancelled') { $statusColor = '#ef4444'; $statusBg = '#fef2f2'; }
            ?>
                <div class="glass order-card">
                    <div class="order-main-info">
                        <!-- Product Preview -->
                        <div class="product-preview">
                            <?php if(!empty($o['product_image'])): ?>
                                <img src="assets/img/<?php echo $o['product_image']; ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fa-solid fa-box"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Order Info -->
                        <div class="order-details">
                            <div class="order-top-row">
                                <span class="order-id">#GC-<?php echo str_pad($o['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                <span class="status-badge" style="background: <?php echo $statusBg; ?>; color: <?php echo $statusColor; ?>;">
                                    <?php echo htmlspecialchars($o['status'] ?: 'Pending'); ?>
                                </span>
                            </div>
                            <h3 class="order-date"><?php echo date('d M, Y', strtotime($o['order_date'])); ?></h3>
                            <div class="order-bottom-row">
                                <span class="order-price"><?php echo formatPrice($o['total_amount']); ?></span>
                                <span class="order-time"><?php echo date('h:i A', strtotime($o['order_date'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="order-actions">
                        <a href="product.php?id=<?php echo $o['first_product_id']; ?>" class="btn-action primary">
                            View Details
                        </a>
                        <a href="receipt.php?id=<?php echo $o['id']; ?>" class="btn-action secondary">
                            <i class="fa-solid fa-file-invoice"></i> Receipt
                        </a>
                        <?php if($o['status'] === 'completed'): ?>
                            <a href="returns.php" class="btn-action return-btn" onclick="event.stopPropagation();">
                                <i class="fa-solid fa-arrow-rotate-left"></i> Return
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<style>
.orders-main { padding: 3.5rem 5%; max-width: 1200px; margin: 0 auto; }
.orders-header { margin-bottom: 3rem; text-align: center; }
.orders-header h1 { font-size: 2.5rem; font-weight: 850; }

.orders-list { display: flex; flex-direction: column; gap: 1.5rem; }

.order-card { 
    padding: 1.5rem; 
    border-radius: 20px; 
    transition: all 0.3s ease; 
    border: 1px solid #f1f5f9; 
    cursor: pointer; 
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
}

.order-main-info { display: flex; gap: 1.5rem; align-items: center; flex: 1; }

.product-preview { 
    width: 80px; height: 80px; border-radius: 12px; overflow: hidden; 
    background: #f8fafc; flex-shrink: 0; border: 1px solid #f1f5f9; 
}
.product-preview img { width: 100%; height: 100%; object-fit: cover; }
.no-image { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #cbd5e1; }

.order-details { flex: 1; }
.order-top-row { display: flex; align-items: center; gap: 1rem; margin-bottom: 0.3rem; }
.order-id { font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; }
.status-badge { padding: 4px 10px; border-radius: 8px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; }
.order-date { font-size: 1.2rem; font-weight: 700; color: #1e293b; margin: 0; }
.order-bottom-row { display: flex; align-items: center; gap: 1.5rem; margin-top: 0.3rem; }
.order-price { font-weight: 850; color: var(--primary); font-size: 1.2rem; }
.order-time { font-size: 0.85rem; color: #64748b; }

.order-actions { display: flex; gap: 1rem; }
.btn-action { 
    text-decoration: none; font-weight: 700; font-size: 0.9rem; padding: 0.8rem 1.5rem; 
    border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all 0.2s ease;
}
.btn-action.secondary { background: #f8fafc; border: 1px solid #e2e8f0; color: #1e293b; }
.btn-action.primary { background: var(--primary); color: white; }
.btn-action.return-btn { background: #fff1f2; border: 1px solid #fecdd3; color: #e11d48; }

.order-card:hover { 
    transform: translateY(-5px); 
    box-shadow: 0 15px 30px rgba(0,0,0,0.06); 
    border-color: #fecdd3; 
}

/* Mobile Specific Premium Look */
@media (max-width: 768px) {
    .orders-main { padding-top: 130px !important; padding-left: 1rem; padding-right: 1rem; }
    .orders-header { text-align: left; margin-bottom: 2rem; padding-left: 0.5rem; }
    .orders-header h1 { font-size: 2rem; }
    
    .order-card { 
        flex-direction: column; 
        align-items: stretch; 
        padding: 1.2rem; 
        gap: 1.2rem;
        border-radius: 24px;
    }
    
    .order-main-info { align-items: flex-start; }
    .product-preview { width: 70px; height: 70px; border-radius: 15px; }
    
    .order-top-row { justify-content: space-between; }
    .order-date { font-size: 1.1rem; }
    
    .order-actions { 
        padding-top: 1.2rem; 
        border-top: 1px solid #f1f5f9; 
        gap: 0.8rem;
    }
    .btn-action { flex: 1; padding: 0.75rem; font-size: 0.85rem; border-radius: 14px; }
}
</style>

<?php include 'includes/footer.php'; ?>

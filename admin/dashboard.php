<?php
require_once '../includes/functions.php';

if (!isAdmin()) redirect('login.php');

// One-time update for Admin name
if ($_SESSION['name'] === 'System Admin') {
    $pdo->prepare("UPDATE users SET name = 'Admin Panel' WHERE id = ?")->execute([$_SESSION['user_id']]);
    $_SESSION['name'] = 'Admin Panel';
}

// Statistics
$total_sales = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'completed'")->fetchColumn() ?: 0;
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

// Recent Orders
$recent_orders = $pdo->query("SELECT o.*, u.name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC LIMIT 5")->fetchAll();

include '../includes/header.php';
?>

<div class="container" style="padding: 2.5rem 5%;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Admin Dashboard</h1>
        <p style="color: #64748b;">Overview of your boutique's performance.</p>
    </div>
    
    <?php include 'admin_nav.php'; ?>

    <!-- Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2.5rem;">
        <div style="background: white; padding: 1.5rem 2rem; border-left: 4px solid var(--primary);">
            <p style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">Revenue</p>
            <h3 style="font-size: 1.8rem; font-weight: 800;"><?php echo formatPrice($total_sales); ?></h3>
        </div>
        <div style="background: white; padding: 1.5rem 2rem; border-left: 4px solid #fbbf24;">
            <p style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">Orders</p>
            <h3 style="font-size: 1.8rem; font-weight: 800;"><?php echo $total_orders; ?></h3>
        </div>
        <div style="background: white; padding: 1.5rem 2rem; border-left: 4px solid #38bdf8;">
            <p style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">Customers</p>
            <h3 style="font-size: 1.8rem; font-weight: 800;"><?php echo $total_users; ?></h3>
        </div>
        <div style="background: white; padding: 1.5rem 2rem; border-left: 4px solid #10b981;">
            <p style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">Products</p>
            <h3 style="font-size: 1.8rem; font-weight: 800;"><?php echo $total_products; ?></h3>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr; gap: 3rem;">
        <!-- Recent Orders -->
        <div class="glass" style="padding: 3rem; border-radius: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 style="font-size: 1.5rem;">Recent Activity</h2>
                <a href="orders.php" style="color: var(--primary); font-weight: 700; text-decoration: none; font-size: 0.9rem;">View All &rarr;</a>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="text-align: left; border-bottom: 1px solid #eee;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase;">Order</th>
                            <th style="padding: 1rem; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase;">Customer</th>
                            <th style="padding: 1rem; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase;">Amount</th>
                            <th style="padding: 1rem; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_orders as $order): ?>
                            <tr style="border-bottom: 1px solid #f8fafc;">
                                <td style="padding: 1.5rem; font-weight: 700; color: #64748b;">#<?php echo $order['id']; ?></td>
                                <td style="padding: 1.5rem; font-weight: 600;"><?php echo $order['name']; ?></td>
                                <td style="padding: 1.5rem; font-weight: 800; color: var(--primary);"><?php echo formatPrice($order['total_amount']); ?></td>
                                <td style="padding: 1.5rem;">
                                    <span style="background: #f1f5f9; padding: 4px 12px; border-radius: 0; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #64748b;">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

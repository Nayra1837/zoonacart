<?php
require_once '../includes/functions.php';
if (!isAdmin()) redirect('login.php');

$success = '';

// Handle Status Update
if (isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['order_id']]);
    $success = "Order status updated!";
}

// Handle Order Deletion
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $success = "Order deleted successfully!";
}

$orders = $pdo->query("SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC")->fetchAll();
include '../includes/header.php';
?>

<div class="container" style="padding: 2.5rem 5%;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Order Fulfillment</h1>
        <p style="color: #64748b;">Track and manage customer beauty orders with precision.</p>
    </div>

    <?php include 'admin_nav.php'; ?>

    <?php if($success): ?>
        <div style="background: #ecfdf5; color: #065f46; padding: 1rem 2rem; border-radius: 0; margin-bottom: 2rem; font-weight: 600; display: flex; align-items: center; gap: 1rem; border: 1px solid #a7f3d0;">
            <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div class="glass" style="border-radius: 0; overflow: hidden; padding: 2rem;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="text-align: left; border-bottom: 1px solid #eee;">
                    <tr>
                        <th style="padding: 1.5rem; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Order ID & Date</th>
                        <th style="padding: 1.5rem; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Customer Information</th>
                        <th style="padding: 1.5rem; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; text-align: center;">Amount</th>
                        <th style="padding: 1.5rem; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; text-align: center;">Status</th>
                        <th style="padding: 1.5rem; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $o): ?>
                        <tr style="border-bottom: 1px solid #f8fafc; transition: 0.3s;" onmouseenter="this.style.background='#fdfaff'" onmouseleave="this.style.background='transparent'">
                            <td style="padding: 1.5rem;">
                                <p style="font-weight: 800; color: #1e293b; font-family: monospace;">#GC-<?php echo str_pad($o['id'], 5, '0', STR_PAD_LEFT); ?></p>
                                <p style="font-size: 0.8rem; color: #94a3b8; padding-top: 5px;"><?php echo date('M j, Y, g:i a', strtotime($o['order_date'])); ?></p>
                            </td>
                            <td style="padding: 1.5rem;">
                                <p style="font-weight: 700;"><?php echo $o['customer_name']; ?></p>
                                <p style="font-size: 0.75rem; color: #64748b; margin-top: 4px; line-height: 1.4; max-width: 250px;">
                                    <i class="fa-solid fa-location-dot" style="font-size: 0.6rem;"></i> <?php echo $o['delivery_address']; ?>
                                </p>
                            </td>
                            <td style="padding: 1.5rem; text-align: center;">
                                <span style="font-weight: 800; color: var(--primary); font-size: 1.1rem;"><?php echo formatPrice($o['total_amount']); ?></span>
                            </td>
                            <td style="padding: 1.5rem; text-align: center;">
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                    <select name="status" onchange="this.form.submit()" style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 0; padding: 0.5rem 1rem; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; cursor: pointer; outline: none; transition: 0.3s;" onfocus="this.style.borderColor='var(--primary)'">
                                        <option value="pending" <?php echo $o['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="completed" <?php echo $o['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $o['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td style="padding: 1.5rem; text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="../receipt.php?id=<?php echo $o['id']; ?>" class="btn btn-dark" style="padding: 0.6rem 1rem; font-size: 0.8rem; border-radius: 0;" title="View Receipt">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </a>
                                    <a href="?delete=<?php echo $o['id']; ?>" onclick="return confirm('Delete this order?')" class="btn" style="padding: 0.6rem 1rem; font-size: 0.8rem; border-radius: 0; background: #fff1f2; color: #e11d48;" title="Delete Order">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

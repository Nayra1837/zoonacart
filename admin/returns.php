<?php
require_once '../includes/functions.php';
if (!isAdmin()) redirect('login.php');

$currentPage = 'returns.php';

// Fetch all returns
$sql = "SELECT r.*, u.name as user_name, o.id as order_ref, p.name as product_name 
        FROM returns r
        JOIN users u ON r.user_id = u.id
        JOIN orders o ON r.order_id = o.id
        JOIN products p ON r.product_id = p.id
        ORDER BY r.created_at DESC";
$returns = $pdo->query($sql)->fetchAll();

include '../includes/header.php';
?>

<div class="container" style="padding: 2.5rem 5%;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Manage Returns</h1>
        <p style="color: #64748b;">Process customer return requests.</p>
    </div>
    
    <?php include 'admin_nav.php'; ?>

    <div class="glass" style="padding: 3rem; border-radius: 0;">
        <?php if (empty($returns)): ?>
            <p style="text-align: center; color: #94a3b8; padding: 2rem;">No return requests found.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                            <th style="padding: 1rem;">ID</th>
                            <th style="padding: 1rem;">Customer</th>
                            <th style="padding: 1rem;">Product</th>
                            <th style="padding: 1rem;">Reason</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($returns as $r): ?>
                            <tr style="border-bottom: 1px solid #f8fafc;">
                                <td style="padding: 1.5rem;">#<?php echo $r['id']; ?></td>
                                <td style="padding: 1.5rem; font-weight: 600;">
                                    <?php echo $r['user_name']; ?>
                                    <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 400;">Order #<?php echo $r['order_ref']; ?></div>
                                </td>
                                <td style="padding: 1.5rem;"><?php echo $r['product_name']; ?></td>
                                <td style="padding: 1.5rem;">
                                    <span style="font-weight: 600;"><?php echo $r['reason']; ?></span>
                                    <div style="font-size: 0.85rem; color: #64748b; margin-top: 0.2rem;"><?php echo $r['feedback']; ?></div>
                                </td>
                                <td style="padding: 1.5rem;">
                                    <span class="status-badge <?php echo $r['status']; ?>" 
                                          style="padding: 4px 10px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; border-radius: 0;
                                          background: <?php echo $r['status'] == 'approved' ? '#dcfce7' : ($r['status'] == 'rejected' ? '#fee2e2' : '#f1f5f9'); ?>;
                                          color: <?php echo $r['status'] == 'approved' ? '#166534' : ($r['status'] == 'rejected' ? '#991b1b' : '#475569'); ?>;">
                                        <?php echo $r['status']; ?>
                                    </span>
                                </td>
                                <td style="padding: 1.5rem; text-align: right;">
                                    <?php if($r['status'] == 'pending'): ?>
                                        <button onclick="updateReturnStatus(<?php echo $r['id']; ?>, 'approved')" class="btn" style="background: #10b981; color: white; padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 0;">Approve</button>
                                        <button onclick="updateReturnStatus(<?php echo $r['id']; ?>, 'rejected')" class="btn" style="background: #ef4444; color: white; padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 0; margin-left: 0.5rem;">Reject</button>
                                    <?php else: ?>
                                        <span style="font-size: 0.85rem; color: #cbd5e1;">Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
async function updateReturnStatus(id, status) {
    if(!confirm('Are you sure you want to ' + status + ' this return request?')) return;

    const formData = new FormData();
    formData.append('return_id', id);
    formData.append('status', status);

    try {
        const res = await fetch('../api/main.php?action=update_return_status', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        
        if(result.success) {
            location.reload();
        } else {
            alert(result.error || 'Failed to update status');
        }
    } catch(err) {
        console.error(err);
        alert('Error: ' + err.message);
    }
}
</script>

<?php include '../includes/footer.php'; ?>

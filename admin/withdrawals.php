<?php
require_once '../includes/functions.php';
if (!isAdmin()) redirect('login.php');

$currentPage = 'withdrawals.php';

// Fetch all withdrawals
$sql = "SELECT w.*, u.name as user_name 
        FROM withdrawals w
        JOIN users u ON w.user_id = u.id
        ORDER BY w.created_at DESC";
$withdrawals = $pdo->query($sql)->fetchAll();

include '../includes/header.php';
?>

<div class="container" style="padding: 2.5rem 5%;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Withdrawal Requests</h1>
        <p style="color: #64748b;">Manage fund transfer requests.</p>
    </div>
    
    <?php include 'admin_nav.php'; ?>

    <div class="glass" style="padding: 3rem; border-radius: 0;">
        <?php if (empty($withdrawals)): ?>
            <p style="text-align: center; color: #94a3b8; padding: 2rem;">No withdrawal requests found.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                            <th style="padding: 1rem;">ID</th>
                            <th style="padding: 1rem;">User</th>
                            <th style="padding: 1rem;">Amount</th>
                            <th style="padding: 1rem;">Details</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($withdrawals as $w): ?>
                            <tr style="border-bottom: 1px solid #f8fafc;">
                                <td style="padding: 1.5rem;">#<?php echo $w['id']; ?></td>
                                <td style="padding: 1.5rem; font-weight: 600;"><?php echo $w['user_name']; ?></td>
                                <td style="padding: 1.5rem; font-weight: 800; color: var(--dark);"><?php echo formatPrice($w['amount']); ?></td>
                                <td style="padding: 1.5rem;">
                                    <span style="font-weight: 600; display: block; margin-bottom: 0.3rem;"><?php echo $w['method']; ?></span>
                                    <div style="font-size: 0.85rem; color: #64748b; white-space: pre-wrap;"><?php echo $w['details']; ?></div>
                                </td>
                                <td style="padding: 1.5rem;">
                                    <span class="status-badge <?php echo $w['status']; ?>" 
                                          style="padding: 4px 10px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; border-radius: 0;
                                          background: <?php echo $w['status'] == 'approved' ? '#dcfce7' : ($w['status'] == 'rejected' ? '#fee2e2' : '#f1f5f9'); ?>;
                                          color: <?php echo $w['status'] == 'approved' ? '#166534' : ($w['status'] == 'rejected' ? '#991b1b' : '#475569'); ?>;">
                                        <?php echo $w['status']; ?>
                                    </span>
                                </td>
                                <td style="padding: 1.5rem; text-align: right;">
                                    <?php if($w['status'] == 'pending'): ?>
                                        <button onclick="updateStatus(<?php echo $w['id']; ?>, 'approved')" class="btn" style="background: #10b981; color: white; padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 0;">Approve</button>
                                        <button onclick="updateStatus(<?php echo $w['id']; ?>, 'rejected')" class="btn" style="background: #ef4444; color: white; padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 0; margin-left: 0.5rem;">Reject</button>
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
async function updateStatus(id, status) {
    if(!confirm('Are you sure you want to ' + status + ' this request? ' + (status === 'rejected' ? 'Funds will be refunded to user wallet.' : ''))) return;

    const formData = new FormData();
    formData.append('id', id);
    formData.append('status', status);

    try {
        const res = await fetch('../api/main.php?action=update_withdrawal_status', {
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
        alert('Server Error: ' + err.message);
    }
}
</script>

<?php include '../includes/footer.php'; ?>

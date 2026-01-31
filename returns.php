<?php
require_once 'includes/functions.php';
if (!isLoggedIn()) redirect('login.php');

$userId = $_SESSION['user_id'];
// Fetch all items from orders that are NOT cancelled
// Ideally status should be 'delivered', but for now we allow all except cancelled
$sql = "SELECT oi.*, o.order_date, o.status as order_status, p.name, p.image, r.status as return_status 
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        JOIN products p ON oi.product_id = p.id
        LEFT JOIN returns r ON oi.order_id = r.order_id AND oi.product_id = r.product_id
        WHERE o.user_id = ? AND o.status != 'cancelled'
        ORDER BY o.order_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container" style="padding: 3rem 5%;">
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem;">Returns Center</h1>
        <p style="color: #64748b;">Select an item to return or exchange.</p>
    </div>

    <?php if(empty($items)): ?>
        <div style="text-align: center; padding: 5rem; background: #f8fafc; border: 1px solid #e2e8f0;">
            <p>You haven't purchased any eligible items yet.</p>
            <a href="shop.php" class="btn btn-primary" style="margin-top: 1rem;">Start Shopping</a>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 1.5rem;">
            <?php foreach($items as $item): ?>
                <div class="glass" style="padding: 1.5rem; border-radius: 0; display: flex; align-items: center; flex-wrap: wrap; gap: 2rem;">
                    <img src="assets/img/<?php echo $item['image']; ?>" style="width: 80px; height: 80px; object-fit: cover; background: #f8fafc;">
                    
                    <div style="flex: 1; min-width: 200px;">
                        <h3 style="font-size: 1.1rem; margin-bottom: 0.25rem;"><?php echo $item['name']; ?></h3>
                        <p style="font-size: 0.85rem; color: #64748b;">
                            Order #GC-<?php echo str_pad($item['order_id'], 5, '0', STR_PAD_LEFT); ?> • 
                            <?php echo date('M d, Y', strtotime($item['order_date'])); ?>
                        </p>
                    </div>

                    <div style="text-align: right;">
                        <?php if($item['order_status'] !== 'completed'): ?>
                            <span style="background: #fff7ed; color: #c2410c; padding: 6px 12px; font-size: 0.8rem; font-weight: 700; border: 1px solid #ffedd5; display: inline-block;">
                                <i class="fa-solid fa-truck-fast"></i> Delivery Pending
                            </span>
                        <?php elseif($item['return_status']): ?>
                            <span style="background: #f1f5f9; padding: 6px 12px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: var(--primary);">
                                <?php echo $item['return_status'] == 'pending' ? 'Return Requested' : $item['return_status']; ?>
                            </span>
                        <?php else: ?>
                            <button onclick="openReturnModal(<?php echo $item['order_id']; ?>, <?php echo $item['product_id']; ?>, '<?php echo addslashes($item['name']); ?>')" 
                                    class="btn" style="border: 1px solid #cbd5e1; padding: 0.6rem 1.5rem; color: #475569;">
                                Return Item
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Return Modal -->
<div id="returnModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; width: 90%; max-width: 500px; position: relative;">
        <button onclick="closeReturnModal()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        
        <h2 style="margin-bottom: 1.5rem;">Return Item</h2>
        <p id="modalItemName" style="color: var(--primary); font-weight: 700; margin-bottom: 1.5rem;"></p>
        
        <form id="returnForm" onsubmit="submitReturn(event)">
            <input type="hidden" id="returnOrderId" name="order_id">
            <input type="hidden" id="returnProductId" name="product_id">
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem;">Reason for Return</label>
                <select name="reason" id="returnReason" required style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1;">
                    <option value="">Select a reason...</option>
                    <option value="Defective/Damaged">Defective or Damaged</option>
                    <option value="Wrong Item">Received Wrong Item</option>
                    <option value="Did not like">Did not like the product</option>
                    <option value="Changed Mind">Changed my mind</option>
                </select>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem;">Feedback</label>
                <textarea name="feedback" id="returnFeedback" rows="3" required placeholder="Please tell us more..." style="width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1;"></textarea>
            </div>
            
            <button type="submit" class="btn btn-dark" style="width: 100%; padding: 1rem;">Submit Request</button>
        </form>
    </div>
</div>

<script>
function openReturnModal(orderId, productId, name) {
    document.getElementById('returnOrderId').value = orderId;
    document.getElementById('returnProductId').value = productId;
    document.getElementById('modalItemName').innerText = name;
    document.getElementById('returnModal').style.display = 'flex';
}

function closeReturnModal() {
    document.getElementById('returnModal').style.display = 'none';
    document.getElementById('returnForm').reset();
}

async function submitReturn(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const res = await fetch('api/main.php?action=return_item', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        
        if(result.success) {
            alert('Return request submitted successfully!');
            location.reload();
        } else {
            alert(result.error || 'Failed to submit return.');
        }
    } catch(err) {
        alert('Error connecting to server.');
    }
}

// Close modal on outside click
document.getElementById('returnModal').addEventListener('click', (e) => {
    if(e.target === document.getElementById('returnModal')) closeReturnModal();
});
</script>

<?php include 'includes/footer.php'; ?>

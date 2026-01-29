<?php
require_once 'includes/functions.php';
include 'includes/header.php';

if (!isLoggedIn()) header("Location: login.php");

$orderId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

// Fetch order details
$stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ? AND (o.user_id = ? OR ? = 'admin')");
$stmt->execute([$orderId, $userId, $_SESSION['role']]);
$order = $stmt->fetch();

if (!$order) {
    echo "<div style='padding: 10rem 5%; text-align: center;'><h2>Order not found.</h2><br><a href='index.php' class='btn btn-primary'>Back to Home</a></div>";
    include 'includes/footer.php';
    exit();
}

// Fetch order items
$stmt = $pdo->prepare("SELECT oi.*, p.name, p.image 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();
?>

<div class="container" style="padding: 2.5rem 5%; max-width: 900px;">
    <div style="text-align: center; margin-bottom: 2.5rem;">
        <div style="width: 80px; height: 80px; background: #ecfdf5; color: #10b981; border-radius: 0; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.5rem; border: 4px solid white; box-shadow: var(--shadow);">
            <i class="fa-solid fa-check"></i>
        </div>
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Order Confirmed!</h1>
        <p style="color: #64748b;">Thank you for choosing GlamCart. Your beauty package is being prepared.</p>
    </div>

    <!-- Receipt Card -->
    <div class="glass" style="border-radius: 0; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1); background: white;" id="receipt">
        <div style="padding: 3rem 4rem; background: #0f172a; color: white; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 class="gradient-text" style="font-size: 2.5rem; margin-bottom: 0.5rem; font-weight: 800;">GlamCart</h2>
                <p style="color: #94a3b8; font-size: 0.9rem;">Premium Boutique Experience</p>
            </div>
            <div style="text-align: right;">
                <p style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 2px; margin-bottom: 0.5rem;">Order Number</p>
                <p style="font-size: 1.5rem; font-weight: 800; font-family: monospace; color: white;">#GC-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></p>
            </div>
        </div>

        <div style="padding: 4rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; margin-bottom: 4rem;">
                <div>
                    <h4 style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; margin-bottom: 1rem;">Customer Details</h4>
                    <p style="font-weight: 700; font-size: 1.1rem; color: var(--dark); margin-bottom: 0.3rem;"><?php echo $order['customer_name']; ?></p>
                    <p style="color: #64748b;"><?php echo $order['email']; ?></p>
                </div>
                <div>
                    <h4 style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; margin-bottom: 1rem;">Shipping Destination</h4>
                    <p style="color: #64748b; line-height: 1.6;"><?php echo $order['delivery_address']; ?></p>
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 4rem;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                        <th style="padding-bottom: 1.5rem; font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Product</th>
                        <th style="padding-bottom: 1.5rem; font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; text-align: center;">Qty</th>
                        <th style="padding-bottom: 1.5rem; font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Price</th>
                        <th style="padding-bottom: 1.5rem; font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr style="border-bottom: 1px solid #f8fafc;">
                            <td style="padding: 2rem 0; display: flex; align-items: center; gap: 1.5rem;">
                                <img src="<?php echo ASSETS_URL . 'img/' . $item['image']; ?>" style="width: 60px; height: 60px; border-radius: 0; object-fit: cover; background: #f8fafc;">
                                <span style="font-weight: 700;"><?php echo $item['name']; ?></span>
                            </td>
                            <td style="padding: 2rem 0; text-align: center; color: #64748b; font-weight: 600;"><?php echo $item['quantity']; ?></td>
                            <td style="padding: 2rem 0; text-align: right; color: #64748b; font-weight: 600;"><?php echo formatPrice($item['price']); ?></td>
                            <td style="padding: 2rem 0; text-align: right; font-weight: 800; color: var(--dark);"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="border-top: 3px dashed #f1f5f9; padding-top: 2rem; display: flex; flex-direction: column; align-items: flex-end;">
                <div style="width: 100%; max-width: 300px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: #64748b; font-weight: 600;">
                        <span>Total Items</span>
                        <span><?php echo count($items); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 1.5rem; border-top: 1px solid #f1f5f9;">
                        <span style="font-weight: 800; font-size: 1.2rem;">Amount Paid</span>
                        <span style="font-weight: 800; font-size: 1.5rem; color: var(--primary);"><?php echo formatPrice($order['total_amount']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div style="background: #f8fafc; padding: 2rem 4rem; text-align: center;">
            <p style="font-size: 0.8rem; color: #94a3b8; font-weight: 600;">Purchase Date: <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
            <p style="font-size: 0.6rem; color: #cbd5e1; text-transform: uppercase; letter-spacing: 1px; margin-top: 0.8rem;">Thank you for shopping with GlamCart</p>
        </div>
    </div>

    <div class="no-print" style="margin-top: 4rem; display: flex; justify-content: center; gap: 1.5rem;">
        <button onclick="downloadReceipt()" class="btn btn-dark" style="padding: 1.2rem 2.5rem; display: flex; align-items: center; gap: 0.8rem;">
            <i class="fa-solid fa-download"></i> Download Receipt
        </button>
        <a href="index.php" class="btn" style="background: white; border: 1px solid #e2e8f0; padding: 1.2rem 2.5rem; color: #64748b;">
            Back to Home
        </a>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadReceipt() {
    const element = document.getElementById('receipt');
    
    const opt = {
        margin:       10, // Standard margin
        filename:     'GlamCart-Receipt-<?php echo $order['id']; ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
            scale: 2, 
            useCORS: true,
            letterRendering: true,
            scrollY: 0
        },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    // This ensures the element is perfectly centered and scaled to fit the PDF
    html2pdf().from(element).set(opt).save();
}
</script>

<style>
@media print {
    header, footer, .no-print {
        display: none !important;
    }
    body {
        background: white;
    }
    .container {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }
    #receipt {
        box-shadow: none !important;
        border: 1px solid #eee !important;
        border-radius: 0 !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>

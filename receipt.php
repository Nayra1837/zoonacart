<?php
// DEBUG: Enable errors temporarily
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/functions.php';
include 'includes/header.php';

if (!isLoggedIn()) header("Location: login.php");

$orderId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'] ?? 'user';

// Fetch order details
$stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ? AND (o.user_id = ? OR ? = 'admin')");
$stmt->execute([$orderId, $userId, $userRole]);
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

// Calculate fallback values if missing in DB
$calc_subtotal = 0;
foreach ($items as $item) {
    $calc_subtotal += $item['price'] * $item['quantity'];
}

$gst_rate = getSetting('gst_rate') ?: 18;
$display_subtotal = ($order['subtotal_amount'] > 0) ? $order['subtotal_amount'] : $calc_subtotal;
$display_gst = ($order['gst_amount'] > 0) ? $order['gst_amount'] : ($display_subtotal * ($gst_rate / 100));

// Safety check for amount in words
try {
    $amount_in_words = getAmountInWords($order['total_amount']);
    if (!$amount_in_words) $amount_in_words = "Zero";
} catch (Exception $e) {
    $amount_in_words = "Unavailable";
}
?>

<style>
    /* NUCLEAR UI STABILITY RESET */
    * { 
        animation: none !important; 
        transition: none !important; 
        transform: none !important; 
        box-shadow: none !important;
        text-shadow: none !important;
        box-sizing: border-box !important;
    }
    #invoice, .container {
        font-family: Arial, sans-serif !important; 
        direction: ltr !important;
    }
    .invoice-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 2px solid #000;
        margin-bottom: 2rem;
    }
    @media (max-width: 600px) {
        .container { padding: 1rem 3% !important; }
        #invoice { min-width: 800px; } /* Force table to maintain structure */
        h1 { font-size: 1.8rem !important; }
    }
</style>

<div class="container" style="padding: 2.5rem 5%; max-width: 1000px; margin: 0 auto; background: white;">
    
    <div class="no-print" style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 2.5rem; color: #0f172a; margin-bottom: 0.5rem; font-weight: 900; letter-spacing: -1px;">Order Confirmation</h1>
        <p style="color: #64748b; font-size: 1.2rem;">Your order #GC-<?php echo (int)$order['id']; ?> has been placed successfully. ✅</p>
    </div>

    <!-- INVOICE BOX - ULTRA STABLE TABLES -->
    <div class="invoice-wrapper">
        <div id="invoice" style="background: white; color: #000; font-family: Arial, sans-serif !important; width: 100%; padding: 0; border-radius: 0;">
        
        <!-- Header Table -->
        <table style="width: 100%; border-collapse: collapse; border-bottom: 2px solid #000;">
            <tr>
                <td style="padding: 2rem; text-align: left;">
                    <h2 style="font-size: 1.8rem; color: #000; margin: 0; font-weight: 900;"><?php echo htmlspecialchars(getSetting('site_name')); ?></h2>
                </td>
                <td style="padding: 2rem; text-align: right; vertical-align: top;">
                    <h3 style="font-size: 1.2rem; margin: 0; color: #000; text-transform: uppercase; font-weight: 900;">Tax Invoice/Bill of Supply</h3>
                    <p style="font-size: 0.9rem; color: #000; font-weight: 700; margin: 5px 0 0 0;">(Original for Recipient)</p>
                </td>
            </tr>
        </table>

        <!-- Info Table -->
        <table style="width: 100%; border-collapse: collapse; border-bottom: 1px solid #000;">
            <tr>
                <td style="width: 50%; padding: 2rem; vertical-align: top; border-right: 1px solid #000;">
                    <p style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Sold By :</p>
                    <p style="font-weight: 800; font-size: 1rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars(getSetting('seller_name')); ?></p>
                    <p style="font-size: 0.85rem; color: #000; line-height: 1.6; white-space: pre-line;"><?php echo htmlspecialchars(getSetting('seller_address')); ?></p>
                </td>
                <td style="width: 50%; padding: 2rem; vertical-align: top;">
                    <p style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Billing/Shipping Address :</p>
                    <p style="font-weight: 800; font-size: 1rem; margin-bottom: 0.3rem;"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p style="font-size: 0.85rem; color: #000; line-height: 1.6;"><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                </td>
            </tr>
        </table>

        <!-- Metadata Table -->
        <table style="width: 100%; border-collapse: collapse; border-bottom: 1px solid #000; background: #fafafa;">
            <tr>
                <td style="padding: 1rem 2rem; font-size: 0.85rem;">
                    <p style="margin: 0;"><span style="font-weight: 700;">Order ID:</span> GC-<?php echo $order['id']; ?></p>
                    <p style="margin: 0;"><span style="font-weight: 700;">Order Date:</span> <?php echo date('d.m.Y', strtotime($order['order_date'])); ?></p>
                </td>
                <td style="padding: 1rem 2rem; text-align: right; font-size: 0.85rem;">
                    <p style="margin: 0;"><span style="font-weight: 700;">Invoice No:</span> INV-GC-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></p>
                    <p style="margin: 0;"><span style="font-weight: 700;">Invoice Date:</span> <?php echo date('d.m.Y', strtotime($order['order_date'])); ?></p>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="background: #000; color: white;">
                    <th style="padding: 1rem; border: 1px solid #000;">Sl</th>
                    <th style="padding: 1rem; border: 1px solid #000; text-align: left; width: 35%;">Description</th>
                    <th style="padding: 1rem; border: 1px solid #000;">HSN</th>
                    <th style="padding: 1rem; border: 1px solid #000; text-align: right;">Unit Price</th>
                    <th style="padding: 1rem; border: 1px solid #000;">Qty</th>
                    <th style="padding: 1rem; border: 1px solid #000; text-align: right;">Tax</th>
                    <th style="padding: 1rem; border: 1px solid #000; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $index => $item): 
                    $item_hsn = $item['hsn_code'] ?? '3304';
                    $item_tax_rate = $item['tax_percent'] ?? 18;
                    $sub = $item['price'] * $item['quantity'];
                    $tax_val = $sub * ($item_tax_rate / 100);
                    $row_total = $sub + $tax_val;
                ?>
                <tr>
                    <td style="padding: 1rem; border: 1px solid #e2e8f0; text-align: center;"><?php echo $index + 1; ?></td>
                    <td style="padding: 1rem; border: 1px solid #e2e8f0;"><?php echo htmlspecialchars($item['name']); ?></td>
                    <td style="padding: 1rem; border: 1px solid #e2e8f0; text-align: center;"><?php echo $item_hsn; ?></td>
                    <td style="padding: 1rem; border: 1px solid #e2e8f0; text-align: right;">₹<?php echo number_format($item['price'], 2); ?></td>
                    <td style="padding: 1rem; border: 1px solid #e2e8f0; text-align: center;"><?php echo $item['quantity']; ?></td>
                    <td style="padding: 1rem; border: 1px solid #e2e8f0; text-align: right;"><?php echo $item_tax_rate; ?>%</td>
                    <td style="padding: 1rem; border: 1px solid #e2e8f0; text-align: right; font-weight: 700;">₹<?php echo number_format($row_total, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background: #fafafa;">
                    <td colspan="5" style="padding: 1rem; border: 1px solid #000; text-align: right; font-weight: 700;">Subtotal (Base Price)</td>
                    <td style="padding: 1rem; border: 1px solid #000; text-align: right;">₹<?php echo number_format($display_subtotal, 2); ?></td>
                </tr>
                <tr style="background: #fafafa;">
                    <td colspan="5" style="padding: 1rem; border: 1px solid #000; text-align: right; font-weight: 700;">GST (Integrated Tax) <?php echo $gst_rate; ?>%</td>
                    <td style="padding: 1rem; border: 1px solid #000; text-align: right;">₹<?php echo number_format($display_gst, 2); ?></td>
                </tr>
                <tr style="background: #eee; font-weight: 900;">
                    <td colspan="5" style="padding: 1.2rem; border: 1px solid #000; text-align: right; font-size: 1.1rem;">Grand Total</td>
                    <td style="padding: 1.2rem; border: 1px solid #000; text-align: right; font-size: 1.3rem;">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- Word Conversion -->
        <div style="padding: 2rem; border-top: 2px solid #000;">
            <p style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Amount in Words:</p>
            <p style="font-weight: 900; font-size: 1rem; margin: 0;"><?php echo $amount_in_words; ?> Rupees Only</p>
        </div>

        <!-- Footer -->
        <table style="width: 100%; border-collapse: collapse; padding: 2rem;">
            <tr>
                <td style="padding: 2rem; vertical-align: bottom;">
                    <p style="font-size: 0.8rem; color: #94a3b8; margin: 0;">This is a computer generated invoice.</p>
                </td>
                <td style="padding: 2rem; text-align: right; vertical-align: bottom;">
                    <p style="font-weight: 800; margin-bottom: 2rem;"><?php echo htmlspecialchars(getSetting('site_name')); ?></p>
                    <p style="font-weight: 800; text-decoration: overline; margin:0;">Authorized Signatory</p>
                </td>
            </tr>
        </table>
    </div> <!-- End #invoice -->
</div> <!-- End .invoice-wrapper -->

    <!-- Actions -->
    <div class="no-print" style="margin-top: 3rem; display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
        <button onclick="seamlessDownload()" class="btn" id="downloadBtn" style="background: #2563eb; color: white; padding: 1rem 2.5rem; border-radius: 8px; font-weight: 800; text-align: center; border: none; cursor: pointer;">
            <i class="fa-solid fa-file-arrow-down"></i> Download PDF Receipt
        </button>
        <a href="index.php" class="btn" style="background: #64748b; color: white; padding: 1rem 2.5rem; border-radius: 8px; text-align: center;">Back to Home</a>
        <?php if($order['status'] === 'completed'): ?>
            <a href="returns.php" class="btn" style="background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; padding: 1rem 2.5rem; border-radius: 8px; text-align: center; font-weight: 800;">
                <i class="fa-solid fa-arrow-rotate-left"></i> Return Items
            </a>
        <?php endif; ?>
    </div>

</div>

<?php include 'includes/footer.php'; ?>

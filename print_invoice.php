<?php
require_once 'includes/functions.php';

if (!isLoggedIn()) die("Access Denied");

$orderId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

// Fetch order details
$stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ? AND (o.user_id = ? OR ? = 'admin')");
$stmt->execute([$orderId, $userId, $_SESSION['role']]);
$order = $stmt->fetch();

if (!$order) die("Order not found");

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice_GC_<?php echo $order['id']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { background: #f1f5f9; margin: 0; padding: 40px; font-family: 'Outfit', sans-serif; color: #1e293b; }
        .invoice-container { background: white; max-width: 900px; margin: 0 auto; border: 1px solid #e2e8f0; padding: 0; position: relative; }
        table { width: 100%; border-collapse: collapse; }
        .header-table { border-bottom: 2px solid #f1f5f9; }
        .info-table { border-bottom: 1px solid #e2e8f0; }
        .meta-table { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        .items-table th { background: #0f172a; color: white; padding: 15px; text-align: left; }
        .items-table td { padding: 15px; border: 1px solid #f1f5f9; }
        .footer-strip { background: #0f172a; color: #94a3b8; padding: 15px; text-align: center; font-size: 11px; }
        
        @media print {
            body { background: white; padding: 0; }
            .invoice-container { border: none; max-width: 100%; }
            .no-print-area { display: none; }
        }
        .no-print-area { padding: 20px; text-align: center; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: <?php echo isset($_GET['raw']) ? 'none' : 'block'; ?>; }
        .btn { padding: 10px 20px; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; border: none; cursor: pointer; margin: 0 5px; font-family: sans-serif; }
    </style>
</head>
<body>

    <?php if(!isset($_GET['raw'])): ?>
    <!-- PDF Generation Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <div class="no-print-area">
        <button onclick="downloadPDF()" class="btn" style="background: #22c55e;">
            <i class="fa-solid fa-file-arrow-down"></i> Download PDF Now
        </button>
        <button onclick="window.print()" class="btn" style="background: #2563eb;">Print Manually</button>
        <a href="receipt.php?id=<?php echo $orderId; ?>" class="btn" style="background: #64748b;">Back</a>
    </div>

    <script>
    function downloadPDF() {
        const element = document.getElementById('invoice');
        const opt = {
            margin:       [0.5, 0.5],
            filename:     'Invoice_GC-<?php echo str_pad($orderId, 5, '0', STR_PAD_LEFT); ?>.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, letterRendering: true },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        // Show loading state
        const btn = document.querySelector('.btn[onclick="downloadPDF()"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
        btn.disabled = true;

        html2pdf().set(opt).from(element).save().then(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }).catch(err => {
            console.error('PDF Error:', err);
            btn.innerHTML = 'Error! Try Manual Print';
            btn.disabled = false;
        });
    }

    // Auto-trigger download if requested via URL
    window.onload = function() {
        if (window.location.search.indexOf('download=1') > -1) {
            setTimeout(downloadPDF, 1000);
        }
    };
    </script>

    <!-- CLEAN ROOM INVOICE BOX -->
    <div id="invoice" class="invoice-container">
        
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td style="padding: 30px;">
                    <h2 style="font-size: 24px; color: #0f172a; margin: 0;"><?php echo getSetting('site_name'); ?></h2>
                </td>
                <td style="padding: 30px; text-align: right; vertical-align: top;">
                    <h3 style="font-size: 14px; margin: 0; color: #64748b; text-transform: uppercase;">Tax Invoice/Bill of Supply</h3>
                    <p style="font-size: 12px; color: #94a3b8; margin: 5px 0 0 0;">(Original for Recipient)</p>
                </td>
            </tr>
        </table>

        <!-- Info Grid -->
        <table class="info-table">
            <tr>
                <td style="width: 50%; padding: 30px; vertical-align: top; border-right: 1px solid #e2e8f0;">
                    <p style="font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 10px;">Sold By :</p>
                    <p style="font-weight: 800; font-size: 15px; margin-bottom: 5px;"><?php echo getSetting('seller_name'); ?></p>
                    <p style="font-size: 13px; color: #475569; line-height: 1.6;"><?php echo getSetting('seller_address'); ?></p>
                    <div style="font-size: 13px; margin-top: 15px;">
                        <p style="margin: 0 0 5px 0;"><span style="font-weight: 700;">PAN No:</span> <?php echo getSetting('seller_pan'); ?></p>
                        <p style="margin: 0;"><span style="font-weight: 700;">GSTIN No:</span> <?php echo getSetting('seller_gstin'); ?></p>
                    </div>
                </td>
                <td style="width: 50%; padding: 30px; vertical-align: top;">
                    <p style="font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 10px;">Billing Address :</p>
                    <p style="font-weight: 800; font-size: 15px; margin-bottom: 5px;"><?php echo $order['customer_name']; ?></p>
                    <p style="font-size: 13px; color: #475569; line-height: 1.6;"><?php echo $order['delivery_address']; ?></p>
                </td>
            </tr>
        </table>

        <!-- Meta Table -->
        <table class="meta-table">
            <tr>
                <td style="padding: 20px 30px; font-size: 13px; line-height: 1.6;">
                    <p style="margin: 0;"><span style="font-weight: 700;">Order #:</span> <?php echo str_pad($order['id'], 18, '404-0000000-', STR_PAD_LEFT); ?></p>
                    <p style="margin: 0;"><span style="font-weight: 700;">Date:</span> <?php echo date('d.m.Y', strtotime($order['order_date'])); ?></p>
                </td>
                <td style="padding: 20px 30px; text-align: right; font-size: 13px; line-height: 1.6;">
                    <p style="margin: 0;"><span style="font-weight: 700;">Invoice #:</span> <strong>INV-GC-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></strong></p>
                </td>
            </tr>
        </table>

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Tax</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): 
                    $item_hsn = $item['hsn_code'] ?? '3304';
                    $item_tax_rate = $item['tax_percent'] ?? 18;
                    $sub = $item['price'] * $item['quantity'];
                    $tax_val = $sub * ($item_tax_rate / 100);
                    $tot = $sub + $tax_val;
                ?>
                <tr>
                    <td style="font-weight: 700;"><?php echo htmlspecialchars($item['name']); ?> <br><small style="color:#64748b; font-weight:400;">HSN: <?php echo $item_hsn; ?></small></td>
                    <td style="text-align: right;"><?php echo number_format($item['price'], 2); ?></td>
                    <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                    <td style="text-align: right;"><?php echo $item_tax_rate; ?>%</td>
                    <td style="text-align: right; font-weight: 800;"><?php echo number_format($tot, 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr style="background: #f8fafc;">
                    <td colspan="4" style="text-align: right; text-transform: uppercase; font-weight: 700; padding: 10px 15px;">Subtotal</td>
                    <td style="text-align: right; font-weight: 700; padding: 10px 15px;">₹<?php echo number_format($display_subtotal, 2); ?></td>
                </tr>
                <tr style="background: #f8fafc;">
                    <td colspan="4" style="text-align: right; text-transform: uppercase; font-weight: 700; padding: 10px 15px;">GST (Integrated Tax) <?php echo $gst_rate; ?>%</td>
                    <td style="text-align: right; font-weight: 700; padding: 10px 15px;">₹<?php echo number_format($display_gst, 2); ?></td>
                </tr>
                <tr style="background: #0f172a; color: white; font-weight: 800;">
                    <td colspan="4" style="text-align: right; text-transform: uppercase; padding: 15px;">Grand Total</td>
                    <td style="text-align: right; font-size: 18px; padding: 15px;">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <table style="padding: 30px;">
            <tr>
                <td style="padding: 30px; vertical-align: top;">
                    <p style="font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px;">Amount in Words :</p>
                    <p style="font-weight: 800; font-size: 14px;"><?php echo $amount_in_words; ?> Rupees Only</p>
                </td>
                <td style="padding: 30px; text-align: right; vertical-align: bottom;">
                    <div style="margin-bottom: 15px;">
                        <img src="assets/img/signature.png" style="height: 60px;" onerror="this.style.display='none'">
                    </div>
                    <p style="font-weight: 800; text-decoration: overline; margin: 0;">Authorized Signatory</p>
                </td>
            </tr>
        </table>

        <div class="footer-strip">
            Computer generated invoice. No signature required. Thank you for shopping!
        </div>
    </div>

</body>
</html>

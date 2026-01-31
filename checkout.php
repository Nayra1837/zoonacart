<?php
require_once 'includes/functions.php';

if (!isLoggedIn()) header("Location: login.php");
if (empty($_SESSION['cart'])) header("Location: shop.php");

$subtotal = 0;
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products_data = $stmt->fetchAll();
$products = [];
foreach($products_data as $p) {
    $products[$p['id']] = $p;
}

$gst_amount = 0;
foreach ($products as $id => $p) {
    $qty = $_SESSION['cart'][$id];
    $item_subtotal = $p['price'] * $qty;
    $item_tax = $item_subtotal * (($p['tax_percent'] ?? 18) / 100);
    $gst_amount += $item_tax;
}
$total = $subtotal + $gst_amount;
$gst_rate = 18; // Fallback for display, but logic uses product rates

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'] . ', ' . $_POST['city'] . ', ' . $_POST['zip'];
    
    try {
        $pdo->beginTransaction();
        
        
        $payment_method = $_POST['payment_method'] ?? 'cod';
        
        // Handle Wallet Payment
        if ($payment_method === 'wallet') {
            $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $balance = $stmt->fetchColumn();
            
            if ($balance < $total) {
                throw new Exception("Insufficient wallet balance");
            }
            
            // Deduct Balance
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
            $stmt->execute([$total, $_SESSION['user_id']]);
            
            // Record Transaction
            $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'purchase', ?)");
            $stmt->execute([$_SESSION['user_id'], -$total, "Order Payment"]);
        }

        // FAIL-SAFE: Auto-check if columns exist (Prevents "Column not found" error)
        try {
            $pdo->query("SELECT subtotal_amount FROM orders LIMIT 1");
        } catch (Exception $e) {
            // Column missing, try to add it on the fly
            $pdo->exec("ALTER TABLE orders ADD COLUMN subtotal_amount DECIMAL(10,2) DEFAULT 0.00 AFTER total_amount");
            $pdo->exec("ALTER TABLE orders ADD COLUMN gst_amount DECIMAL(10,2) DEFAULT 0.00 AFTER subtotal_amount");
        }

        // Create Order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, subtotal_amount, gst_amount, delivery_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $total, $subtotal, $gst_amount, $address]);
        $orderId = $pdo->lastInsertId();
        
        // Create Order Items
        foreach ($_SESSION['cart'] as $pid => $qty) {
            $p = $products[$pid];
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, hsn_code, tax_percent) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$orderId, $pid, $qty, $p['price'], $p['hsn_code'] ?? '3304', $p['tax_percent'] ?? 18]);
            
            // Update Stock
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$qty, $pid]);
        }
        
        $pdo->commit();
        $_SESSION['cart'] = []; // Clear cart
        header("Location: receipt.php?id=" . $orderId);
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Order processing failed: " . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<style>
    /* FIX FOR SCROLLING: Ensure page is naturally scrollable */
    html, body { 
        overflow-y: auto !important; 
        height: auto !important; 
        position: relative !important;
    }
    
    .checkout-wrapper {
        display: flex;
        gap: 2rem;
        align-items: start;
    }
    .checkout-main { flex: 1.5; }
    .checkout-side { flex: 1; position: sticky; top: 100px; }

    /* MOBILE ADJUSTMENTS: Override global restrictive container padding */
    @media (max-width: 992px) {
        .container { 
            padding-top: 140px !important; /* Reduced from 180px to see more content */
            overflow: visible !important; 
        }
        .checkout-wrapper { flex-direction: column; gap: 3rem; }
        .checkout-side { position: relative; top: 0; width: 100%; }
        .checkout-main { width: 100%; }
    }
    
    @media (max-width: 600px) {
        .container { padding: 120px 4% 100px 4% !important; }
        .glass { padding: 1.5rem !important; }
        h1 { font-size: 1.8rem !important; margin-bottom: 1.5rem !important; }
        
        /* Larger touch targets for mobile */
        textarea, input {
            font-size: 16px !important; /* Prevents auto-zoom on iOS */
            padding: 1.2rem !important;
        }
    }
</style>

<div class="container" style="padding: 3rem 5%;">
    <div class="checkout-wrapper">
        <!-- Shipping Form -->
        <div class="checkout-main">
            <h1 style="font-size: 2.5rem; margin-bottom: 2rem;">Shipping Details</h1>
            
            <?php if($error): ?>
                <div style="background: #fee2e2; color: #b91c1c; padding: 1rem; margin-bottom: 2rem; border: 1px solid #fecaca;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="glass" style="padding: 2.5rem; border-radius: 0;">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.85rem; color: #475569;">Delivery Address</label>
                    <textarea name="address" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none; resize: none;" rows="3" placeholder="Street name, apartment, etc."></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.85rem; color: #475569;">City</label>
                        <input type="text" name="city" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;" placeholder="Mumbai">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.85rem; color: #475569;">Postal Code</label>
                        <input type="text" name="zip" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;" placeholder="400001">
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem; font-size: 1rem;">Payment Method</h3>
                    
                    <!-- Wallet Option -->
                    <?php
                    $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $wallet_balance = $stmt->fetchColumn() ?: 0;
                    $can_pay_wallet = $wallet_balance >= $total;
                    ?>
                    
                    <label style="cursor: pointer; display: block; margin-bottom: 1rem;">
                        <input type="radio" name="payment_method" value="wallet" <?php echo !$can_pay_wallet ? 'disabled' : ''; ?> style="display: none;" onchange="document.querySelectorAll('.payment-box').forEach(b => b.style.borderColor='transparent'); this.parentElement.querySelector('.payment-box').style.borderColor='var(--primary)';">
                        <div class="payment-box" style="padding: 1.5rem; border: 2px solid transparent; background: #fff1f2; display: flex; align-items: center; justify-content: space-between; transition: 0.2s; <?php echo !$can_pay_wallet ? 'opacity: 0.6;' : ''; ?>">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <i class="fa-solid fa-wallet" style="color: var(--primary); font-size: 1.3rem;"></i>
                                <div>
                                    <p style="font-weight: 700;">ZoonaCart Wallet (<?php echo formatPrice($wallet_balance); ?>)</p>
                                    <?php if(!$can_pay_wallet): ?>
                                        <p style="font-size: 0.8rem; color: #ef4444;">Insufficient balance</p>
                                    <?php else: ?>
                                        <p style="font-size: 0.8rem; color: #64748b;">Pay instantly with your balance</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if($can_pay_wallet): ?>
                                <i class="fa-solid fa-circle-check" style="color: var(--primary); font-size: 1.2rem;"></i>
                            <?php endif; ?>
                        </div>
                    </label>

                    <!-- COD Option -->
                    <label style="cursor: pointer; display: block;">
                        <input type="radio" name="payment_method" value="cod" checked style="display: none;" onchange="document.querySelectorAll('.payment-box').forEach(b => b.style.borderColor='transparent'); this.parentElement.querySelector('.payment-box').style.borderColor='var(--primary)';">
                        <div class="payment-box" style="padding: 1.5rem; border: 2px solid var(--primary); background: #fff1f2; display: flex; align-items: center; justify-content: space-between; transition: 0.2s;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <i class="fa-solid fa-truck-fast" style="color: var(--primary); font-size: 1.3rem;"></i>
                                <div>
                                    <p style="font-weight: 700;">Cash on Delivery</p>
                                    <p style="font-size: 0.8rem; color: #64748b;">Pay when you receive your package</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-circle-check" style="color: var(--primary); font-size: 1.2rem;"></i>
                        </div>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; font-size: 1rem;">
                    Confirm Order - <?php echo formatPrice($total); ?>
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="checkout-side">
            <div class="glass" style="padding: 2.5rem; border-radius: 0;">
                <h2 style="margin-bottom: 2rem; font-size: 1.5rem;">Order Summary</h2>
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <?php foreach ($products as $id => $p): ?>
                        <div style="display: flex; align-items: center; gap: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
                            <img src="assets/img/<?php echo $p['image']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                            <div style="flex-grow: 1;">
                                <h4 style="margin: 0; font-size: 0.95rem;"><?php echo $p['name']; ?></h4>
                                <p style="font-size: 0.75rem; color: #64748b;">Qty: <?php echo $_SESSION['cart'][$id]; ?></p>
                            </div>
                            <span style="font-weight: 700;"><?php echo formatPrice($p['price'] * $_SESSION['cart'][$id]); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #f1f5f9;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.8rem; color: #64748b; font-weight: 600;">
                        <span>Subtotal</span>
                        <span><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; color: #64748b; font-weight: 600;">
                        <span>Estimated Tax</span>
                        <span><?php echo formatPrice($gst_amount); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                        <span style="font-weight: 700; font-size: 1.1rem;">Total Amount</span>
                        <span style="font-weight: 800; font-size: 1.5rem; color: var(--primary);"><?php echo formatPrice($total); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php
require_once 'includes/functions.php';

if (!isLoggedIn()) header("Location: login.php");
if (empty($_SESSION['cart'])) header("Location: shop.php");

$total = 0;
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products_data = $stmt->fetchAll();
$products = [];
foreach($products_data as $p) {
    $products[$p['id']] = $p;
}

foreach ($products as $id => $p) {
    $total += $p['price'] * $_SESSION['cart'][$id];
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'] . ', ' . $_POST['city'] . ', ' . $_POST['zip'];
    
    try {
        $pdo->beginTransaction();
        
        // Create Order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, delivery_address) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $total, $address]);
        $orderId = $pdo->lastInsertId();
        
        // Create Order Items
        foreach ($_SESSION['cart'] as $pid => $qty) {
            $p = $products[$pid];
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $pid, $qty, $p['price']]);
            
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

<div class="container" style="padding: 3rem 5%;">
    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 4rem; align-items: start;">
        <!-- Shipping Form -->
        <div>
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
                    <div style="padding: 1.5rem; border: 2px solid var(--primary); display: flex; align-items: center; justify-content: space-between; background: #fff1f2;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <i class="fa-solid fa-truck-fast" style="color: var(--primary); font-size: 1.3rem;"></i>
                            <div>
                                <p style="font-weight: 700;">Cash on Delivery</p>
                                <p style="font-size: 0.8rem; color: #64748b;">Pay when you receive your package</p>
                            </div>
                        </div>
                        <i class="fa-solid fa-circle-check" style="color: var(--primary); font-size: 1.2rem;"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; font-size: 1rem;">
                    Confirm Order - <?php echo formatPrice($total); ?>
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="glass" style="padding: 2.5rem; border-radius: 0; position: sticky; top: 100px;">
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
            
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 700; font-size: 1.1rem;">Total Amount</span>
                <span style="font-weight: 800; font-size: 1.5rem; color: var(--primary);"><?php echo formatPrice($total); ?></span>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

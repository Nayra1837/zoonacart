<?php
require_once 'includes/functions.php';
include 'includes/header.php';
?>

<main class="container" style="padding: 3rem 5%;">
    <h1 style="font-size: 3rem; margin-bottom: 2rem;">Your Shopping Bag</h1>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 4rem;">
        <div id="cartItems">
            <!-- Cart items loaded by app.js -->
        </div>
        
        <div id="summary">
            <div class="glass" style="padding: 3rem; border-radius: 0; position: sticky; top: 120px;">
                <h2 style="margin-bottom: 2rem;">Order Summary</h2>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: #64748b;">
                    <span>Subtotal</span>
                    <span id="cartTotal">₹0.00</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 2rem; color: #64748b;">
                    <span>Shipping</span>
                    <span style="color: var(--primary); font-weight: 800;">FREE</span>
                </div>
                <hr style="border: none; border-top: 1px solid #e2e8f0; margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; font-size: 1.5rem; font-weight: 800; margin-bottom: 2rem;">
                    <span>Total</span>
                    <span class="gradient-text" id="grandTotal">₹0.00</span>
                </div>
                <a href="checkout.php" class="btn btn-primary" style="width: 100%; text-align: center; padding: 1.2rem;">Proceed to Checkout</a>
            </div>
        </div>
    </div>
</main>

<script src="js/app.js"></script>
<script>
    // Sync grand total with cart total
    const observer = new MutationObserver(() => {
        document.getElementById('grandTotal').innerText = document.getElementById('cartTotal').innerText;
    });
    const target = document.getElementById('cartTotal');
    if (target) {
        observer.observe(target, { childList: true });
    }
</script>

<?php include 'includes/footer.php'; ?>

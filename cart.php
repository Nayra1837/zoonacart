<?php
require_once 'includes/functions.php';
include 'includes/header.php';
?>

<main class="container" style="padding: 3.5rem 5%;">
    <h1 style="font-size: 2.5rem; margin-bottom: 2rem; font-weight: 800;">Your Shopping Bag</h1>
    
    <div id="cartContainer">
        <?php if (getCartCount() == 0): ?>
            <!-- Instant Empty State -->
            <div id="emptyCart" class="text-center" style="padding: 100px 0; background: white; border-radius: 20px;">
                <i class="fa-solid fa-bag-shopping" style="font-size: 5rem; color: #e2e8f0; margin-bottom: 2rem;"></i>
                <h2 style="font-size: 2rem; color: #1e293b;">Your bag is empty</h2>
                <p style="color: #64748b; margin-top: 1rem; margin-bottom: 2rem;">Looks like you haven't added anything to your cart yet.</p>
                <a href="shop.php" class="btn btn-primary" style="padding: 1rem 3rem; font-weight: 700; border-radius: 8px;">Start Shopping</a>
            </div>
        <?php else: ?>
            <!-- Instant Grid Layout (Visible immediately) -->
            <div id="cartContent" class="cart-grid">
                <div id="cartItems">
                    <!-- Loading spinner inside the items area -->
                    <div id="cartLoading" style="text-align: center; padding: 4rem; color: #64748b;">
                        <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                        <p>Loading items...</p>
                    </div>
                </div>
                
                <div id="summary">
                    <div class="glass" style="padding: 2.5rem; border-radius: 0; position: sticky; top: 100px; background: white; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);">
                        <h2 style="margin-bottom: 2rem; font-size: 1.5rem;">Order Summary</h2>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: #64748b; font-size: 0.95rem;">
                            <span>Subtotal</span>
                            <span id="cartTotal">₹0.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 2rem; color: #64748b; font-size: 0.95rem;">
                            <span>Shipping</span>
                            <span style="color: var(--primary); font-weight: 800;">FREE</span>
                        </div>
                        <hr style="border: none; border-top: 1px solid #f1f5f9; margin-bottom: 2rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 1.6rem; font-weight: 800; margin-bottom: 2rem; color: var(--dark);">
                            <span>Total</span>
                            <span class="gradient-text" id="grandTotal">₹0.00</span>
                        </div>
                        <a href="checkout.php" class="btn btn-primary" style="width: 100%; text-align: center; padding: 1.2rem; font-weight: 700; border-radius: 8px;">Proceed to Checkout</a>
                        <p style="text-align: center; font-size: 0.75rem; color: #94a3b8; margin-top: 1.5rem;">
                            <i class="fa-solid fa-shield-halved"></i> 100% Secure Payments
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    /* Consistently applied grid layout */
    .cart-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        align-items: start;
    }

    @media (min-width: 1024px) {
        .cart-grid {
            grid-template-columns: 2fr 1fr;
            gap: 4rem;
        }
    }

    /* Animation for cart items */
    .cart-item-animate {
        animation: slideUp 0.4s ease forwards;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script src="js/app.js?v=<?php echo time(); ?>"></script>
<script>
    // Force grand total update when cartTotal changes
    const target = document.getElementById('cartTotal');
    if (target) {
        const observer = new MutationObserver(() => {
            document.getElementById('grandTotal').innerText = target.innerText;
        });
        observer.observe(target, { childList: true });
    }
</script>

<?php include 'includes/footer.php'; ?>

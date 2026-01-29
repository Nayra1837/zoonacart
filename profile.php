<?php
require_once 'includes/functions.php';
if (!isLoggedIn()) redirect('login.php');
include 'includes/header.php';
?>

<main class="container" style="padding: 3.5rem 5%;">
    <div id="profileContent">
        <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 2.5rem;">
            <div id="userInitials" style="width: 100px; height: 100px; background: var(--primary); color: white; border-radius: 0; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 800; box-shadow: var(--shadow);">
                <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
            </div>
            <div>
                <h1 id="userName" style="font-size: 3rem; margin-bottom: 0.5rem;"><?php echo $_SESSION['name']; ?></h1>
                <p style="color: #64748b;">GlamCart Member</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 3rem; align-items: start;">
            <!-- Order History -->
            <div class="glass" style="padding: 3rem; border-radius: 0;">
                <h2 style="margin-bottom: 2rem;">Your Recent Orders</h2>
                <div id="orderHistory">
                    <p style="color: #94a3b8; text-align: center; padding: 4rem;">Loading your beauty history...</p>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="glass" style="padding: 3rem; border-radius: 0;">
                <h2 style="margin-bottom: 2rem;">Security</h2>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 2rem;">Update your account password to keep your profile secure.</p>
                
                <form id="passwordForm" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">New Password</label>
                        <input type="password" id="new_password" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Confirm New Password</label>
                        <input type="password" id="confirm_password" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                    </div>
                    <button type="submit" class="btn btn-dark" style="width: 100%; padding: 1rem;">Update Password</button>
                    <div id="passwordMessage" style="font-size: 0.85rem; font-weight: 600;"></div>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="js/app.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        // Fetch actual orders
        const ordersRes = await fetch('api/main.php?action=get_orders');
        const orders = await ordersRes.json();
        const container = document.getElementById('orderHistory');

        if (orders.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #94a3b8;">No orders found yet.</p>';
        } else {
            container.innerHTML = `<table style="width: 100%; border-collapse: collapse;">
                <tr style="text-align: left; color: #64748b; font-size: 0.8rem; border-bottom: 1px solid #eee;">
                    <th style="padding: 1rem;">ORDER ID</th>
                    <th style="padding: 1rem;">DATE</th>
                    <th style="padding: 1rem;">STATUS</th>
                    <th style="padding: 1rem; text-align: right;">TOTAL</th>
                </tr>
                ${orders.map(o => `
                    <tr style="border-bottom: 1px solid #f8fafc; cursor: pointer;" onclick="window.location='receipt.php?id=${o.id}'">
                        <td style="padding: 1.5rem; font-weight: 600;">#GC-${o.id.toString().padStart(5, '0')}</td>
                        <td style="padding: 1.5rem;">${new Date(o.order_date).toLocaleDateString()}</td>
                        <td style="padding: 1.5rem;"><span style="background: #f1f5f9; padding: 4px 12px; border-radius: 0; font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">${o.status || 'pending'}</span></td>
                        <td style="padding: 1.5rem; text-align: right; font-weight: 800; color: var(--primary);">₹${parseFloat(o.total_amount).toFixed(2)}</td>
                    </tr>
                `).join('')}
            </table>`;
        }
    });

    // Password Update Handler
    document.getElementById('passwordForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const newPass = document.getElementById('new_password').value;
        const confirmPass = document.getElementById('confirm_password').value;
        const msg = document.getElementById('passwordMessage');

        if (newPass !== confirmPass) {
            msg.style.color = '#e11d48';
            msg.innerText = 'Passwords do not match.';
            return;
        }

        const formData = new FormData();
        formData.append('password', newPass);

        const res = await fetch('api/main.php?action=update_password', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            msg.style.color = '#10b981';
            msg.innerText = 'Password updated successfully!';
            document.getElementById('passwordForm').reset();
        } else {
            msg.style.color = '#e11d48';
            msg.innerText = data.error || 'Failed to update password.';
        }
    });
</script>

<?php include 'includes/footer.php'; ?>

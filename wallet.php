<?php
require_once 'includes/functions.php';
if (!isLoggedIn()) redirect('login.php');
include 'includes/header.php';

// Fetch User Wallet Balance
$stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$balance = $stmt->fetchColumn() ?: 0;
?>

<main class="container" style="padding: 3.5rem 5%;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h1 style="font-size: 2.5rem; margin-bottom: 2rem; text-transform: uppercase; letter-spacing: 1px;">My <span class="gradient-text">Wallet</span></h1>

        <!-- Balance Card -->
        <div style="background: linear-gradient(135deg, var(--primary) 0%, #be185d 100%); color: white; padding: 2.5rem; position: relative; overflow: hidden; border-radius: 20px; box-shadow: 0 10px 30px rgba(225, 29, 72, 0.2); margin-bottom: 3rem;">
            <i class="fa-solid fa-wallet" style="position: absolute; right: -20px; bottom: -20px; font-size: 10rem; opacity: 0.1;"></i>
            <p style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 0.5rem; opacity: 0.9;">Available Balance</p>
            <h2 style="font-size: 3.5rem; font-weight: 800; margin-bottom: 1.5rem;"><?php echo formatPrice($balance); ?></h2>
            <button onclick="openWithdrawModal()" class="btn" style="background: white; color: var(--primary); padding: 0.8rem 2rem; font-weight: 600; border-radius: 10px; border: none; cursor: pointer; transition: 0.3s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-solid fa-money-bill-transfer"></i> Withdraw Funds
            </button>
        </div>

        <!-- Transaction History -->
        <div class="glass" style="padding: 2.5rem; border-radius: 20px;">
            <h3 style="margin-bottom: 2rem; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-clock-rotate-left"></i> Transaction History
            </h3>
            <div id="walletHistory">
                <p style="color: #94a3b8; text-align: center; padding: 3rem;">Fetching your financial trail...</p>
            </div>
        </div>
    </div>

    <!-- Withdraw Modal -->
    <div id="withdrawModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.75); z-index: 10000; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
        <div class="glass" style="background: white; padding: 2.5rem; width: 90%; max-width: 450px; position: relative; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.3);">
            <button onclick="closeWithdrawModal()" style="position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94a3b8;"><i class="fa-solid fa-xmark"></i></button>
            <h2 style="margin-bottom: 1.5rem; font-size: 1.8rem;">Request Withdrawal</h2>
            <form onsubmit="submitWithdrawal(event)">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: #64748b;">Amount to Withdraw</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-weight: 600;">₹</span>
                        <input type="number" id="withdrawAmount" required min="1" max="<?php echo $balance; ?>" style="width: 100%; padding: 1rem 1rem 1rem 2.5rem; border: 2px solid #f1f5f9; outline: none; border-radius: 12px; font-size: 1.1rem; font-weight: 600;" placeholder="0.00">
                    </div>
                    <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 5px;">Max: <?php echo formatPrice($balance); ?></p>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: #64748b;">Withdrawal Method</label>
                    <select id="withdrawMethod" style="width: 100%; padding: 1rem; border: 2px solid #f1f5f9; outline: none; border-radius: 12px; font-size: 1rem;">
                        <option value="UPI">UPI (GPay / PhonePe / Paytm)</option>
                        <option value="Bank Transfer">Direct Bank Transfer</option>
                    </select>
                </div>
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: #64748b;">Payment Details (UPI ID / Bank Account)</label>
                    <textarea id="withdrawDetails" required rows="3" style="width: 100%; padding: 1rem; border: 2px solid #f1f5f9; outline: none; border-radius: 12px; font-size: 1rem; resize: none;" placeholder="Enter your UPI ID or Bank account details..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; border-radius: 12px; font-weight: 700; font-size: 1rem; text-transform: uppercase; letter-spacing: 1px;">Confirm Request</button>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', loadWalletHistory);

async function loadWalletHistory() {
    const container = document.getElementById('walletHistory');
    try {
        const res = await fetch('api/main.php?action=get_wallet_history');
        const data = await res.json();

        if (data.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #94a3b8; padding: 3rem;">No transactions yet.</p>';
            return;
        }

        container.innerHTML = `
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                ${data.map(t => `
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.2rem; border-radius: 15px; background: #f8fafc; border: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: ${t.amount < 0 ? '#fee2e2' : '#dcfce7'}; color: ${t.amount < 0 ? '#ef4444' : '#22c55e'}; font-size: 1.2rem;">
                                <i class="fa-solid ${t.amount < 0 ? 'fa-arrow-up' : 'fa-arrow-down'}"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 1rem; margin-bottom: 2px;">${t.description}</h4>
                                <p style="font-size: 0.75rem; color: #94a3b8;">${new Date(t.created_at).toLocaleString()}</p>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-weight: 800; font-size: 1.1rem; color: ${t.amount < 0 ? '#ef4444' : '#22c55e'};">
                                ${t.amount < 0 ? '-' : '+'}₹${Math.abs(t.amount).toFixed(2)}
                            </span>
                            <p style="font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">${t.type}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    } catch (err) {
        container.innerHTML = '<p style="text-align: center; color: #ef4444;">Failed to load history.</p>';
    }
}

function openWithdrawModal() {
    document.getElementById('withdrawModal').style.display = 'flex';
}

function closeWithdrawModal() {
    document.getElementById('withdrawModal').style.display = 'none';
}

async function submitWithdrawal(e) {
    e.preventDefault();
    const amount = document.getElementById('withdrawAmount').value;
    const method = document.getElementById('withdrawMethod').value;
    const details = document.getElementById('withdrawDetails').value;
    
    const formData = new FormData();
    formData.append('amount', amount);
    formData.append('method', method);
    formData.append('details', details);
    
    try {
        const res = await fetch('api/main.php?action=request_withdrawal', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        if(result.success) {
            alert('Withdrawal request submitted successfully!');
            location.reload();
        } else {
            alert(result.error);
        }
    } catch(err) {
        alert('Error processing request.');
    }
}
</script>

<?php include 'includes/footer.php'; ?>

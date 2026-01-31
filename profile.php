<?php
require_once 'includes/functions.php';
if (!isLoggedIn()) redirect('login.php');
include 'includes/header.php';

// Fetch User Details
$stmt = $pdo->prepare("SELECT name, email, phone, profile_pic FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<main class="container" style="padding: 3.5rem 5%;">
    <div id="profileContent">
        <div style="display: flex; flex-direction: column; align-items: center; text-align: center; margin-bottom: 3rem;">
            <!-- Profile Picture Section -->
            <div style="position: relative; margin-bottom: 1.5rem;">
                <div id="profilePicContainer" style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; border: 4px solid white; box-shadow: 0 10px 25px rgba(0,0,0,0.1); background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                    <?php if(!empty($user['profile_pic'])): ?>
                        <img src="assets/img/profiles/<?php echo $user['profile_pic']; ?>" id="profileImage" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fa-solid fa-user" style="font-size: 5rem; color: #cbd5e1;"></i>
                    <?php endif; ?>
                </div>
                <button onclick="document.getElementById('profilePicInput').click()" style="position: absolute; bottom: 5px; right: 5px; background: var(--primary); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(225, 29, 72, 0.3);">
                    <i class="fa-solid fa-camera"></i>
                </button>
                <input type="file" id="profilePicInput" style="display: none;" accept="image/*" onchange="uploadProfilePic(this)">
            </div>

            <h1 id="userName" style="font-size: 2.5rem; margin-bottom: 0.5rem; text-transform: uppercase; font-weight: 800;"><?php echo htmlspecialchars($user['name']); ?></h1>
            <p style="color: #64748b; margin-bottom: 1.5rem;"><?php echo htmlspecialchars($user['email']); ?></p>

            <div style="display: flex; gap: 1rem;">
                <button onclick="openSettingsModal()" class="btn" style="background: white; border: 1px solid #e2e8f0; padding: 0.7rem 1.5rem; border-radius: 10px; color: #1e293b; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-user-pen"></i> Edit Profile
                </button>
                <a href="wallet.php" class="btn btn-primary" style="padding: 0.7rem 1.5rem; border-radius: 10px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-wallet"></i> My Wallet
                </a>
            </div>
        </div>

        <!-- Settings Modal -->
        <div id="settingsModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 99999; justify-content: center; align-items: center; overflow-y: auto; padding: 20px;">
            <div class="glass" style="background: white; padding: 2.5rem 1.5rem 2rem; width: 100%; max-width: 450px; position: relative; border-radius: 15px; margin: auto;">
                <button onclick="document.getElementById('settingsModal').style.display='none'" style="position: absolute; top: 10px; right: 10px; background: #f1f5f9; border: none; font-size: 1.4rem; cursor: pointer; color: #64748b; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <div style="margin-bottom: 2rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1.5rem;">
                    <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;">Account Settings</h2>
                </div>

                <form onsubmit="updateProfile(event)">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; color: #64748b;">Full Name</label>
                        <input type="text" id="editName" value="<?php echo htmlspecialchars($user['name']); ?>" required style="width: 100%; padding: 0.8rem; border: 1px solid #e2e8f0; outline: none; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; color: #64748b;">Email Address</label>
                        <input type="email" id="editEmail" value="<?php echo htmlspecialchars($user['email']); ?>" required style="width: 100%; padding: 0.8rem; border: 1px solid #e2e8f0; outline: none; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; color: #64748b;">Phone Number</label>
                        <input type="tel" id="editPhone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required placeholder="Enter phone number" style="width: 100%; padding: 0.8rem; border: 1px solid #e2e8f0; outline: none; border-radius: 8px;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; border-radius: 8px; font-weight: 700;">Save Changes</button>
                </form>

                <div style="margin: 2rem 0; height: 1px; background: #f1f5f9;"></div>

                <a href="logout.php" class="btn" style="width: 100%; background: #fee2e2; color: #ef4444; text-align: center; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem; font-weight: 600;">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>

        <!-- Order History Section -->
        <div style="margin-top: 4rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 style="font-size: 1.5rem; margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-box-open" style="color: var(--primary);"></i> My Recent Orders
                </h2>
                <a href="orders.php" style="color: var(--primary); font-weight: 700; text-decoration: none; font-size: 0.9rem;">View All <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div id="orderHistory" class="glass" style="padding: 2rem; border-radius: 15px;">
                <p style="color: #94a3b8; text-align: center; padding: 4rem;">Loading your beauty history...</p>
            </div>
        </div>
    </div>
</main>

<script>
async function uploadProfilePic(input) {
    if (!input.files || !input.files[0]) return;
    
    const formData = new FormData();
    formData.append('profile_pic', input.files[0]);
    
    try {
        const res = await fetch('api/main.php?action=upload_profile_pic', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        if(result.success) {
            alert('Profile picture updated!');
            location.reload();
        } else {
            alert(result.error);
        }
    } catch(err) {
        alert('Error uploading image.');
    }
}

function openSettingsModal() {
    document.getElementById('settingsModal').style.display = 'flex';
}

async function updateProfile(e) {
    e.preventDefault();
    const name = document.getElementById('editName').value;
    const email = document.getElementById('editEmail').value;
    const phone = document.getElementById('editPhone').value;

    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('phone', phone);

    try {
        const res = await fetch('api/main.php?action=update_profile', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        if(result.success) {
            alert('Profile updated successfully!');
            location.reload();
        } else {
            alert(result.error);
        }
    } catch(err) {
        alert('Error updating profile');
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const ordersRes = await fetch('api/main.php?action=get_orders');
    const orders = await ordersRes.json();
    const container = document.getElementById('orderHistory');

    if (orders.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #94a3b8; padding: 2rem;">No orders found yet. <a href="shop.php" style="color: var(--primary); font-weight: 600;">Start Shopping</a></p>';
    } else {
        container.innerHTML = `
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; color: #64748b; font-size: 0.8rem; border-bottom: 1px solid #f1f5f9;">
                            <th style="padding: 1rem;">ORDER</th>
                            <th style="padding: 1rem;">DATE</th>
                            <th style="padding: 1rem;">STATUS</th>
                            <th style="padding: 1rem; text-align: right;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${orders.map(o => `
                            <tr style="border-bottom: 1px solid #f8fafc; transition: 0.2s;" onmouseover="this.style.background='#fcfcfc'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 1.2rem; font-weight: 600; cursor: pointer;" onclick="window.location='receipt.php?id=${o.id}'">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <img src="assets/img/${o.order_image}" style="width: 45px; height: 45px; object-fit: cover; border-radius: 8px;">
                                        <span>#${o.id.toString().padStart(5, '0')}</span>
                                    </div>
                                </td>
                                <td style="padding: 1.2rem; color: #64748b; font-size: 0.9rem;">${new Date(o.order_date).toLocaleDateString()}</td>
                                <td style="padding: 1.2rem;">
                                    <span style="background: ${o.status === 'completed' ? '#dcfce7' : '#f1f5f9'}; color: ${o.status === 'completed' ? '#15803d' : '#64748b'}; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                                        ${o.status || 'pending'}
                                    </span>
                                </td>
                                <td style="padding: 1.2rem; text-align: right; font-weight: 800; color: var(--dark);">₹${parseFloat(o.total_amount).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>`;
    }
});
</script>

<?php include 'includes/footer.php'; ?>

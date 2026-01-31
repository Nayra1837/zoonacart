<?php
require_once 'includes/functions.php';
if (isLoggedIn()) redirect('index.php');
include 'includes/header.php';
?>

<div class="container" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
    <div class="glass animate" style="max-width: 450px; width: 100%; padding: 3rem; border-radius: 0;">
        <div id="forgotStep1">
            <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 1rem;">Reset Password</h2>
            <p style="color: #64748b; margin-bottom: 2rem;">Enter your registered email to receive a recovery code.</p>
            
            <form onsubmit="handleRequestReset(event)">
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">Email Address</label>
                    <input type="email" id="resetEmail" required style="width: 100%; padding: 1rem; border: 1px solid #e2e8f0; outline: none;">
                </div>
                <button type="submit" id="requestBtn" class="btn btn-primary" style="width: 100%; padding: 1rem;">Send Reset Code</button>
            </form>
        </div>

        <div id="forgotStep2" style="display: none;">
            <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 1rem;">Verify Code</h2>
            <p style="color: #64748b; margin-bottom: 2rem;">Enter the 6-digit code sent to your email.</p>
            
            <form onsubmit="handleVerifyReset(event)">
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">Recovery Code</label>
                    <input type="text" id="resetOTP" required maxlength="6" style="width: 100%; padding: 1rem; font-size: 1.5rem; letter-spacing: 5px; text-align: center; border: 1px solid #e2e8f0; outline: none;">
                </div>
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">New Password</label>
                    <input type="password" id="resetNewPass" required style="width: 100%; padding: 1rem; border: 1px solid #e2e8f0; outline: none;">
                </div>
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">Confirm New Password</label>
                    <input type="password" id="resetConfirmPass" required style="width: 100%; padding: 1rem; border: 1px solid #e2e8f0; outline: none;">
                </div>
                <button type="submit" id="resetBtn" class="btn btn-primary" style="width: 100%; padding: 1rem;">Reset Password</button>
            </form>
        </div>

        <div id="forgotMessage" style="margin-top: 1.5rem; font-size: 0.9rem; font-weight: 600; text-align: center;"></div>
    </div>
</div>

<script>
    async function handleRequestReset(e) {
        e.preventDefault();
        const email = document.getElementById('resetEmail').value;
        const btn = document.getElementById('requestBtn');
        const msg = document.getElementById('forgotMessage');

        btn.disabled = true;
        btn.innerText = 'Sending...';
        msg.innerText = '';

        const formData = new FormData();
        formData.append('email', email);

        try {
            const res = await fetch('api/main.php?action=request_password_reset', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                document.getElementById('forgotStep1').style.display = 'none';
                document.getElementById('forgotStep2').style.display = 'block';
                msg.style.color = '#16a34a';
                msg.innerText = 'Reset code sent to your email!';
            } else {
                msg.style.color = '#e11d48';
                msg.innerText = data.error || 'Account not found.';
            }
        } catch (err) {
            msg.style.color = '#e11d48';
            msg.innerText = 'Connection error.';
        }
        btn.disabled = false;
        btn.innerText = 'Send Reset Code';
    }

    async function handleVerifyReset(e) {
        e.preventDefault();
        const email = document.getElementById('resetEmail').value;
        const otp = document.getElementById('resetOTP').value;
        const password = document.getElementById('resetNewPass').value;
        const confirmPass = document.getElementById('resetConfirmPass').value;
        const btn = document.getElementById('resetBtn');
        const msg = document.getElementById('forgotMessage');

        if (password !== confirmPass) {
            msg.style.color = '#e11d48';
            msg.innerText = 'Passwords do not match.';
            return;
        }

        btn.disabled = true;
        btn.innerText = 'Resetting...';
        msg.innerText = '';

        const formData = new FormData();
        formData.append('email', email);
        formData.append('otp', otp);
        formData.append('password', password);

        try {
            const res = await fetch('api/main.php?action=reset_password', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                msg.style.color = '#16a34a';
                msg.innerText = 'Password reset successful! Redirecting to login...';
                setTimeout(() => window.location.href = 'login.php', 2000);
            } else {
                msg.style.color = '#e11d48';
                msg.innerText = data.error || 'Invalid code.';
            }
        } catch (err) {
            msg.style.color = '#e11d48';
            msg.innerText = 'Connection error.';
        }
        btn.disabled = false;
        btn.innerText = 'Reset Password';
    }
</script>

<?php include 'includes/footer.php'; ?>

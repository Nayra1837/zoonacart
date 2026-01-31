<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
    $stmt->execute([$email, $email]); // Using $email variable for input which could be either
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
        // Check verification for normal users only (Skip for Admin)
        if ($user['role'] !== 'admin' && $user['is_verified'] == 0) {
            $_SESSION['verify_email'] = $email;
            header("Location: verify.php");
            exit();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}

// Handle Magic Link Login
if (isset($_GET['magic_token'])) {
    $token = $_GET['magic_token'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE magic_token = ? AND magic_token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Clear token
        $pdo->prepare("UPDATE users SET magic_token = NULL, magic_token_expiry = NULL, is_verified = 1 WHERE id = ?")->execute([$user['id']]);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid or expired login link.";
    }
}

include 'includes/header.php';
?>

<div class="container" style="min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
    <div class="glass animate" style="max-width: 450px; width: 100%; padding: 3rem; border: 1px solid #fee2e2;">
        <div class="text-center" style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;">Welcome Back</h2>
            <p style="color: #64748b; font-size: 0.9rem;">Sign in to your beauty dashboard</p>
        </div>

        <?php if ($error): ?>
            <div style="background: #fff1f2; color: #e11d48; padding: 1rem; border-radius: 0; margin-bottom: 2rem; font-size: 0.85rem; font-weight: 600; border: 1px solid #fecdd3; display: flex; align-items: center; gap: 0.8rem;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">Email / Phone</label>
                <input type="text" name="email" placeholder="Email or 10-digit Phone" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none; transition: 0.3s;" onfocus="this.style.borderColor='var(--primary)'">
            </div>
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <label style="font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase; margin: 0;">Password</label>
                    <a href="forgot_password.php" style="font-size: 0.75rem; color: var(--primary); text-decoration: none; font-weight: 600;">Forgot Password?</a>
                </div>
                <input type="password" name="password" placeholder="••••••••" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none; transition: 0.3s;" onfocus="this.style.borderColor='var(--primary)'">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; margin-top: 0.5rem;">Sign In</button>
        </form>
        
        <div style="text-align: center; margin-top: 1rem;">
            <a href="#" onclick="toggleMagicLogin(); return false;" style="font-size: 0.85rem; color: var(--primary); font-weight: 600;">Use Passwordless Login (Magic Link)</a>
        </div>

        <!-- Magic Link Form (Hidden by default) -->
        <form id="magicLoginForm" style="display: none; flex-direction: column; gap: 1.5rem; margin-top: 1.5rem;">
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">Your Email</label>
                <input type="email" id="magicEmail" placeholder="name@example.com" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
            </div>
            <button type="button" onclick="sendMagicLink()" id="magicBtn" class="btn btn-primary" style="width: 100%; padding: 1.2rem; margin-top: 0.5rem; background: #0f172a;">Send Login Link</button>
            <p id="magicMsg" style="font-size: 0.85rem; text-align: center; margin-top: 0.5rem;"></p>
        </form>

        <script>
            function toggleMagicLogin() {
                const nav = document.querySelector('form[action="login.php"]');
                const magic = document.getElementById('magicLoginForm');
                if (magic.style.display === 'none') {
                    nav.style.display = 'none';
                    magic.style.display = 'flex';
                } else {
                    nav.style.display = 'flex';
                    magic.style.display = 'none';
                }
            }

            async function sendMagicLink() {
                const email = document.getElementById('magicEmail').value;
                const btn = document.getElementById('magicBtn');
                const msg = document.getElementById('magicMsg');
                
                if(!email) return;

                btn.disabled = true;
                btn.innerText = "Sending...";
                
                const formData = new FormData();
                formData.append('email', email);
                
                try {
                    const res = await fetch('api/main.php?action=send_magic_link', { method: 'POST', body: formData });
                    const data = await res.json();
                    
                    if(data.success) {
                        msg.style.color = '#10b981';
                        msg.innerText = "Check your email for the login link!";
                    } else {
                        msg.style.color = '#e11d48';
                        msg.innerText = "Something went wrong.";
                    }
                } catch(e) {
                    msg.style.color = '#e11d48';
                    msg.innerText = "Connection error.";
                }
                btn.disabled = false;
                btn.innerText = "Send Login Link";
            }
        </script>

        <div style="display: flex; align-items: center; margin: 2rem 0;">
            <div style="flex: 1; height: 1px; background: #e2e8f0;"></div>
            <span style="padding: 0 1rem; color: #94a3b8; font-size: 0.8rem; text-transform: uppercase;">Or continue with</span>
            <div style="flex: 1; height: 1px; background: #e2e8f0;"></div>
        </div>

        <!-- Google Button Container -->
        <div id="g_id_onload"
             data-client_id="<?php echo getSetting('google_client_id'); ?>"
             data-context="signin"
             data-ux_mode="popup"
             data-callback="handleCredentialResponse"
             data-auto_prompt="false">
        </div>

        <div class="g_id_signin"
             data-type="standard"
             data-shape="rectangular"
             data-theme="outline"
             data-text="continue_with"
             data-size="large"
             data-logo_alignment="left"
             data-width="400">
        </div>

        <script src="https://accounts.google.com/gsi/client" async defer></script>
        
        <script>
        function handleCredentialResponse(response) {
            // Send JWT to backend
            const formData = new FormData();
            formData.append('credential', response.credential);
            
            fetch('api/main.php?action=google_login', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    window.location.href = 'index.php';
                } else {
                    alert(data.error || 'Google Login Failed');
                }
            })
            .catch(err => console.error(err));
        }
        </script>

        <div class="text-center" style="margin-top: 2rem; font-size: 0.9rem; color: #64748b;">
            New to <?php echo getSetting('site_name'); ?>? <a href="register.php" style="color: var(--primary); font-weight: 800; text-decoration: none;">Create account</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

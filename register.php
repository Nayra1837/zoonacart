<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Enable debug mode for this POST request
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    try {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE email = ? OR phone = ?");
            $stmt->execute([$email, $phone]);
            $existingUser = $stmt->fetch();

            if ($existingUser && ($existingUser['is_verified'] ?? 0) == 1) {
                $error = "Email or Phone already registered.";
            } else {
                // Generate NEW OTP
                $otp = rand(100000, 999999);

                // Send Email
                $mailerPath = __DIR__ . '/includes/Mailer.php';
                $mailerPathLower = __DIR__ . '/includes/mailer.php';
                
                if (file_exists($mailerPath)) {
                    require_once $mailerPath;
                } elseif (file_exists($mailerPathLower)) {
                    require_once $mailerPathLower;
                } else {
                    throw new Exception("Critical File Missing: includes/Mailer.php. Please ensure you have uploaded the 'includes' folder from the ZIP to your server.");
                }
                
                $mailer = new Mailer();
                $subject = "Verify your email - " . getSetting('site_name');
                $body = "<h2>Welcome to " . getSetting('site_name') . "!</h2>
                        <p>Your verification code is: <b style='font-size: 24px;'>$otp</b></p>
                        <p>Please enter this code to activate your account.</p>";
                
                if ($mailer->send($email, $subject, $body)) {
                    if ($existingUser) {
                        // Update existing unverified user
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, password = ?, verification_code = ?, email = ?, phone = ? WHERE id = ?");
                        $stmt->execute([$name, $password, $otp, $email, $phone, $existingUser['id']]);
                    } else {
                        // Insert new user
                        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, verification_code, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
                        $stmt->execute([$name, $email, $phone, $password, $otp]);
                    }
                    
                    // Redirect to verification page
                    $_SESSION['verify_email'] = $email;
                    header("Location: verify.php");
                    exit();
                } else {
                    $error = "Failed to send verification email. " . ($mailer->getLastError() ?: "Check SMTP settings.");
                }
            }
        }
    } catch (Throwable $e) {
        $error = "System Crash: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
    }
}

include 'includes/header.php';
?>

<div class="container" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
    <div class="glass animate" style="max-width: 500px; width: 100%; padding: 3rem; border: 1px solid #fee2e2;">
        <div class="text-center" style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;">Join <?php echo getSetting('site_name'); ?></h2>
            <p style="color: #64748b; font-size: 0.9rem;">Create your profile for a personalized experience</p>
        </div>

        <?php if ($error): ?>
            <div style="background: #fff1f2; color: #e11d48; padding: 1rem; border-radius: 0; margin-bottom: 2rem; font-size: 0.85rem; font-weight: 600; border: 1px solid #fecdd3; display: flex; align-items: center; gap: 0.8rem;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div style="grid-column: 1 / -1;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">Full Name</label>
                <input type="text" name="name" placeholder="E.g. Jane Doe" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none; transition: 0.3s;" onfocus="this.style.borderColor='var(--primary)'">
            </div>
            <div style="grid-column: 1 / -1;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">Email Address</label>
                <input type="email" name="email" placeholder="jane@example.com" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none; transition: 0.3s;" onfocus="this.style.borderColor='var(--primary)'">
            </div>
            <div style="grid-column: 1 / -1;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">Phone Number</label>
                <input type="tel" name="phone" placeholder="9876543210" required pattern="[0-9]{10}" title="10 digit mobile number" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none; transition: 0.3s;" onfocus="this.style.borderColor='var(--primary)'">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">Password</label>
                <input type="password" name="password" placeholder="••••••••" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none; transition: 0.3s;" onfocus="this.style.borderColor='var(--primary)'">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">Confirm</label>
                <input type="password" name="confirm_password" placeholder="••••••••" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none; transition: 0.3s;" onfocus="this.style.borderColor='var(--primary)'">
            </div>
            <button type="submit" class="btn btn-primary" style="grid-column: 1 / -1; padding: 1.2rem; margin-top: 0.5rem;">Create Account</button>
        </form>

        <div style="text-align: center; margin-top: 1rem;">
            <a href="#" onclick="toggleMagicSignup(); return false;" style="font-size: 0.85rem; color: var(--primary); font-weight: 600;">Sign Up Passwordless (Magic Link)</a>
        </div>

        <!-- Magic Link Signup Form -->
        <form id="magicSignupForm" style="display: none; flex-direction: column; gap: 1.5rem; margin-top: 1.5rem;">
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">Your Name</label>
                <input type="text" id="magicName" placeholder="John Doe" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">Your Email</label>
                <input type="email" id="magicEmail" placeholder="name@example.com" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
            </div>
            <button type="button" onclick="sendMagicSignup()" id="magicBtn" class="btn btn-primary" style="width: 100%; padding: 1.2rem; margin-top: 0.5rem; background: #0f172a;">Send Signup Link</button>
            <p id="magicMsg" style="font-size: 0.85rem; text-align: center; margin-top: 0.5rem;"></p>
        </form>

        <script>
            function toggleMagicSignup() {
                const nav = document.querySelector('form[action="register.php"]');
                const magic = document.getElementById('magicSignupForm');
                if (magic.style.display === 'none') {
                    nav.style.display = 'none';
                    magic.style.display = 'flex';
                } else {
                    nav.style.display = 'grid';
                    magic.style.display = 'none';
                }
            }

            async function sendMagicSignup() {
                const name = document.getElementById('magicName').value;
                const email = document.getElementById('magicEmail').value;
                const btn = document.getElementById('magicBtn');
                const msg = document.getElementById('magicMsg');
                
                if(!email || !name) return;

                btn.disabled = true;
                btn.innerText = "Sending...";
                
                const formData = new FormData();
                formData.append('email', email);
                formData.append('name', name);
                
                try {
                    const res = await fetch('api/main.php?action=send_magic_link', { method: 'POST', body: formData });
                    const data = await res.json();
                    
                    if(data.success) {
                        msg.style.color = '#10b981';
                        msg.innerText = "Check your email to verify and login!";
                    } else {
                        msg.style.color = '#e11d48';
                        msg.innerText = data.error || "Something went wrong.";
                    }
                } catch(e) {
                    msg.style.color = '#e11d48';
                    msg.innerText = "Connection error.";
                }
                btn.disabled = false;
                btn.innerText = "Send Signup Link";
            }
        </script>

        <div style="display: flex; align-items: center; margin: 2rem 0;">
            <div style="flex: 1; height: 1px; background: #e2e8f0;"></div>
            <span style="padding: 0 1rem; color: #94a3b8; font-size: 0.8rem; text-transform: uppercase;">Or sign up with</span>
            <div style="flex: 1; height: 1px; background: #e2e8f0;"></div>
        </div>

        <!-- Google Button Container -->
        <div id="g_id_onload"
             data-client_id="<?php echo getSetting('google_client_id'); ?>"
             data-context="signup"
             data-ux_mode="popup"
             data-callback="handleCredentialResponse"
             data-auto_prompt="false">
        </div>

        <div class="g_id_signin"
             data-type="standard"
             data-shape="rectangular"
             data-theme="outline"
             data-text="signup_with"
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
                    alert(data.error || 'Google Signup Failed');
                }
            })
            .catch(err => console.error(err));
        }
        </script>

        <div class="text-center" style="margin-top: 2rem; font-size: 0.9rem; color: #64748b;">
            Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 800; text-decoration: none;">Sign in</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

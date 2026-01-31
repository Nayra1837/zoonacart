<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['verify_email'])) {
    header("Location: register.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resend') {
    $email = $_SESSION['verify_email'];
    $otp = rand(100000, 999999);

    try {
        require_once 'includes/Mailer.php';
        $mailer = new Mailer();
        $subject = "Your New Verification Code - " . getSetting('site_name');
        $body = "<h2>Verification Code Resent</h2>
                <p>Your new verification code is: <b style='font-size: 24px;'>$otp</b></p>
                <p>Please enter this code to activate your account.</p>";

        if ($mailer->send($email, $subject, $body)) {
            $stmt = $pdo->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
            $stmt->execute([$otp, $email]);
            $success = "New code sent to your email!";
        } else {
            $error = "Failed to resend email. Check SMTP settings.";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $email = $_SESSION['verify_email'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND verification_code = ?");
    $stmt->execute([$email, $code]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        unset($_SESSION['verify_email']);

        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid verification code.";
    }
}

include 'includes/header.php';
?>

<div class="container" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
    <div class="glass animate" style="max-width: 400px; width: 100%; padding: 3rem; text-align: center;">
        <i class="fa-solid fa-envelope-open-text" style="font-size: 3rem; color: var(--primary); margin-bottom: 1.5rem;"></i>
        <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;">Verify Email</h2>
        <p style="color: #64748b; margin-bottom: 2rem;">Enter the code sent to <b><?php echo $_SESSION['verify_email']; ?></b></p>
        
        <?php if ($error): ?>
            <div style="background: #fff1f2; color: #e11d48; padding: 0.8rem; margin-bottom: 1rem; font-size: 0.9rem; border: 1px solid #fecdd3;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: #f0fdf4; color: #16a34a; padding: 0.8rem; margin-bottom: 1rem; font-size: 0.9rem; border: 1px solid #bbfcbd;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="code" placeholder="Enter 6-digit code" required 
                   style="width: 100%; padding: 1rem; font-size: 1.5rem; letter-spacing: 5px; text-align: center; margin-bottom: 1.5rem; border: 1px solid #e2e8f0; border-radius: 0; outline: none;" 
                   maxlength="6">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Verify</button>
        </form>

        <div style="margin-top: 2rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">Didn't receive the code?</p>
            <form method="POST" id="resendForm">
                <input type="hidden" name="action" value="resend">
                <button type="submit" id="resendBtn" 
                        style="background: none; border: none; color: var(--primary); font-weight: 800; cursor: pointer; font-size: 0.9rem; text-decoration: underline;">
                    Resend Code
                </button>
                <span id="cooldown" style="font-size: 0.85rem; color: #94a3b8; display: none;">Wait <span id="timer">60</span>s</span>
            </form>
        </div>

        <script>
            const resendBtn = document.getElementById('resendBtn');
            const cooldown = document.getElementById('cooldown');
            const timer = document.getElementById('timer');
            
            // Handle Cooldown
            if (localStorage.getItem('otpCooldown') > Date.now()) {
                startTimer();
            }

            document.getElementById('resendForm').onsubmit = function() {
                if (localStorage.getItem('otpCooldown') > Date.now()) return false;
                localStorage.setItem('otpCooldown', Date.now() + 60000);
                return true;
            };

            function startTimer() {
                resendBtn.style.display = 'none';
                cooldown.style.display = 'inline';
                
                const interval = setInterval(() => {
                    const timeLeft = Math.ceil((localStorage.getItem('otpCooldown') - Date.now()) / 1000);
                    if (timeLeft <= 0) {
                        clearInterval(interval);
                        resendBtn.style.display = 'inline';
                        cooldown.style.display = 'none';
                    } else {
                        timer.innerText = timeLeft;
                    }
                }, 1000);
            }

            if (localStorage.getItem('otpCooldown') > Date.now()) startTimer();
        </script>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

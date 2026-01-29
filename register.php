<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $password]);
            
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['name'] = $name;
            $_SESSION['role'] = 'user';
            header("Location: index.php");
            exit();
        }
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

        <div class="text-center" style="margin-top: 2rem; font-size: 0.9rem; color: #64748b;">
            Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 800; text-decoration: none;">Sign in</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

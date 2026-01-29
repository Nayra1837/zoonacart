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

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid email or password.";
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
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">Email Address</label>
                <input type="email" name="email" placeholder="name@example.com" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none; transition: 0.3s;" onfocus="this.style.borderColor='var(--primary)'">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase;">Password</label>
                <input type="password" name="password" placeholder="••••••••" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none; transition: 0.3s;" onfocus="this.style.borderColor='var(--primary)'">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; margin-top: 0.5rem;">Sign In</button>
        </form>

        <div class="text-center" style="margin-top: 2rem; font-size: 0.9rem; color: #64748b;">
            New to GlamCart? <a href="register.php" style="color: var(--primary); font-weight: 800; text-decoration: none;">Create account</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

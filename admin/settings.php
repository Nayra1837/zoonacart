<?php
require_once '../includes/functions.php';
if (!isAdmin()) redirect('login.php');

// Auto-create settings table if it doesn't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(255) PRIMARY KEY,
    setting_value TEXT
)");

// Ensure default settings exist
$defaults = [
    'site_name' => 'ZoonaCart',
    'site_description' => 'Premium Boutique Experience',
    'hero_title' => 'Redefining Elegance',
    'hero_subtitle' => 'Discover professional-grade cosmetics crafted for your radiant beauty.',
    'hero_image' => 'hero.png',
    'site_favicon' => 'favicon.png',
    'footer_text' => 'Elevating beauty since 2024. Your premier destination for professional cosmetics.'
];

foreach ($defaults as $key => $val) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    $stmt->execute([$key, $val]);
}

$success = '';
$error = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if ($key !== 'submit') {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
    }

    // Handle Hero Image Upload
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === 0) {
        $imgName = 'hero_' . time() . '_' . $_FILES['hero_image']['name'];
        if (move_uploaded_file($_FILES['hero_image']['tmp_name'], '../assets/img/' . $imgName)) {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'hero_image'");
            $stmt->execute([$imgName]);
        }
    }

    // Handle Favicon Upload
    if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] === 0) {
        $imgName = 'favicon_' . time() . '_' . $_FILES['site_favicon']['name'];
        if (move_uploaded_file($_FILES['site_favicon']['tmp_name'], '../assets/img/' . $imgName)) {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_favicon'");
            $stmt->execute([$imgName]);
        }
    }

    // Handle Admin Password Update
    if (!empty($_POST['admin_password'])) {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$_POST['admin_password'], $_SESSION['user_id']]);
    }

    $success = "Settings updated successfully!";
}

$settings = [];
$rows = $pdo->query("SELECT * FROM settings")->fetchAll();
foreach ($rows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

include '../includes/header.php';
?>

<div class="container" style="padding: 2.5rem 5%;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Site Configuration</h1>
        <p style="color: #64748b;">Manage your boutique's identity and hero content.</p>
    </div>

    <?php include 'admin_nav.php'; ?>

    <?php if($success): ?>
        <div style="background: #ecfdf5; color: #065f46; padding: 1rem 2rem; border-radius: 0; margin-bottom: 2rem; font-weight: 600; display: flex; align-items: center; gap: 1rem; border: 1px solid #a7f3d0;">
            <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="glass animate" style="padding: 3rem; border-radius: 0;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 3rem;">
            <!-- Site Identity -->
            <div>
                <h3 style="margin-bottom: 2rem;">Site Identity</h3>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Store Name</label>
                    <input type="text" name="site_name" value="<?php echo $settings['site_name'] ?? ''; ?>" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Store Description</label>
                    <input type="text" name="site_description" value="<?php echo $settings['site_description'] ?? ''; ?>" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Site Favicon</label>
                    <div style="display: flex; align-items: center; gap: 2rem;">
                        <?php if(isset($settings['site_favicon'])): ?>
                            <img src="../assets/img/<?php echo $settings['site_favicon']; ?>" style="width: 32px; height: 32px; object-fit: contain; border: 1px solid #e2e8f0; background: #fff;">
                        <?php endif; ?>
                        <input type="file" name="site_favicon" style="font-size: 0.8rem;">
                    </div>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Footer Text</label>
                    <textarea name="footer_text" rows="3" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none; resize: none;"><?php echo $settings['footer_text'] ?? ''; ?></textarea>
                </div>
            </div>

            <!-- Hero Banner -->
            <div>
                <h3 style="margin-bottom: 2rem;">Hero Banner</h3>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Hero Title</label>
                    <input type="text" name="hero_title" value="<?php echo $settings['hero_title'] ?? ''; ?>" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Hero Subtitle</label>
                    <input type="text" name="hero_subtitle" value="<?php echo $settings['hero_subtitle'] ?? ''; ?>" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Hero Image</label>
                    <div style="display: flex; align-items: center; gap: 2rem;">
                        <?php if(isset($settings['hero_image'])): ?>
                            <img src="../assets/img/<?php echo $settings['hero_image']; ?>" style="width: 100px; height: 60px; object-fit: cover; border: 1px solid #e2e8f0;">
                        <?php endif; ?>
                        <input type="file" name="hero_image" style="font-size: 0.8rem;">
                    </div>
                </div>
            </div>

            <!-- Admin Security -->
            <div>
                <h3 style="margin-bottom: 2rem;">Admin Security</h3>
                <div style="margin-bottom: 2rem;">
                    <p style="color: #64748b; font-size: 0.85rem; line-height: 1.6;">Update your account password to keep your administrative access secure.</p>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">New Password</label>
                    <input type="password" name="admin_password" placeholder="Leave blank to keep current" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                </div>
            </div>

            <!-- Email Configuration (SMTP) -->
            <div>
                <h3 style="margin-bottom: 2rem;">Email Settings (SMTP)</h3>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">SMTP Host</label>
                    <input type="text" name="smtp_host" value="<?php echo $settings['smtp_host'] ?? 'smtp.gmail.com'; ?>" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Sender Email</label>
                    <input type="text" name="smtp_email" value="<?php echo $settings['smtp_email'] ?? ''; ?>" placeholder="your-email@gmail.com" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">App Password</label>
                    <input type="password" name="smtp_password" value="<?php echo $settings['smtp_password'] ?? ''; ?>" placeholder="Gmail App Password" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                </div>
            </div>
            
            <!-- Google Login -->
            <div>
                <h3 style="margin-bottom: 2rem;">Social Login</h3>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Google Client ID</label>
                    <input type="text" name="google_client_id" value="<?php echo $settings['google_client_id'] ?? ''; ?>" placeholder="xxx.apps.googleusercontent.com" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                </div>
            </div>

            <!-- Shiprocket Integration -->
            <div>
                <h3 style="margin-bottom: 2rem;">Shiprocket Integration</h3>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">User Email</label>
                    <input type="email" name="shiprocket_email" value="<?php echo $settings['shiprocket_email'] ?? ''; ?>" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Password</label>
                    <input type="password" name="shiprocket_password" value="<?php echo $settings['shiprocket_password'] ?? ''; ?>" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                </div>
            </div>
        </div>

        <div style="margin-top: 3rem; border-top: 1px solid #f1f5f9; padding-top: 2rem;">
            <button type="submit" name="submit" class="btn btn-primary" style="padding: 1rem 4rem;">Update All Settings</button>
        </div>
    </form>
</div>

<?php include '../includes/header.php'; ?>

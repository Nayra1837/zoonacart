<?php
require_once '../includes/functions.php';
if (!isAdmin()) redirect('login.php');

// Handle Role Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$_POST['role'], $_POST['user_id']]);
    $success = "User role updated successfully!";
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
include '../includes/header.php';
?>

<div class="container" style="padding: 2.5rem 5%;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">User Management</h1>
        <p style="color: #64748b;">Manage customer profiles and administrative access.</p>
    </div>

    <?php include 'admin_nav.php'; ?>

    <?php if(isset($success)): ?>
        <div style="background: #ecfdf5; color: #065f46; padding: 1rem 2rem; border-radius: 0; margin-bottom: 2rem; font-weight: 600; display: flex; align-items: center; gap: 1rem; border: 1px solid #a7f3d0;">
            <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div class="glass" style="border-radius: 0; overflow: hidden; padding: 2rem;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="text-align: left; border-bottom: 1px solid #eee;">
                    <tr>
                        <th style="padding: 1.5rem; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Customer</th>
                        <th style="padding: 1.5rem; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Email Address</th>
                        <th style="padding: 1.5rem; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; text-align: center;">Role</th>
                        <th style="padding: 1.5rem; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; text-align: center;">Joined Date</th>
                        <th style="padding: 1.5rem; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Total Orders</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                        <tr style="border-bottom: 1px solid #f8fafc; transition: 0.3s;" onmouseenter="this.style.background='#fdfaff'" onmouseleave="this.style.background='transparent'">
                            <td style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 45px; height: 45px; background: #f1f5f9; color: #64748b; border-radius: 0; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; border: 2px solid white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                                    <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
                                </div>
                                <span style="font-weight: 700; color: #1e293b;"><?php echo $u['name']; ?></span>
                            </td>
                            <td style="padding: 1.5rem; color: #64748b; font-size: 0.9rem;"><?php echo $u['email']; ?></td>
                            <td style="padding: 1.5rem; text-align: center;">
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <select name="role" onchange="this.form.submit()" style="padding: 4px 12px; border-radius: 0; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; cursor: pointer; border: 1px solid #e2e8f0; <?php 
                                        echo $u['role'] === 'admin' ? 'background: #fff1f2; color: var(--primary); border-color: #fecdd3;' : 'background: #f1f5f9; color: #64748b;';
                                    ?>">
                                        <option value="user" <?php echo $u['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td style="padding: 1.5rem; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                                <?php echo date('M j, Y', strtotime($u['created_at'])); ?>
                            </td>
                            <td style="padding: 1.5rem; text-align: right; font-weight: 800; color: #1e293b;">
                                <?php 
                                    $count = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
                                    $count->execute([$u['id']]);
                                    echo $count->fetchColumn();
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

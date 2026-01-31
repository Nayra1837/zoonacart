<?php
session_start();

// THE MASTER PASSWORD (You can change this)
$MASTER_PASSWORD = 'zoona2026'; 

$error = '';

if (isset($_POST['unlock'])) {
    $entered_pass = $_POST['password'] ?? '';
    if ($entered_pass === $MASTER_PASSWORD) {
        $_SESSION['app_unlocked'] = true;
        header("Location: index.php");
        exit();
    } else {
        $error = "Incorrect Security PIN. Access Denied.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Locked | ZoonaCart Security</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .lock-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; width: 100%; max-width: 400px; border: 1px solid #e2e8f0; }
        .icon { font-size: 50px; margin-bottom: 20px; }
        h1 { font-size: 24px; color: #0f172a; margin-bottom: 10px; font-weight: 800; }
        p { color: #64748b; margin-bottom: 30px; font-size: 14px; }
        input { width: 100%; padding: 15px; border-radius: 12px; border: 1px solid #cbd5e1; margin-bottom: 20px; box-sizing: border-box; text-align: center; font-size: 18px; letter-spacing: 5px; }
        button { width: 100%; padding: 15px; border-radius: 12px; border: none; background: #e11d48; color: white; font-weight: 800; cursor: pointer; transition: 0.3s; }
        button:hover { background: #be123c; transform: translateY(-2px); }
        .error { color: #e11d48; font-size: 13px; margin-bottom: 15px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="lock-card">
        <div class="icon">🔒</div>
        <h1>System Locked</h1>
        <p>This project is protected by a Security PIN. Please enter the master password to run the application.</p>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="password" name="password" placeholder="••••••••" required autofocus autocomplete="off">
            <button type="submit" name="unlock">Unlock Application</button>
        </form>
    </div>
</body>
</html>

<?php
ob_start();
header('Content-Type: application/json');
require_once '../config.php';
require_once '../includes/functions.php';

$action = $_GET['action'] ?? '';

try {
    switch($action) {
    case 'get_products':
    if (isset($_GET['sort']) && $_GET['sort'] === 'random') {
        $stmt = $pdo->query("
            SELECT p.*, GROUP_CONCAT(pi.image_path) as images 
            FROM products p 
            LEFT JOIN product_images pi ON p.id = pi.product_id 
            GROUP BY p.id 
            ORDER BY RAND() LIMIT 12
        ");
    } else {
        $stmt = $pdo->query("
            SELECT p.*, GROUP_CONCAT(pi.image_path) as images 
            FROM products p 
            LEFT JOIN product_images pi ON p.id = pi.product_id 
            GROUP BY p.id 
            ORDER BY p.created_at DESC
        ");
    }
        $products = $stmt->fetchAll();
        
        // Convert comma-separated string to array
        foreach ($products as &$product) {
            if ($product['images']) {
                $product['images'] = array_unique(explode(',', $product['images']));
            } else {
                $product['images'] = [];
            }
            // Ensure main image is in the list
            if ($product['image'] && !in_array($product['image'], $product['images'])) {
                array_unshift($product['images'], $product['image']);
            }
        }
        ob_end_clean(); // Clean buffer before output
        echo json_encode($products);
        break;

    case 'get_cart':
        $items = [];
        $subtotal = 0;
        if (!empty($_SESSION['cart'])) {
            $ids = array_keys($_SESSION['cart']);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $products = $stmt->fetchAll();
            foreach ($products as $p) {
                $qty = $_SESSION['cart'][$p['id']];
                $p_subtotal = $p['price'] * $qty;
                $subtotal += $p_subtotal;
                $items[] = array_merge($p, ['qty' => $qty, 'subtotal' => $p_subtotal]);
            }
        }
        $gst_rate = (float)getSetting('gst_rate');
        $gst_amount = $subtotal * ($gst_rate / 100);
        $total = $subtotal + $gst_amount;
        echo json_encode([
            'items' => $items, 
            'subtotal' => $subtotal,
            'gst_rate' => $gst_rate,
            'gst' => $gst_amount,
            'total' => $total, 
            'count' => array_sum($_SESSION['cart'] ?? [])
        ]);
        break;

    case 'add_to_cart':
        $id = $_POST['id'];
        $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
        if ($qty < 1) $qty = 1;
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $qty;
        echo json_encode(['success' => true, 'count' => array_sum($_SESSION['cart'])]);
        break;

    case 'update_cart':
        $id = $_POST['id'];
        $qty = (int)$_POST['qty'];
        if ($qty <= 0) unset($_SESSION['cart'][$id]);
        else $_SESSION['cart'][$id] = $qty;
        echo json_encode(['success' => true]);
        break;

    case 'get_auth':
        echo json_encode([
            'isLoggedIn' => isset($_SESSION['user_id']),
            'name' => $_SESSION['name'] ?? '',
            'role' => $_SESSION['role'] ?? ''
        ]);
        break;

    case 'get_orders':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([]);
            break;
        }
        $stmt = $pdo->prepare("
            SELECT o.*, 
            (SELECT p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) as order_image 
            FROM orders o 
            WHERE o.user_id = ? 
            ORDER BY o.order_date DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        echo json_encode($stmt->fetchAll());
        break;

    case 'update_password':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authorized']);
            break;
        }
        $password = $_POST['password'];
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$password, $_SESSION['user_id']]);
        echo json_encode(['success' => true]);
        break;

    case 'return_item':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authorized']);
            break;
        }
        $orderId = $_POST['order_id'];
        $productId = $_POST['product_id'];
        $reason = $_POST['reason'];
        $feedback = $_POST['feedback'];
        
        // Check if order is completed
        $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $orderStatus = $stmt->fetchColumn();

        if ($orderStatus !== 'completed') {
            echo json_encode(['success' => false, 'error' => 'Only completed orders can be returned.']);
            break;
        }

        // Basic duplicate check
        $stmt = $pdo->prepare("SELECT id FROM returns WHERE order_id = ? AND product_id = ?");
        $stmt->execute([$orderId, $productId]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Return already requested for this item.']);
            break;
        }

        $stmt = $pdo->prepare("INSERT INTO returns (user_id, order_id, product_id, reason, feedback) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $orderId, $productId, $reason, $feedback]);
        echo json_encode(['success' => true]);
        break;

    case 'request_withdrawal':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authorized']);
            break;
        }
        $amount = (float)$_POST['amount'];
        $method = $_POST['method'];
        $details = $_POST['details'];
        
        $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $balance = $stmt->fetchColumn();
        
        if ($balance < $amount) {
            echo json_encode(['success' => false, 'error' => 'Insufficient balance']);
            break;
        }
        
        // Deduct Balance
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
        $stmt->execute([$amount, $_SESSION['user_id']]);
        
        // Record Transaction
        $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'withdrawal', ?)");
        $stmt->execute([$_SESSION['user_id'], -$amount, "Withdrawal Request ($method)"]);
        
        // Create Withdrawal Request
        $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, amount, method, details) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $amount, $method, $details]);
        
        echo json_encode(['success' => true]);
        break;

    case 'update_withdrawal_status':
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            break;
        }
        $id = $_POST['id'];
        $status = $_POST['status'];
        
        if ($status === 'rejected') {
            // Refund the amount to wallet
            $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id = ?");
            $stmt->execute([$id]);
            $w = $stmt->fetch();
            
            if ($w && $w['status'] === 'pending') {
                $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
                $stmt->execute([$w['amount'], $w['user_id']]);
                
                $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'withdrawal_refund', ?)");
                $stmt->execute([$w['user_id'], $w['amount'], "Refund: Withdrawal Rejected"]);
            }
        }
        
        $stmt = $pdo->prepare("UPDATE withdrawals SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['success' => true]);
        break;

    case 'update_return_status':
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            break;
        }
        $returnId = $_POST['return_id'];
        $status = $_POST['status'];
        
        // Fetch return details to get user and product info for refund
        $stmt = $pdo->prepare("SELECT r.*, oi.price, oi.quantity FROM returns r 
                              JOIN order_items oi ON r.order_id = oi.order_id AND r.product_id = oi.product_id 
                              WHERE r.id = ?");
        $stmt->execute([$returnId]);
        $return = $stmt->fetch();

        if ($return && $status === 'approved' && $return['status'] !== 'approved') {
            // Process Refund to Wallet
            $refundAmount = $return['price'] * $return['quantity']; // Assuming full qty return for now or 1 unit. 
            // Better logic: fetch price of single item. Returns table doesn't track qty, assuming 1 for simplicity of this system or matching order item.
            // Let's assume refunding the price of ONE unit for the returned product logic.
            // But wait, order_items tracks total qty. If I bought 2, and return 1... 
            // The current return system identifies order_id and product_id. It doesn't specify qty.
            // We will assume 1 unit refund for now to be safe, or we need to update return request to include qty.
            // For this specific 'simple' request, let's refund the Unit Price.
            
            $stmt = $pdo->prepare("SELECT price FROM order_items WHERE order_id = ? AND product_id = ?");
            $stmt->execute([$return['order_id'], $return['product_id']]);
            $unitPrice = $stmt->fetchColumn(); 

            // Credit Wallet
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
            $stmt->execute([$unitPrice, $return['user_id']]);

            // Log Transaction
            $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'refund', ?)");
            $stmt->execute([$return['user_id'], $unitPrice, "Refund for Order #" . $return['order_id']]);
        }
        
        $stmt = $pdo->prepare("UPDATE returns SET status = ? WHERE id = ?");
        $stmt->execute([$status, $returnId]);
        echo json_encode(['success' => true]);
        break;

    case 'track_order':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authorized']);
            break;
        }
        $orderId = $_GET['order_id'];
        
        $stmt = $pdo->prepare("SELECT tracking_number FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $_SESSION['user_id']]);
        $trackingNumber = $stmt->fetchColumn();
        
        if (!$trackingNumber) {
            echo json_encode(['success' => false, 'error' => 'Tracking not available yet']);
            break;
        }

        require_once '../includes/Shiprocket.php';
        $shiprocket = new Shiprocket();
        $data = $shiprocket->trackOrder($trackingNumber);
        
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'google_login':
        $token = $_POST['credential'];
        
        // Validate Token with Google
        $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $token;
        $json = file_get_contents($url);
        $data = json_decode($json, true);
        
        if (isset($data['email']) && $data['email_verified']) {
            $email = $data['email'];
            $name = $data['name'];
            $googleId = $data['sub']; // Google User ID
            
            // Check if user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Determine if we need to update google_id
                if (!$user['google_id']) {
                    $stmt = $pdo->prepare("UPDATE users SET google_id = ?, is_verified = 1 WHERE id = ?");
                    $stmt->execute([$googleId, $user['id']]);
                }
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
            } else {
                // Create New User
                $password = bin2hex(random_bytes(8)); // Random password
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, google_id, is_verified) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([$name, $email, $password, $googleId]);
                
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['name'] = $name;
                $_SESSION['role'] = 'user';
            }
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid Google Token']);
        }
        break;

    case 'send_magic_link':
        $email = $_POST['email'];
        $name = $_POST['name'] ?? null; // Optional name for signup
        
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // If user doesn't exist...
        if (!$user) {
            // If it's a Signup request (Name provided)
            if ($name) {
                $password = bin2hex(random_bytes(8)); // Random password
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, is_verified) VALUES (?, ?, ?, 0)");
                $stmt->execute([$name, $email, $password]);
                $userId = $pdo->lastInsertId();
                $user = ['id' => $userId, 'name' => $name];
            } else {
                // Login request but user not found
                echo json_encode(['success' => false, 'error' => 'Account not found. Please sign up first.']);
                break;
            }
        }
        
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $stmt = $pdo->prepare("UPDATE users SET magic_token = ?, magic_token_expiry = ? WHERE id = ?");
        $stmt->execute([$token, $expiry, $user['id']]);
        
        // Send Email
        require_once '../includes/Mailer.php';
        $mailer = new Mailer();
        $link = BASE_URL . "login.php?magic_token=" . $token;
        
        $subject = "Login to " . getSetting('site_name');
        $body = "<h2>Magic Login Link</h2>
                 <p>Hi " . htmlspecialchars($user['name']) . ",</p>
                 <p>Click the link below to sign in instantly. The link expires in 15 minutes.</p>
                 <p><a href='$link' style='background: #e11d48; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Click to Login</a></p>
                 <p>Or copy this URL: $link</p>";
        
        if($mailer->send($email, $subject, $body)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to send email.']);
        }
        break;

    case 'request_password_reset':
        $email = $_POST['email'];
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $otp = rand(100000, 999999);
            $stmt = $pdo->prepare("UPDATE users SET verification_code = ? WHERE id = ?");
            $stmt->execute([$otp, $user['id']]);

            require_once '../includes/Mailer.php';
            $mailer = new Mailer();
            $subject = "Password Reset Code - " . getSetting('site_name');
            $body = "<h2>Password Recovery</h2>
                     <p>Hi " . htmlspecialchars($user['name']) . ",</p>
                     <p>Your password reset code is: <b style='font-size: 24px;'>$otp</b></p>
                     <p>Use this code to set a new password for your account.</p>";
            
            if ($mailer->send($email, $subject, $body)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to send reset code.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Email not found.']);
        }
        break;

    case 'reset_password':
        $email = $_POST['email'];
        $otp = $_POST['otp'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND verification_code = ?");
        $stmt->execute([$email, $otp]);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = $pdo->prepare("UPDATE users SET password = ?, verification_code = NULL WHERE id = ?");
            $stmt->execute([$password, $user['id']]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid recovery code.']);
        }
        break;

    case 'update_profile':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authorized']);
            break;
        }
        
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        
        // Basic Validation
        if (empty($name) || empty($email) || empty($phone)) {
            echo json_encode(['success' => false, 'error' => 'All fields are required']);
            break;
        }
        
        // Check uniqueness for Email/Phone (excluding current user)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR phone = ?) AND id != ?");
        $stmt->execute([$email, $phone, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Email or Phone already in use by another account']);
            break;
        }
        
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $_SESSION['user_id']]);
        
        // Update Session Name
        $_SESSION['name'] = $name;
        
        echo json_encode(['success' => true]);
        break;

    case 'get_wallet_history':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([]);
            break;
        }
        $stmt = $pdo->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        echo json_encode($stmt->fetchAll());
        break;

    case 'upload_profile_pic':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authorized']);
            break;
        }

        if (!isset($_FILES['profile_pic'])) {
            echo json_encode(['success' => false, 'error' => 'No file uploaded']);
            break;
        }

        $file = $_FILES['profile_pic'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array(strtolower($ext), $allowed)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type']);
            break;
        }

        $filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
        $target = '../assets/img/profiles/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            // Update database
            $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmt->execute([$filename, $_SESSION['user_id']]);
            echo json_encode(['success' => true, 'filename' => $filename]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save file']);
        }
        break;
}

} catch (Exception $e) {
    // Log server-side and return a clean JSON error (avoids HTML error pages breaking JSON.parse)
    error_log($e->getMessage());
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

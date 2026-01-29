<?php
header('Content-Type: application/json');
require_once '../config.php';

$action = $_GET['action'] ?? '';

try {
    switch($action) {
    case 'get_products':
        $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll());
        break;

    case 'get_cart':
        $items = [];
        $total = 0;
        if (!empty($_SESSION['cart'])) {
            $ids = array_keys($_SESSION['cart']);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $products = $stmt->fetchAll();
            foreach ($products as $p) {
                $qty = $_SESSION['cart'][$p['id']];
                $subtotal = $p['price'] * $qty;
                $total += $subtotal;
                $items[] = array_merge($p, ['qty' => $qty, 'subtotal' => $subtotal]);
            }
        }
        echo json_encode(['items' => $items, 'total' => $total, 'count' => array_sum($_SESSION['cart'] ?? [])]);
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
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
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
}
} catch (Exception $e) {
    // Log server-side and return a clean JSON error (avoids HTML error pages breaking JSON.parse)
    error_log("API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}
?>

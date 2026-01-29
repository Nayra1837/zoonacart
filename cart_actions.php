<?php
require_once 'includes/functions.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add') {
    $productId = $_POST['product_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    addToCart($productId, $quantity);
    
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?: BASE_URL . 'shop.php'));
    exit();
}

if ($action === 'remove') {
    $productId = $_GET['id'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    redirect('cart.php');
}

if ($action === 'update') {
    $productId = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
    redirect('cart.php');
}

redirect('index.php');
?>

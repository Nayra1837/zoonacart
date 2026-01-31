<?php
require_once 'includes/functions.php';

// Bypass admin check for this specific critical fix if necessary, 
// but let's keep it safe for now. 
// If you are not logged in as admin, please login first.
if (!isAdmin()) {
    die("Access Denied: Please login as Admin to run this fix.");
}

echo "<h2>ZoonaCart Database System Repair</h2>";
echo "<p>Starting deep-sync of database schema...</p>";

function columnExists($pdo, $table, $column) {
    try {
        $check = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $check->rowCount() > 0;
    } catch (Exception $e) {
        // Log or handle error, assume column doesn't exist if check fails
        return false;
    }
}

function addColumnIfMissing($pdo, $table, $column, $definition) {
    try {
        // Check if column exists
        if (!columnExists($pdo, $table, $column)) {
            echo "Adding column <b>$column</b> to <b>$table</b>... ";
            $pdo->exec("ALTER TABLE `$table` ADD `$column` $definition");
            echo "<span style='color:green;'>DONE</span><br>";
        } else {
            echo "Column <b>$column</b> already exists in <b>$table</b>. <span style='color:blue;'>SKIPPED</span><br>";
        }
    } catch (Exception $e) {
        echo "<span style='color:red;'>FAILED: " . $e->getMessage() . "</span><br>";
    }
}

try {
    // 1. Synchronizing Orders Table
    echo "<h4>1. Synchronizing Orders Table:</h4>";
    addColumnIfMissing($pdo, 'orders', 'total_amount', "DECIMAL(10,2) NOT NULL DEFAULT 0.00");
    addColumnIfMissing($pdo, 'orders', 'subtotal_amount', "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER total_amount");
    addColumnIfMissing($pdo, 'orders', 'gst_amount', "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER subtotal_amount");
    addColumnIfMissing($pdo, 'orders', 'tracking_id', "VARCHAR(255) DEFAULT NULL");
    addColumnIfMissing($pdo, 'orders', 'shipment_id', "VARCHAR(255) DEFAULT NULL");

    // 2. Synchronizing Users Table
    echo "<h4>2. Synchronizing Users Table:</h4>";
    $userColumns = [
        'wallet_balance' => "DECIMAL(10, 2) DEFAULT 0.00",
        'profile_pic' => "VARCHAR(255) DEFAULT NULL",
        'phone' => "VARCHAR(20) DEFAULT NULL",
        'is_verified' => "TINYINT(1) DEFAULT 0",
        'verification_code' => "VARCHAR(255) DEFAULT NULL",
        'google_id' => "VARCHAR(255) DEFAULT NULL",
        'magic_token' => "VARCHAR(255) DEFAULT NULL",
        'magic_token_expiry' => "DATETIME DEFAULT NULL"
    ];
    foreach ($userColumns as $col => $def) {
        if (!columnExists($pdo, 'users', $col)) {
            echo "Adding column <b>$col</b> to <b>users</b>... ";
            $pdo->exec("ALTER TABLE users ADD COLUMN $col $def");
            echo "<span style='color:green;'>DONE</span><br>";
        } else {
            echo "Column <b>$col</b> already exists in <b>users</b>. <span style='color:blue;'>SKIPPED</span><br>";
        }
    }

    // 3. Synchronizing Products Table
    echo "<h4>3. Synchronizing Products Table:</h4>";
    $productColumns = [
        'hsn_code' => "VARCHAR(20) DEFAULT '3304'",
        'tax_percent' => "DECIMAL(5, 2) DEFAULT 18.00"
    ];
    foreach ($productColumns as $col => $def) {
        if (!columnExists($pdo, 'products', $col)) {
            echo "Adding column <b>$col</b> to <b>products</b>... ";
            $pdo->exec("ALTER TABLE products ADD COLUMN $col $def");
            echo "<span style='color:green;'>DONE</span><br>";
        } else {
            echo "Column <b>$col</b> already exists in <b>products</b>. <span style='color:blue;'>SKIPPED</span><br>";
        }
    }

    // 4. Checking Critical Tables
    echo "<h4>4. Checking Critical Tables:</h4>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS returns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        reason VARCHAR(255),
        feedback TEXT,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(user_id),
        INDEX(order_id)
    )");
    echo "Table <b>returns</b> exists.<br>";

    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
    // Double check order_items for dynamic tax/hsn
    $orderItemColumns = [
        'hsn_code' => "VARCHAR(20) DEFAULT '3304'",
        'tax_percent' => "DECIMAL(5, 2) DEFAULT 18.00"
    ];
    foreach ($orderItemColumns as $col => $def) {
        if (!columnExists($pdo, 'order_items', $col)) {
            echo "Adding column <b>$col</b> to <b>order_items</b>... ";
            $pdo->exec("ALTER TABLE order_items ADD COLUMN $col $def");
            echo "<span style='color:green;'>DONE</span><br>";
        } else {
            echo "Column <b>$col</b> already exists in <b>order_items</b>. <span style='color:blue;'>SKIPPED</span><br>";
        }
    }
    echo "Table <b>order_items</b> synced.<br>";

    $pdo->exec("CREATE TABLE IF NOT EXISTS wallet_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        type ENUM('deposit', 'withdrawal', 'purchase', 'refund', 'withdrawal_refund') NOT NULL,
        description TEXT,
        status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(user_id)
    )");
    echo "Table <b>wallet_transactions</b> exists.<br>";

    $pdo->exec("CREATE TABLE IF NOT EXISTS withdrawals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        method VARCHAR(50) DEFAULT 'UPI',
        details TEXT,
        upi_id VARCHAR(255) DEFAULT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(user_id)
    )");
    echo "Table <b>withdrawals</b> exists.<br>";

    echo "<h3 style='color:green; margin-top:30px;'>✅ ALL SYSTEMS SYNCED!</h3>";
    echo "<p>Your checkout system is now 100% compatible with the new Invoice & Wallet features.</p>";
    echo "<a href='checkout.php' style='display:inline-block; padding:12px 25px; background:#f43f5e; color:white; text-decoration:none; font-weight:bold; border-radius:5px;'>Try Checkout Now</a>";
    echo " &nbsp; <a href='index.php' style='color:#64748b;'>Return Home</a>";

} catch (Exception $e) {
    echo "<h3 style='color:red;'>❌ CRITICAL ERROR!</h3>";
    echo "<p>Process halted: " . $e->getMessage() . "</p>";
}
?>

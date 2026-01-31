<?php
// Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

// Credentials
$host = 'sql305.iceiy.com';
$user = 'icei_41023241';
$pass = 'VEBeZ8c5UPzA';
$dbname = 'icei_41023241_zoonacart_db';

echo "<p><strong>Attempting to connect with:</strong><br>";
echo "Host: $host<br>";
echo "User: $user<br>";
echo "Content: [HIDDEN]<br>";
echo "DB Name: $dbname</p>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2 style='color:green'>✅ Connection Successful!</h2>";
    
    // Test Query
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tables Found: " . count($tables) . "</h3>";
    echo "<ul>";
    foreach($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";

} catch (PDOException $e) {
    echo "<h2 style='color:red'>❌ Connection Failed</h2>";
    echo "<p>Error Message: " . $e->getMessage() . "</p>";
    
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Check if Hostname is correct (found in MySQL Databases section).</li>";
    echo "<li>Check if Database Name is exactly correct (including prefix).</li>";
    echo "<li>Check if Password is correct.</li>";
    echo "</ul>";
}
?>

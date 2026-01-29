<?php
// init_db.php
// Usage: php init_db.php
// This script reads database.sql and executes it via mysqli->multi_query

$host = 'localhost';
$user = 'root';
$pass = '';
$sqlFile = __DIR__ . DIRECTORY_SEPARATOR . 'database.sql';

if (!file_exists($sqlFile)) {
    echo "database.sql not found at: $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "Failed to read database.sql\n";
    exit(1);
}

$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_error) {
    echo "MySQL connection failed: " . $mysqli->connect_error . "\n";
    exit(1);
}

// Enable multi statements and run the SQL
if ($mysqli->multi_query($sql)) {
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());

    if ($mysqli->errno) {
        echo "Completed with warnings/errors: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
    } else {
        echo "Database schema imported successfully.\n";
    }
} else {
    echo "Failed to run SQL: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
}

$mysqli->close();

?>
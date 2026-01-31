<?php
$files = ['config.php', 'includes/functions.php', 'api/main.php', 'index.php'];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $bom = substr($content, 0, 3);
    
    if ($bom === "\xEF\xBB\xBF") {
        echo "BOM FOUND in $file! Attempting to remove...\n";
        file_put_contents($file, substr($content, 3));
        echo "BOM removed from $file.\n";
    } else {
        echo "No BOM in $file.\n";
    }
}
?>

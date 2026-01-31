<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/Mailer.php';

echo "<h2>SMTP Diagnostic Tester</h2>";

$host = getSetting('smtp_host');
$email = getSetting('smtp_email');
$pass = getSetting('smtp_password');

echo "<p><strong>Host:</strong> $host</p>";
echo "<p><strong>Email:</strong> $email</p>";
echo "<p><strong>Password:</strong> " . ($pass ? "********" : "Not Set!") . "</p>";

if (!$email || !$pass) {
    die("<h3 style='color: red'>Error: Email or App Password is missing in Admin Settings.</h3>");
}

echo "<hr><p>Attempting to connect...</p>";

try {
    $mailer = new Mailer();
    $subject = "Test Email from Zoonacart Diagnostic";
    $body = "<h1>It Works!</h1><p>Your SMTP settings are correct.</p>";
    
    // Attempt send
    if ($mailer->send($email, $subject, $body)) {
        echo "<h3 style='color: green'>Success! Email sent to $email. Check your inbox (and Spam folder).</h3>";
    } else {
        echo "<h3 style='color: red'>Failed to send email.</h3>";
        echo "<p style='color: red'><strong>Error Detail:</strong> " . $mailer->getLastError() . "</p>";
        echo "<p>Common Fixes:</p>";
        echo "<ul>
                <li>You are using an <b>App Password</b>, not your Google login password.</li>
                <li>2-Step Verification is ON in your Google Account.</li>
                <li>Your firewall allows outbound connections to port 465.</li>
              </ul>";
    }
} catch (Exception $e) {
    echo "<h3 style='color: red'>Exception Occurred:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>

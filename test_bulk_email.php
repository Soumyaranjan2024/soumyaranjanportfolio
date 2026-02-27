<?php
require_once 'database.php';
require_once 'admin_bulk_email.php';

echo "<h2>Testing Bulk Email System</h2>";

// Test 1: Check email configuration
echo "<h3>1. Email Configuration Test</h3>";
if (testEmailConfig()) {
    echo "✅ Email configuration is valid<br>";
} else {
    echo "❌ Email configuration is invalid<br>";
    exit;
}

// Test 2: Check database recipients
echo "<h3>2. Database Recipients Test</h3>";
try {
    $contacts = $pdo->query("SELECT COUNT(DISTINCT email) FROM messages WHERE email != ''")->fetchColumn();
    $users = $pdo->query("SELECT COUNT(*) FROM users WHERE email != ''")->fetchColumn();

    echo "Contact form emails: $contacts<br>";
    echo "User emails: $users<br>";
    echo "Total unique recipients: " . ($contacts + $users) . "<br>";

    if (($contacts + $users) > 0) {
        echo "✅ Recipients found in database<br>";
    } else {
        echo "❌ No recipients found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Send test bulk email to yourself
echo "<h3>3. Test Bulk Email</h3>";
$test_result = sendBulkEmail(
    'soumyaranjanpadhi936@gmail.com',
    'Test Admin',
    'Test Bulk Email from Admin Panel',
    'This is a test bulk email sent from your admin panel. If you receive this, the bulk email system is working correctly!',
    true
);

if ($test_result) {
    echo "✅ Test bulk email sent successfully!<br>";
} else {
    echo "❌ Test bulk email failed!<br>";
}

echo "<br><h3>Summary</h3>";
echo "If all tests show ✅, your bulk email system is ready to use!<br>";
echo "<a href='admin.php?section=bulk_email'>Go to Bulk Email Panel</a>";
?>
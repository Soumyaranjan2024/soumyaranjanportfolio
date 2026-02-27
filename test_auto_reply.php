<?php
require_once 'auto_reply.php';

echo "<h2>Testing Auto-Reply System</h2>";

// Test email configuration
echo "<h3>1. Testing Email Configuration...</h3>";
if (testEmailConfig()) {
    echo "✅ Email configuration is valid<br>";
} else {
    echo "❌ Email configuration is invalid<br>";
    exit;
}

// Test auto-reply
echo "<h3>2. Sending Test Auto-Reply...</h3>";
$test_email = "soumyaranjanpadhi936@gmail.com"; // Send to yourself for testing
$test_name = "Test User";
$test_subject = "Test Contact Form Submission";

echo "Sending auto-reply to: $test_email<br>";
echo "Debug output:<br><br>";

$result = sendAutoReply($test_email, $test_name, $test_subject, true); // Enable debug

if ($result) {
    echo "<br><br>✅ <strong>Auto-reply sent successfully!</strong>";
} else {
    echo "<br><br>❌ <strong>Auto-reply failed!</strong>";
}
?>
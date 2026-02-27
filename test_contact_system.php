<?php
require_once 'database.php';
require_once 'auto_reply.php';

echo "<h2>Testing Complete Contact System</h2>";

// Test 1: Database connection
echo "<h3>1. Testing Database Connection...</h3>";
try {
    $count = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
    echo "✅ Database connected successfully. Current messages: $count<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Email configuration
echo "<h3>2. Testing Email Configuration...</h3>";
if (testEmailConfig()) {
    echo "✅ Email configuration is valid<br>";
    $config = getEmailConfig();
    echo "SMTP Host: " . $config['host'] . "<br>";
    echo "Username: " . $config['username'] . "<br>";
    echo "From Email: " . $config['from_email'] . "<br>";
} else {
    echo "❌ Email configuration is invalid<br>";
    exit;
}

// Test 3: Insert test message and send auto-reply
echo "<h3>3. Testing Complete Contact Flow...</h3>";
$test_name = "Test User";
$test_email = "soumyaranjanpadhi936@gmail.com"; // Send to yourself
$test_subject = "Test Portfolio Contact";
$test_message = "This is a test message from the portfolio contact form.";

try {
    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
    $result = $stmt->execute([$test_name, $test_email, $test_subject, $test_message]);

    if ($result) {
        echo "✅ Test message saved to database<br>";

        // Send auto-reply
        echo "Sending auto-reply to: $test_email<br>";
        $auto_reply_result = sendAutoReply($test_email, $test_name, $test_subject, true);

        if ($auto_reply_result) {
            echo "<br>✅ <strong>Auto-reply sent successfully!</strong><br>";
            echo "✅ <strong>Complete system test PASSED!</strong><br>";
        } else {
            echo "<br>❌ <strong>Auto-reply failed!</strong><br>";
        }
    } else {
        echo "❌ Failed to save test message<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>Summary:</h3>";
echo "If all tests show ✅, your contact system is working correctly!<br>";
echo "Messages will be stored in portfolio_db.messages table and auto-replies will be sent.<br>";
?>
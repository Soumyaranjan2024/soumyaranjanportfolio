<?php
require_once 'database.php';
require_once 'auto_reply.php';

// Set response headers
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate input
    if (empty($name) || empty($email) || empty($message)) {
        $response['message'] = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Please enter a valid email address.";
    } else {
        try {
            // Insert message into database (portfolio_db)
            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([$name, $email, $subject, $message, $now]);

            if ($result) {
                // Send automatic reply
                $auto_reply_sent = sendAutoReply($email, $name, $subject);

                // Log the attempt
                error_log("Contact form submission: $name ($email) - Auto-reply: " . ($auto_reply_sent ? 'sent' : 'failed'));

                if ($auto_reply_sent) {
                    $response['success'] = true;
                    $response['message'] = "Thank you for your message, $name! I've sent a confirmation email to $email and will get back to you soon.";
                } else {
                    $response['success'] = true;
                    $response['message'] = "Thank you for your message, $name! I've received it and will get back to you soon.";
                }
            } else {
                $response['message'] = "Sorry, there was an error saving your message. Please try again.";
            }
        } catch (PDOException $e) {
            $response['message'] = "Database error. Please try again later.";
            error_log("Contact form database error: " . $e->getMessage());
        }
    }
} else {
    $response['message'] = "Invalid request method.";
}

// Return JSON response for AJAX
if (isset($_POST['ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    echo json_encode($response);
} else {
    // Regular form submission - redirect with message
    if ($response['success']) {
        header('Location: index.php?contact=success&msg=' . urlencode($response['message']) . '#contact');
    } else {
        header('Location: index.php?contact=error&msg=' . urlencode($response['message']) . '#contact');
    }
}
exit;
?>
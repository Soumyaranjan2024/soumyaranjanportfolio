<?php
require_once 'database.php';
require_once 'auto_reply.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
            // Insert message into database
            $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$name, $email, $subject, $message]);

            if ($result) {
                // Send automatic reply
                $auto_reply_sent = sendAutoReply($email, $name, $subject);

                if ($auto_reply_sent) {
                    $response['success'] = true;
                    $response['message'] = "Thank you for your message! We've sent a confirmation email to " . $email;
                } else {
                    $response['success'] = true;
                    $response['message'] = "Thank you for your message! We've received it and will get back to you soon.";
                    // Log the auto-reply failure
                    error_log("Auto-reply failed for: " . $email);
                }
            } else {
                $response['message'] = "Sorry, there was an error sending your message. Please try again.";
            }
        } catch (PDOException $e) {
            $response['message'] = "Database error. Please try again later.";
            error_log("Contact form error: " . $e->getMessage());
        }
    }
}

// Return JSON response for AJAX or redirect for regular form
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Regular form submission - redirect with message
    if ($response['success']) {
        header('Location: contact.php?success=' . urlencode($response['message']));
    } else {
        header('Location: contact.php?error=' . urlencode($response['message']));
    }
}
exit;
?>
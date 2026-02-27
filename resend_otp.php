<?php
require 'auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['otp_email']) || !isset($_SESSION['otp_user_data'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid session']);
    exit;
}

$user_data = $_SESSION['otp_user_data'];
$email = $_SESSION['otp_email'];

// Generate new OTP
$otp_code = generateOTP();

if (storeOTP($user_data['id'], $email, $otp_code)) {
    if (sendOTPEmail($email, $user_data['username'], $otp_code)) {
        echo json_encode(['success' => true, 'message' => 'OTP sent successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send email']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to generate OTP']);
}
?>
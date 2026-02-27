<?php
require_once 'config/session.php';
require_once 'config/email_config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

requireLogin();

$message = '';
$error = '';
$debug_info = '';

// Check if email is configured
if (!testEmailConfig()) {
    $error = 'Email configuration is incomplete. Please update config/email_config.php with your email provider settings.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && testEmailConfig()) {
    try {
        $mail = new PHPMailer(true);

        // Get email configuration
        $config = getEmailConfig();

        // Enable verbose debug output (remove in production)
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function ($str, $level) use (&$debug_info) {
            $debug_info .= "Debug level $level; message: $str\n";
        };

        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];

        // Additional settings for better compatibility
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom($config['from_email'], $config['from_name']);

        // Main recipients
        $to_emails = array_filter(array_map('trim', explode(',', $_POST['to'])));
        foreach ($to_emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($email);
            }
        }

        // CC recipients
        if (!empty($_POST['cc'])) {
            $cc_emails = array_filter(array_map('trim', explode(',', $_POST['cc'])));
            foreach ($cc_emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $mail->addCC($email);
                }
            }
        }

        // BCC recipients
        if (!empty($_POST['bcc'])) {
            $bcc_emails = array_filter(array_map('trim', explode(',', $_POST['bcc'])));
            foreach ($bcc_emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $mail->addBCC($email);
                }
            }
        }

        // Attachments
        if (isset($_FILES['attachments'])) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {
                if ($_FILES['attachments']['error'][$i] == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['attachments']['tmp_name'][$i];
                    $file_name = $_FILES['attachments']['name'][$i];

                    // Basic security check
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip'];
                    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    if (in_array($file_extension, $allowed_extensions)) {
                        $mail->addAttachment($tmp_name, $file_name);
                    }
                }
            }
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $_POST['subject'];
        $mail->Body = nl2br(htmlspecialchars($_POST['message']));
        $mail->AltBody = strip_tags($_POST['message']);

        // Send email
        if ($mail->send()) {
            $message = 'Email sent successfully!';
            $debug_info = ''; // Clear debug info on success
        }

    } catch (Exception $e) {
        $error = "Email could not be sent. Error: " . $e->getMessage();

        // Provide specific error messages for common issues
        if (strpos($e->getMessage(), 'SMTP Error: Could not authenticate') !== false) {
            $error .= "<br><br><strong>Authentication Error Solutions:</strong><br>";
            $error .= "• For Gmail: Use App Password instead of regular password<br>";
            $error .= "• For Outlook: Enable 2FA and use app password<br>";
            $error .= "• Check if username and password are correct<br>";
            $error .= "• Verify SMTP settings for your email provider";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Email - Email System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        textarea {
            height: 150px;
            resize: vertical;
        }

        button {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #0056b3;
        }

        button:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .help-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }

        .file-input {
            margin-top: 5px;
        }

        .config-warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }

        .debug-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }

        .nav-links a {
            color: #007bff;
            text-decoration: none;
            margin-right: 15px;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Send Email</h1>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <?php if (isAdmin()): ?>
                    <a href="admin.php">Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <?php if (!testEmailConfig()): ?>
            <div class="config-warning">
                <strong>⚠️ Email Configuration Required</strong><br>
                Please update your email settings in <code>config/email_config.php</code> before sending emails.
                <br><br>
                <strong>Quick Setup for Gmail:</strong><br>
                1. Enable 2-Factor Authentication on your Google account<br>
                2. Generate an App Password: <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App
                    Passwords</a><br>
                3. Update EMAIL_PROVIDER to 'gmail' and add your credentials in email_config.php
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="to">To (Recipients):</label>
                <input type="text" id="to" name="to" required
                    value="<?php echo isset($_POST['to']) ? htmlspecialchars($_POST['to']) : ''; ?>">
                <div class="help-text">Separate multiple email addresses with commas</div>
            </div>

            <div class="form-group">
                <label for="cc">CC (Carbon Copy):</label>
                <input type="text" id="cc" name="cc"
                    value="<?php echo isset($_POST['cc']) ? htmlspecialchars($_POST['cc']) : ''; ?>">
                <div class="help-text">Optional: Separate multiple email addresses with commas</div>
            </div>

            <div class="form-group">
                <label for="bcc">BCC (Blind Carbon Copy):</label>
                <input type="text" id="bcc" name="bcc"
                    value="<?php echo isset($_POST['bcc']) ? htmlspecialchars($_POST['bcc']) : ''; ?>">
                <div class="help-text">Optional: Separate multiple email addresses with commas</div>
            </div>

            <div class="form-group">
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" required
                    value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="message">Message:</label>
                <textarea id="message" name="message"
                    required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="attachments">Attachments:</label>
                <input type="file" id="attachments" name="attachments[]" multiple class="file-input"
                    accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip">
                <div class="help-text">Allowed: JPG, PNG, PDF, DOC, DOCX, TXT, ZIP (Max 10MB per file)</div>
            </div>

            <button type="submit" <?php echo !testEmailConfig() ? 'disabled' : ''; ?>>
                <?php echo testEmailConfig() ? 'Send Email' : 'Configure Email First'; ?>
            </button>
        </form>

        <?php if ($debug_info && $error): ?>
            <div class="debug-info">
                <strong>Debug Information:</strong><br>
                <?php echo htmlspecialchars($debug_info); ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
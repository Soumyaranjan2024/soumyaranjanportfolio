<?php
require_once 'config/email_config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$bulk_message = '';
$bulk_error = '';
$email_stats = [];

// Get email statistics
try {
    $total_contacts = $pdo->query("SELECT COUNT(DISTINCT email) FROM messages WHERE email != ''")->fetchColumn();
    $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE email != ''")->fetchColumn();
    $email_stats = [
        'total_contacts' => $total_contacts,
        'total_users' => $total_users,
        'total_recipients' => $total_contacts + $total_users
    ];
} catch (Exception $e) {
    $email_stats = ['total_contacts' => 0, 'total_users' => 0, 'total_recipients' => 0];
}

// Handle bulk email sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_bulk_email'])) {
    $subject = trim($_POST['subject']);
    $message_body = trim($_POST['message']);
    $recipient_type = $_POST['recipient_type'];
    $include_name = isset($_POST['include_name']);

    // Handle file upload
    $attachment_path = '';
    $attachment_name = '';

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/bulk_emails/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_name = $_FILES['attachment']['name'];
        $file_tmp = $_FILES['attachment']['tmp_name'];
        $file_size = $_FILES['attachment']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Check file type and size
        $allowed_types = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'];
        $max_size = 10 * 1024 * 1024; // 10MB

        if (!in_array($file_ext, $allowed_types)) {
            $bulk_error = 'Invalid file type. Allowed: PDF, DOC, DOCX, TXT, JPG, PNG';
        } elseif ($file_size > $max_size) {
            $bulk_error = 'File too large. Maximum size: 10MB';
        } else {
            $safe_filename = date('Y-m-d_H-i-s') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file_name);
            $attachment_path = $upload_dir . $safe_filename;

            if (move_uploaded_file($file_tmp, $attachment_path)) {
                $attachment_name = $file_name;
            } else {
                $bulk_error = 'Failed to upload attachment.';
            }
        }
    }

    if (empty($subject) || empty($message_body)) {
        $bulk_error = 'Please fill in both subject and message fields.';
    } elseif (!$bulk_error) {
        $recipients = [];

        try {
            // Get recipients based on type
            switch ($recipient_type) {
                case 'contacts':
                    $stmt = $pdo->query("SELECT DISTINCT name, email FROM messages WHERE email != '' ORDER BY name");
                    $recipients = $stmt->fetchAll();
                    break;

                case 'users':
                    $stmt = $pdo->query("SELECT username as name, email FROM users WHERE email != '' ORDER BY username");
                    $recipients = $stmt->fetchAll();
                    break;

                case 'all':
                default:
                    // Get contacts
                    $stmt1 = $pdo->query("SELECT DISTINCT name, email FROM messages WHERE email != ''");
                    $contacts = $stmt1->fetchAll();

                    // Get users
                    $stmt2 = $pdo->query("SELECT username as name, email FROM users WHERE email != ''");
                    $users = $stmt2->fetchAll();

                    // Merge and remove duplicates by email
                    $all_recipients = array_merge($contacts, $users);
                    $unique_emails = [];
                    foreach ($all_recipients as $recipient) {
                        if (!isset($unique_emails[$recipient['email']])) {
                            $unique_emails[$recipient['email']] = $recipient;
                        }
                    }
                    $recipients = array_values($unique_emails);
                    break;
            }

            if (empty($recipients)) {
                $bulk_error = 'No recipients found for the selected type.';
            } else {
                $sent_count = 0;
                $failed_count = 0;
                $failed_emails = [];

                // Log bulk email campaign
                $campaign_id = logBulkEmailCampaign($subject, $message_body, count($recipients), $attachment_name);

                foreach ($recipients as $recipient) {
                    try {
                        $result = sendBulkEmail(
                            $recipient['email'],
                            $recipient['name'],
                            $subject,
                            $message_body,
                            $include_name,
                            $attachment_path,
                            $attachment_name
                        );

                        // Log individual email
                        logSentEmail($campaign_id, $recipient['email'], $recipient['name'], $result);

                        if ($result) {
                            $sent_count++;
                        } else {
                            $failed_count++;
                            $failed_emails[] = $recipient['email'];
                        }
                    } catch (Exception $e) {
                        $failed_count++;
                        $failed_emails[] = $recipient['email'];
                        error_log("Bulk email error for " . $recipient['email'] . ": " . $e->getMessage());
                    }

                    // Small delay to avoid overwhelming SMTP server
                    usleep(500000); // 0.5 second delay
                }

                // Update campaign stats
                updateCampaignStats($campaign_id, $sent_count, $failed_count);

                if ($sent_count > 0) {
                    $bulk_message = "Bulk email sent successfully to $sent_count recipients!";
                    if ($attachment_name) {
                        $bulk_message .= " (with attachment: $attachment_name)";
                    }
                    if ($failed_count > 0) {
                        $bulk_message .= " ($failed_count failed: " . implode(', ', array_slice($failed_emails, 0, 5));
                        if (count($failed_emails) > 5) {
                            $bulk_message .= " and " . (count($failed_emails) - 5) . " more";
                        }
                        $bulk_message .= ")";
                    }
                } else {
                    $bulk_error = "Failed to send emails to all recipients.";
                }

                // Clean up attachment file after sending
                if ($attachment_path && file_exists($attachment_path)) {
                    unlink($attachment_path);
                }
            }

        } catch (Exception $e) {
            $bulk_error = "Database error: " . $e->getMessage();
        }
    }
}

function sendBulkEmail($to_email, $to_name, $subject, $message_body, $include_name = true, $attachment_path = '', $attachment_name = '')
{
    $config = getEmailConfig();
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];

        // SSL options
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to_email, $to_name);
        $mail->addReplyTo($config['reply_to'], $config['from_name']);

        // Add attachment if provided
        if ($attachment_path && file_exists($attachment_path)) {
            $mail->addAttachment($attachment_path, $attachment_name);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;

        // Personalize message if requested
        $personalized_message = $message_body;
        if ($include_name && !empty($to_name)) {
            $personalized_message = "Dear " . htmlspecialchars($to_name) . ",\n\n" . $message_body;
        }

        $mail->Body = generateBulkEmailTemplate($to_name, $subject, $personalized_message, $attachment_name);
        $mail->AltBody = strip_tags($personalized_message);

        return $mail->send();

    } catch (Exception $e) {
        error_log("Bulk email failed for $to_email: " . $e->getMessage());
        return false;
    }
}

function generateBulkEmailTemplate($name, $subject, $message, $attachment_name = '')
{
    $current_year = date('Y');
    $company_name = COMPANY_NAME;

    $attachment_notice = '';
    if ($attachment_name) {
        $attachment_notice = "
        <div style='background-color: #e7f3ff; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff;'>
            <strong>📎 Attachment Included:</strong> " . htmlspecialchars($attachment_name) . "
        </div>";
    }

    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
            .content { padding: 30px 20px; background-color: #ffffff; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; background-color: #f8f9fa; }
            .message-content { background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . htmlspecialchars($subject) . "</h1>
                <p>Message from " . htmlspecialchars($company_name) . "</p>
            </div>
            <div class='content'>
                " . $attachment_notice . "
                
                <div class='message-content'>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
                
                <p>Thank you for being part of our community!</p>
                
                <p>Best regards,<br>
                <strong>Soumya Ranjan Padhi</strong><br>
                <em>Full Stack Developer</em></p>
            </div>
            <div class='footer'>
                <p>&copy; {$current_year} {$company_name}. All rights reserved.</p>
                <p>You received this email because you contacted us through our portfolio or are a registered user.</p>
                <p>If you no longer wish to receive emails, please contact us directly.</p>
            </div>
        </div>
    </body>
    </html>";
}

// Database functions for logging
function logBulkEmailCampaign($subject, $message, $recipient_count, $attachment_name = '')
{
    global $pdo;

    try {
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("INSERT INTO bulk_email_campaigns (subject, message, recipient_count, attachment_name, created_at, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$subject, $message, $recipient_count, $attachment_name, $now, $_SESSION['user']['username']]);
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Failed to log bulk email campaign: " . $e->getMessage());
        return 0;
    }
}

function logSentEmail($campaign_id, $email, $name, $success)
{
    global $pdo;

    try {
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("INSERT INTO sent_emails (campaign_id, recipient_email, recipient_name, sent_at, success) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$campaign_id, $email, $name, $now, $success ? 1 : 0]);
    } catch (Exception $e) {
        error_log("Failed to log sent email: " . $e->getMessage());
    }
}

function updateCampaignStats($campaign_id, $sent_count, $failed_count)
{
    global $pdo;

    try {
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("UPDATE bulk_email_campaigns SET sent_count = ?, failed_count = ?, completed_at = ? WHERE id = ?");
        $stmt->execute([$sent_count, $failed_count, $now, $campaign_id]);
    } catch (Exception $e) {
        error_log("Failed to update campaign stats: " . $e->getMessage());
    }
}
?>

<div class="card">
    <h3>📧 Bulk Email System with Attachments</h3>

    <!-- Email Statistics -->
    <div class="card" style="background-color: #e7f3ff; margin-bottom: 20px;">
        <h4>📊 Email Statistics</h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div style="text-align: center; padding: 15px; background: white; border-radius: 8px;">
                <h3 style="margin: 0; color: #667eea;"><?php echo $email_stats['total_contacts']; ?></h3>
                <p style="margin: 5px 0;">Contact Form Submissions</p>
            </div>
            <div style="text-align: center; padding: 15px; background: white; border-radius: 8px;">
                <h3 style="margin: 0; color: #764ba2;"><?php echo $email_stats['total_users']; ?></h3>
                <p style="margin: 5px 0;">Registered Users</p>
            </div>
            <div style="text-align: center; padding: 15px; background: white; border-radius: 8px;">
                <h3 style="margin: 0; color: #28a745;"><?php echo $email_stats['total_recipients']; ?></h3>
                <p style="margin: 5px 0;">Total Unique Recipients</p>
            </div>
        </div>
    </div>

    <?php if ($bulk_message): ?>
        <div
            style="color: green; background: #d4edda; padding: 15px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
            ✅ <?php echo $bulk_message; ?>
        </div>
    <?php endif; ?>

    <?php if ($bulk_error): ?>
        <div
            style="color: #721c24; background: #f8d7da; padding: 15px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
            ❌ <?php echo $bulk_error; ?>
        </div>
    <?php endif; ?>

    <?php if (!testEmailConfig()): ?>
        <div
            style="color: #856404; background: #fff3cd; padding: 15px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ffeaa7;">
            ⚠️ <strong>Email Configuration Required:</strong> Please configure your email settings in
            config/email_config.php before sending bulk emails.
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="bulkEmailForm">
        <div class="form-group">
            <label for="recipient_type">📋 Send To:</label>
            <select name="recipient_type" id="recipient_type" required>
                <option value="all">All Recipients (Contacts + Users)</option>
                <option value="contacts">Contact Form Submissions Only</option>
                <option value="users">Registered Users Only</option>
            </select>
        </div>

        <div class="form-group">
            <label for="subject">📝 Subject:</label>
            <input type="text" name="subject" id="subject" required placeholder="Enter email subject..."
                value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="message">💬 Message:</label>
            <textarea name="message" id="message" required placeholder="Enter your message here..."
                style="height: 200px;"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
            <small style="color: #666;">Tip: Keep your message professional and valuable to recipients.</small>
        </div>

        <div class="form-group">
            <label for="attachment">📎 Attachment (Optional):</label>
            <input type="file" name="attachment" id="attachment" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png"
                style="width: 100%; padding: 10px; border: 2px dashed #ddd; border-radius: 4px;">
            <small style="color: #666;">
                Allowed file types: PDF, DOC, DOCX, TXT, JPG, PNG | Maximum size: 10MB<br>
                <strong>Recommended:</strong> PDF files for documents, images for visual content
            </small>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="include_name" value="1" <?php echo (isset($_POST['include_name']) || !isset($_POST['send_bulk_email'])) ? 'checked' : ''; ?>>
                Personalize emails with recipient names ("Dear [Name],")
            </label>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" name="send_bulk_email" class="btn btn-primary" <?php echo !testEmailConfig() ? 'disabled' : ''; ?>
                onclick="return confirm('Are you sure you want to send this email to all selected recipients? This action cannot be undone.')">
                📤 Send Bulk Email
            </button>

            <button type="button" class="btn" style="background: #6c757d; color: white; margin-left: 10px;"
                onclick="document.getElementById('bulkEmailForm').reset(); updatePreview();">
                🔄 Clear Form
            </button>
        </div>
    </form>

    <!-- Preview Section -->
    <div class="card" style="margin-top: 30px; background-color: #f8f9fa;">
        <h4>👁️ Email Preview</h4>
        <div id="emailPreview"
            style="border: 1px solid #ddd; padding: 15px; background: white; border-radius: 4px; min-height: 100px;">
            <p style="color: #666; font-style: italic;">Fill in the subject and message above to see a preview of your
                email.</p>
        </div>
    </div>
</div>

<script>
    // Real-time email preview
    function updatePreview() {
        const subject = document.getElementById('subject').value;
        const message = document.getElementById('message').value;
        const includeName = document.querySelector('input[name="include_name"]').checked;
        const attachment = document.getElementById('attachment').files[0];
        const preview = document.getElementById('emailPreview');

        if (subject || message || attachment) {
            let previewContent = '<div style="border-left: 4px solid #667eea; padding-left: 15px;">';
            previewContent += '<h4 style="color: #667eea; margin-top: 0;">' + (subject || '[Subject]') + '</h4>';

            if (attachment) {
                previewContent += '<div style="background-color: #e7f3ff; padding: 10px; border-radius: 4px; margin: 10px 0;">';
                previewContent += '<strong>📎 Attachment:</strong> ' + attachment.name + ' (' + (attachment.size / 1024 / 1024).toFixed(2) + ' MB)';
                previewContent += '</div>';
            }

            if (includeName) {
                previewContent += '<p><strong>Dear [Recipient Name],</strong></p>';
            }

            previewContent += '<div style="white-space: pre-line; background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0;">' + (message || '[Message content]') + '</div>';
            previewContent += '<br><p>Best regards,<br><strong>Soumya Ranjan Padhi</strong><br><em>Full Stack Developer</em></p>';
            previewContent += '</div>';

            preview.innerHTML = previewContent;
        } else {
            preview.innerHTML = '<p style="color: #666; font-style: italic;">Fill in the subject and message above to see a preview of your email.</p>';
        }
    }

    // Add event listeners
    document.getElementById('subject').addEventListener('input', updatePreview);
    document.getElementById('message').addEventListener('input', updatePreview);
    document.getElementById('attachment').addEventListener('change', updatePreview);
    document.querySelector('input[name="include_name"]').addEventListener('change', updatePreview);

    // File size validation
    document.getElementById('attachment').addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                alert('File is too large! Maximum size is 10MB.');
                this.value = '';
                updatePreview();
            }
        }
    });

    // Update recipient count when selection changes
    document.getElementById('recipient_type').addEventListener('change', function () {
        const type = this.value;
        const stats = <?php echo json_encode($email_stats); ?>;
        let count = 0;

        switch (type) {
            case 'contacts':
                count = stats.total_contacts;
                break;
            case 'users':
                count = stats.total_users;
                break;
            case 'all':
            default:
                count = stats.total_recipients;
                break;
        }

        const button = document.querySelector('button[name="send_bulk_email"]');
        if (button && !button.disabled) {
            button.innerHTML = '📤 Send to ' + count + ' Recipients';
        }
    });

    // Initialize
    updatePreview();
</script>
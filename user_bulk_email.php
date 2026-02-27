<?php
require_once 'auth.php';
require_once 'database.php';
require_once 'config/email_config.php';

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';
$username = $_SESSION['username'];

// Get imported contacts count
try {
    $contactsStmt = $pdo->prepare("SELECT COUNT(*) FROM imported_data WHERE uploaded_by = ?");
    $contactsStmt->execute([$username]);
    $contactCount = $contactsStmt->fetchColumn();
} catch (Exception $e) {
    $contactCount = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_bulk_email'])) {
    try {
        $subject = trim($_POST['subject']);
        $messageBody = trim($_POST['message']);
        $includeAttachment = isset($_POST['include_attachment']);

        if (empty($subject) || empty($messageBody)) {
            throw new Exception('Subject and message are required');
        }

        // Get all imported contacts for this user
        $contactsStmt = $pdo->prepare("
            SELECT name, email, company, position 
            FROM imported_data 
            WHERE uploaded_by = ? AND email IS NOT NULL AND email != ''
        ");
        $contactsStmt->execute([$username]);
        $contacts = $contactsStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($contacts)) {
            throw new Exception('No contacts found to send emails to');
        }

        // Handle file attachment
        $attachmentPath = '';
        $attachmentName = '';
        if ($includeAttachment && isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/attachments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $attachmentName = $_FILES['attachment']['name'];
            $attachmentPath = $uploadDir . uniqid() . '_' . $attachmentName;

            if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $attachmentPath)) {
                throw new Exception('Failed to upload attachment');
            }
        }

        // Create bulk email campaign record
        $campaignStmt = $pdo->prepare("
            INSERT INTO bulk_email_campaigns 
            (subject, message, recipient_count, attachment_name, created_by) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $campaignStmt->execute([$subject, $messageBody, count($contacts), $attachmentName, $username]);
        $campaignId = $pdo->lastInsertId();

        // Send emails
        $sentCount = 0;
        $failedCount = 0;

        foreach ($contacts as $contact) {
            try {
                $personalizedMessage = str_replace(
                    ['[NAME]', '[COMPANY]', '[POSITION]'],
                    [$contact['name'], $contact['company'], $contact['position']],
                    $messageBody
                );

                // Use your existing email sending function
                $emailSent = sendEmail(
                    $contact['email'],
                    $contact['name'],
                    $subject,
                    $personalizedMessage,
                    $attachmentPath
                );

                if ($emailSent) {
                    $sentCount++;
                    $success = true;
                } else {
                    $failedCount++;
                    $success = false;
                }

                // Record individual email
                $emailStmt = $pdo->prepare("
                    INSERT INTO sent_emails 
                    (campaign_id, recipient_email, recipient_name, success) 
                    VALUES (?, ?, ?, ?)
                ");
                $emailStmt->execute([$campaignId, $contact['email'], $contact['name'], $success]);

            } catch (Exception $e) {
                $failedCount++;

                // Record failed email
                $emailStmt = $pdo->prepare("
                    INSERT INTO sent_emails 
                    (campaign_id, recipient_email, recipient_name, success) 
                    VALUES (?, ?, ?, 0)
                ");
                $emailStmt->execute([$campaignId, $contact['email'], $contact['name']]);
            }
        }

        // Update campaign with results
        $updateStmt = $pdo->prepare("
            UPDATE bulk_email_campaigns 
            SET sent_count = ?, failed_count = ?, completed_at = NOW() 
            WHERE id = ?
        ");
        $updateStmt->execute([$sentCount, $failedCount, $campaignId]);

        // Clean up attachment file
        if ($attachmentPath && file_exists($attachmentPath)) {
            unlink($attachmentPath);
        }

        $message = "Bulk email campaign completed! Sent: $sentCount, Failed: $failedCount";

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Function to send email (you'll need to implement this based on your email config)
function sendEmail($to, $name, $subject, $message, $attachmentPath = '')
{
    // Implement your email sending logic here
    // This should use your existing email configuration
    // Return true on success, false on failure

    // Example implementation:
    try {
        // Use PHPMailer or your preferred email library
        // Configure with your SMTP settings
        // Send the email
        return true; // Return true if email sent successfully
    } catch (Exception $e) {
        return false; // Return false if email failed
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Email Campaign - Portfolio Email System</title>
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
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #fd7e14;
            padding-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        textarea {
            height: 200px;
            resize: vertical;
        }

        .btn {
            background: #fd7e14;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }

        .btn:hover {
            background: #e8590c;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .info-box {
            background: #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .contact-preview {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .contact-preview h4 {
            margin-top: 0;
            color: #fd7e14;
        }

        .contact-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
        }

        .contact-item {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }

        .contact-item:last-child {
            border-bottom: none;
        }

        .personalization-help {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
            font-size: 14px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📧 Bulk Email Campaign</h1>
            <p>Send emails to all your imported contacts</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="info-box">
            <strong>📊 Campaign Overview:</strong><br>
            • Total imported contacts: <strong><?php echo $contactCount; ?></strong><br>
            • Contacts with valid emails: <strong id="validEmailCount">Loading...</strong><br>
            • User: <strong><?php echo htmlspecialchars($username); ?></strong>
        </div>

        <?php if ($contactCount > 0): ?>
            <div class="contact-preview">
                <h4>👥 Your Imported Contacts Preview</h4>
                <div class="contact-list" id="contactList">
                    <?php
                    try {
                        $previewStmt = $pdo->prepare("
                            SELECT name, email, company, position 
                            FROM imported_data 
                            WHERE uploaded_by = ? AND email IS NOT NULL AND email != ''
                            LIMIT 10
                        ");
                        $previewStmt->execute([$username]);
                        $previewContacts = $previewStmt->fetchAll(PDO::FETCH_ASSOC);

                        $validEmailStmt = $pdo->prepare("
                            SELECT COUNT(*) 
                            FROM imported_data 
                            WHERE uploaded_by = ? AND email IS NOT NULL AND email != ''
                        ");
                        $validEmailStmt->execute([$username]);
                        $validEmailCount = $validEmailStmt->fetchColumn();

                        echo "<script>document.getElementById('validEmailCount').textContent = '$validEmailCount';</script>";

                        foreach ($previewContacts as $contact) {
                            echo '<div class="contact-item">';
                            echo '<strong>' . htmlspecialchars($contact['name']) . '</strong> - ';
                            echo htmlspecialchars($contact['email']);
                            if ($contact['company']) {
                                echo ' (' . htmlspecialchars($contact['company']) . ')';
                            }
                            echo '</div>';
                        }

                        if (count($previewContacts) >= 10) {
                            echo '<div class="contact-item"><em>... and ' . ($validEmailCount - 10) . ' more contacts</em></div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="contact-item">Error loading contacts</div>';
                    }
                    ?>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="subject">Email Subject:</label>
                    <input type="text" name="subject" id="subject" required placeholder="Enter email subject"
                        value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="message">Email Message:</label>
                    <textarea name="message" id="message" required
                        placeholder="Enter your email message..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>

                    <div class="personalization-help">
                        <strong>💡 Personalization Tags:</strong><br>
                        Use <code>[NAME]</code> for recipient name, <code>[COMPANY]</code> for company,
                        <code>[POSITION]</code> for position<br>
                        Example: "Dear [NAME], I hope this email finds you well at [COMPANY]..."
                    </div>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="include_attachment" id="include_attachment">
                        <label for="include_attachment">Include file attachment</label>
                    </div>
                    <input type="file" name="attachment" id="attachment" style="display: none;"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt">
                </div>

                <div class="form-group">
                    <button type="submit" name="send_bulk_email" class="btn"
                        onclick="return confirm('Are you sure you want to send this email to all <?php echo $validEmailCount; ?> contacts?')">
                        📧 Send Bulk Email Campaign
                    </button>
                    <a href="user_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                </div>
            </form>

        <?php else: ?>
            <div class="alert alert-danger">
                <strong>No contacts found!</strong><br>
                You need to import contacts first before sending bulk emails.<br>
                <a href="user_excel_import.php">📥 Import Contacts Now</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Show/hide attachment field
        document.getElementById('include_attachment').addEventListener('change', function () {
            const attachmentField = document.getElementById('attachment');
            if (this.checked) {
                attachmentField.style.display = 'block';
                attachmentField.required = true;
            } else {
                attachmentField.style.display = 'none';
                attachmentField.required = false;
            }
        });
    </script>
</body>

</html>
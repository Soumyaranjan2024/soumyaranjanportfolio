<?php
require_once 'config/email_config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendAutoReply($to_email, $to_name, $subject = '', $debug = false)
{
    if (!AUTO_REPLY_ENABLED) {
        return false;
    }

    $config = getEmailConfig();
    
    if (empty($config['username']) || empty($config['password'])) {
        error_log("Email config missing username or password.");
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // Enable debug if requested
        if ($debug) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = 'html';
        }

        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];

        // SSL options for Gmail
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

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Thank you for contacting me - Message Received';

        $original_subject = $subject ? $subject : 'No Subject';
        $mail->Body = generateEmailTemplate($to_name, $original_subject);
        $mail->AltBody = generatePlainTextEmail($to_name, $original_subject);

        $result = $mail->send();

        if ($debug) {
            echo $result ? "Auto-reply sent successfully!" : "Failed to send auto-reply";
        }

        return $result;

    } catch (Exception $e) {
        $error_msg = "Auto-reply failed: {$mail->ErrorInfo}";
        error_log($error_msg);

        if ($debug) {
            echo "Error: " . $error_msg;
        }

        return false;
    }
}

function sendAdminReply($to_email, $to_name, $original_subject, $reply_text, $debug = false)
{
    $config = getEmailConfig();
    
    if (empty($config['username']) || empty($config['password'])) {
        error_log("Email config missing username or password.");
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        if ($debug) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = 'html';
        }

        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to_email, $to_name);
        $mail->addReplyTo($config['reply_to'], $config['from_name']);

        $mail->isHTML(true);
        $mail->Subject = 'Re: ' . ($original_subject ?: 'Your message to Soumya Portfolio');
        
        $mail->Body = generateAdminReplyTemplate($to_name, $reply_text);
        $mail->AltBody = strip_tags($reply_text);

        return $mail->send();

    } catch (Exception $e) {
        error_log("Admin reply failed: {$mail->ErrorInfo}");
        return false;
    }
}

function generateAdminReplyTemplate($name, $reply_text)
{
    $current_year = date('Y');
    $company_name = COMPANY_NAME;

    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; border: 1px solid #eee; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px 20px; text-align: center; }
            .content { padding: 30px 20px; background-color: #ffffff; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; background-color: #f8f9fa; border-top: 1px solid #eee; }
            .reply-box { background-color: #f0f7ff; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Response from Soumya</h2>
            </div>
            <div class='content'>
                <p>Hello " . htmlspecialchars($name) . ",</p>
                <p>Thank you for your message. Here is my response:</p>
                
                <div class='reply-box'>
                    " . $reply_text . "
                </div>
                
                <p>If you have any further questions, feel free to reply to this email or reach out through my portfolio.</p>
                
                <p>Best regards,<br>
                <strong>Soumya Ranjan Padhi</strong><br>
                <em>Full Stack Developer</em></p>
            </div>
            <div class='footer'>
                <p>&copy; {$current_year} {$company_name}. All rights reserved.</p>
                <p>You received this email because you contacted me through my portfolio website.</p>
            </div>
        </div>
    </body>
    </html>";
}

function generateEmailTemplate($name, $subject)
{
    $current_year = date('Y');
    $company_name = COMPANY_NAME;

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
            .highlight { background-color: #e7f3ff; padding: 15px; border-left: 4px solid #667eea; margin: 20px 0; border-radius: 4px; }
            .button { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🙏 Thank You for Reaching Out!</h1>
                <p>Your message has been received</p>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($name) . ",</p>
                
                <p>Thank you for taking the time to contact me through my portfolio website. I truly appreciate your interest in my work and I'm excited to connect with you!</p>
                
                <div class='highlight'>
                    <strong>📧 Your Message Details:</strong><br>
                    <strong>Subject:</strong> " . htmlspecialchars($subject) . "<br>
                    <strong>Received:</strong> " . date('F j, Y \a\t g:i A') . "<br>
                    <strong>Status:</strong> ✅ Successfully received and stored
                </div>
                
                <p>I personally review every message that comes through my portfolio contact form. You can expect to hear back from me within 24-48 hours during business days.</p>
                
                <p>In the meantime, feel free to:</p>
                <ul>
                    <li>Check out my latest projects on my portfolio</li>
                    <li>Connect with me on LinkedIn or other social platforms</li>
                    <li>Browse through my skills and experience</li>
                </ul>
                
                <p>If your message is urgent, please don't hesitate to reach out to me directly.</p>
                
                <p>Thank you again for your interest, and I look forward to our conversation!</p>
                
                <p>Best regards,<br>
                <strong>Soumya Ranjan Padhi</strong><br>
                <em>Full Stack Developer</em></p>
            </div>
            <div class='footer'>
                <p>&copy; {$current_year} {$company_name}. All rights reserved.</p>
                <p>This is an automated confirmation message. Please do not reply to this email.</p>
                <p>If you need immediate assistance, please contact me directly through my portfolio.</p>
            </div>
        </div>
    </body>
    </html>";
}

function generatePlainTextEmail($name, $subject)
{
    $current_year = date('Y');
    $company_name = COMPANY_NAME;

    return "
Dear " . $name . ",

Thank you for taking the time to contact me through my portfolio website. I truly appreciate your interest in my work and I'm excited to connect with you!

📧 Your Message Details:
Subject: " . $subject . "
Received: " . date('F j, Y \a\t g:i A') . "
Status: ✅ Successfully received and stored

I personally review every message that comes through my portfolio contact form. You can expect to hear back from me within 24-48 hours during business days.

In the meantime, feel free to:
• Check out my latest projects on my portfolio
• Connect with me on LinkedIn or other social platforms  
• Browse through my skills and experience

If your message is urgent, please don't hesitate to reach out to me directly.

Thank you again for your interest, and I look forward to our conversation!

Best regards,
Soumya Ranjan Padhi
Full Stack Developer

---
© {$current_year} {$company_name}. All rights reserved.
This is an automated confirmation message. Please do not reply to this email.
If you need immediate assistance, please contact me directly through my portfolio.
";
}
?>
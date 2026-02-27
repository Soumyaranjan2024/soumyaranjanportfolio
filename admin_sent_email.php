<?php
// Get sent email campaigns
$campaigns = $pdo->query("
    SELECT * FROM bulk_email_campaigns 
    ORDER BY created_at DESC 
    LIMIT 50
")->fetchAll();

// Get detailed view if requested
$campaign_details = null;
if (isset($_GET['campaign_id'])) {
    $campaign_id = $_GET['campaign_id'];

    $stmt = $pdo->prepare("SELECT * FROM bulk_email_campaigns WHERE id = ?");
    $stmt->execute([$campaign_id]);
    $campaign_details = $stmt->fetch();

    if ($campaign_details) {
        $stmt = $pdo->prepare("
            SELECT * FROM sent_emails 
            WHERE campaign_id = ? 
            ORDER BY sent_at DESC
        ");
        $stmt->execute([$campaign_id]);
        $sent_emails = $stmt->fetchAll();
    }
}
?>

<div class="card">
    <h3>📧 Sent Email History</h3>

    <?php if ($campaign_details): ?>
        <!-- Campaign Details View -->
        <div class="card" style="background-color: #f8f9fa; margin-bottom: 20px;">
            <h4>📋 Campaign Details</h4>
            <p><strong>Subject:</strong> <?php echo htmlspecialchars($campaign_details['subject']); ?></p>
            <p><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($campaign_details['created_at'])); ?></p>
            <p><strong>Created by:</strong> <?php echo htmlspecialchars($campaign_details['created_by']); ?></p>
            <p><strong>Recipients:</strong> <?php echo $campaign_details['recipient_count']; ?></p>
            <p><strong>Sent:</strong> <?php echo $campaign_details['sent_count']; ?></p>
            <p><strong>Failed:</strong> <?php echo $campaign_details['failed_count']; ?></p>
            <?php if ($campaign_details['attachment_name']): ?>
                <p><strong>Attachment:</strong> <?php echo htmlspecialchars($campaign_details['attachment_name']); ?></p>
            <?php endif; ?>

            <div style="background: white; padding: 15px; border-radius: 4px; margin-top: 15px;">
                <strong>Message:</strong><br>
                <?php echo nl2br(htmlspecialchars($campaign_details['message'])); ?>
            </div>

            <a href="admin.php?section=sent_emails" class="btn btn-primary" style="margin-top: 15px;">← Back to List</a>
        </div>

        <!-- Individual Email Status -->
        <div class="card">
            <h4>📬 Individual Email Status</h4>
            <table>
                <thead>
                    <tr>
                        <th>Recipient</th>
                        <th>Email</th>
                        <th>Sent At</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sent_emails as $email): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($email['recipient_name']); ?></td>
                            <td><?php echo htmlspecialchars($email['recipient_email']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($email['sent_at'])); ?></td>
                            <td>
                                <?php if ($email['success']): ?>
                                    <span style="color: green;">✅ Sent</span>
                                <?php else: ?>
                                    <span style="color: red;">❌ Failed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <!-- Campaigns List View -->
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Recipients</th>
                    <th>Sent/Failed</th>
                    <th>Attachment</th>
                    <th>Date</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($campaigns)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #666; font-style: italic;">
                            No bulk emails sent yet. <a href="admin.php?section=bulk_email">Send your first bulk email</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($campaigns as $campaign): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($campaign['subject']); ?></strong>
                                <br>
                                <small style="color: #666;">
                                    <?php echo substr(htmlspecialchars($campaign['message']), 0, 100); ?>...
                                </small>
                            </td>
                            <td><?php echo $campaign['recipient_count']; ?></td>
                            <td>
                                <span style="color: green;"><?php echo $campaign['sent_count']; ?> sent</span>
                                <?php if ($campaign['failed_count'] > 0): ?>
                                    <br><span style="color: red;"><?php echo $campaign['failed_count']; ?> failed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($campaign['attachment_name']): ?>
                                    📎 <?php echo htmlspecialchars($campaign['attachment_name']); ?>
                                <?php else: ?>
                                    <span style="color: #999;">No attachment</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($campaign['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($campaign['created_by']); ?></td>
                            <td>
                                <a href="admin.php?section=sent_emails&campaign_id=<?php echo $campaign['id']; ?>"
                                    class="btn btn-primary">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>s
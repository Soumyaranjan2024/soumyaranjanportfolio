<?php
// Get additional statistics for bulk emails
try {
    $total_campaigns = $pdo->query("SELECT COUNT(*) FROM bulk_email_campaigns")->fetchColumn();
    $total_sent_emails = $pdo->query("SELECT SUM(sent_count) FROM bulk_email_campaigns")->fetchColumn();
    $recent_campaign = $pdo->query("SELECT * FROM bulk_email_campaigns ORDER BY created_at DESC LIMIT 1")->fetch();
} catch (Exception $e) {
    $total_campaigns = 0;
    $total_sent_emails = 0;
    $recent_campaign = null;
}

// Get recent activity (last 7 days)
try {
    $is_sqlite = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    if ($is_sqlite) {
        $recent_messages = $pdo->query("SELECT COUNT(*) FROM messages WHERE created_at >= datetime('now', '-7 days')")->fetchColumn();
        $recent_campaigns = $pdo->query("SELECT COUNT(*) FROM bulk_email_campaigns WHERE created_at >= datetime('now', '-7 days')")->fetchColumn();
    } else {
        $recent_messages = $pdo->query("SELECT COUNT(*) FROM messages WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
        $recent_campaigns = $pdo->query("SELECT COUNT(*) FROM bulk_email_campaigns WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
    }
} catch (Exception $e) {
    $recent_messages = 0;
    $recent_campaigns = 0;
}
?>

<div class="card">
    <h3>📊 Dashboard Overview</h3>
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
        <!-- Projects Card -->
        <div class="card"
            style="text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h4 style="margin-top: 0; color: white;">📁 Projects</h4>
            <p style="font-size: 2.5rem; margin: 10px 0; font-weight: bold;"><?php echo $total_projects; ?></p>
            <small>Portfolio Projects</small>
        </div>

        <!-- Skills Card -->
        <div class="card"
            style="text-align: center; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <h4 style="margin-top: 0; color: white;">🛠️ Skills</h4>
            <p style="font-size: 2.5rem; margin: 10px 0; font-weight: bold;"><?php echo $total_skills; ?></p>
            <small>Technical Skills</small>
        </div>

        <!-- Messages Card -->
        <div class="card"
            style="text-align: center; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <h4 style="margin-top: 0; color: white;">📧 Messages</h4>
            <p style="font-size: 2.5rem; margin: 10px 0; font-weight: bold;"><?php echo $total_messages; ?></p>
            <small>Contact Form Messages</small>
        </div>

        <!-- Unread Messages Card -->
        <div class="card"
            style="text-align: center; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
            <h4 style="margin-top: 0; color: white;">🔔 Unread</h4>
            <p style="font-size: 2.5rem; margin: 10px 0; font-weight: bold;"><?php echo $unread_messages; ?></p>
            <small>New Messages</small>
        </div>

        <!-- Email Campaigns Card -->
        <div class="card"
            style="text-align: center; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333;">
            <h4 style="margin-top: 0; color: #333;">📤 Campaigns</h4>
            <p style="font-size: 2.5rem; margin: 10px 0; font-weight: bold;"><?php echo $total_campaigns; ?></p>
            <small>Bulk Email Campaigns</small>
        </div>

        <!-- Total Sent Emails Card -->
        <div class="card"
            style="text-align: center; background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #333;">
            <h4 style="margin-top: 0; color: #333;">📬 Sent Emails</h4>
            <p style="font-size: 2.5rem; margin: 10px 0; font-weight: bold;"><?php echo $total_sent_emails ?: 0; ?></p>
            <small>Total Emails Sent</small>
        </div>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="card">
    <h3>📈 Recent Activity (Last 7 Days)</h3>
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
        <div style="background: #e7f3ff; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff;">
            <h4 style="margin-top: 0; color: #007bff;">📨 New Messages</h4>
            <p style="font-size: 1.8rem; margin: 5px 0; font-weight: bold; color: #007bff;">
                <?php echo $recent_messages; ?>
            </p>
            <small style="color: #666;">Contact form submissions</small>
        </div>

        <div style="background: #f0f9ff; padding: 20px; border-radius: 8px; border-left: 4px solid #0ea5e9;">
            <h4 style="margin-top: 0; color: #0ea5e9;">📤 Email Campaigns</h4>
            <p style="font-size: 1.8rem; margin: 5px 0; font-weight: bold; color: #0ea5e9;">
                <?php echo $recent_campaigns; ?>
            </p>
            <small style="color: #666;">Bulk emails sent</small>
        </div>

        <div style="background: #f0fdf4; padding: 20px; border-radius: 8px; border-left: 4px solid #22c55e;">
            <h4 style="margin-top: 0; color: #22c55e;">📊 Response Rate</h4>
            <p style="font-size: 1.8rem; margin: 5px 0; font-weight: bold; color: #22c55e;">
                <?php
                $response_rate = $total_messages > 0 ? round(($unread_messages / $total_messages) * 100, 1) : 0;
                echo $response_rate . '%';
                ?>
            </p>
            <small style="color: #666;">Unread message rate</small>
        </div>
    </div>
</div>

<!-- Quick Actions Section -->
<div class="card">
    <h3>⚡ Quick Actions</h3>
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
        <a href="admin.php?section=bulk_email" class="btn btn-primary"
            style="text-decoration: none; text-align: center; padding: 15px;">
            📤 Send Bulk Email
        </a>
        <a href="admin.php?section=messages" class="btn btn-primary"
            style="text-decoration: none; text-align: center; padding: 15px;">
            📧 View Messages
        </a>
        <a href="admin.php?section=projects" class="btn btn-primary"
            style="text-decoration: none; text-align: center; padding: 15px;">
            📁 Manage Projects
        </a>
        <a href="admin.php?section=sent_emails" class="btn btn-primary"
            style="text-decoration: none; text-align: center; padding: 15px;">
            📬 Email History
        </a>
    </div>
</div>

<!-- Recent Messages Section -->
<div class="card">
    <h3>📨 Recent Messages</h3>
    <?php if (empty($messages)): ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <p style="font-size: 1.2rem;">📭 No messages yet</p>
            <p>Messages from your portfolio contact form will appear here.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($messages, 0, 5) as $message): ?>
                    <tr style="<?php echo $message['is_read'] ? '' : 'background-color: #f8f9fa; font-weight: 500;'; ?>">
                        <td>
                            <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                            <?php if (!$message['is_read']): ?>
                                <span
                                    style="background: #dc3545; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: 5px;">NEW</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                        <td><?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?></td>
                        <td>
                            <?php
                            $date = new DateTime($message['created_at']);
                            $now = new DateTime();
                            $diff = $now->diff($date);

                            if ($diff->days == 0) {
                                echo 'Today ' . $date->format('H:i');
                            } elseif ($diff->days == 1) {
                                echo 'Yesterday';
                            } else {
                                echo $date->format('M d, Y');
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($message['is_read']): ?>
                                <span style="color: #28a745;">✅ Read</span>
                            <?php else: ?>
                                <span style="color: #dc3545;">🔴 Unread</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="admin.php?section=messages&view=<?php echo $message['id']; ?>" class="btn btn-primary"
                                style="font-size: 12px; padding: 5px 10px;">
                                View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="text-align: right; margin-top: 15px;">
            <a href="admin.php?section=messages" class="btn btn-primary">View All Messages
                (<?php echo $total_messages; ?>)</a>
        </div>
    <?php endif; ?>
</div>

<!-- Recent Email Campaigns Section -->
<?php if ($recent_campaign): ?>
    <div class="card">
        <h3>📤 Latest Email Campaign</h3>
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #6c63ff;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <h4 style="margin-top: 0; color: #6c63ff;">
                        <?php echo htmlspecialchars($recent_campaign['subject']); ?>
                    </h4>
                    <p style="color: #666; margin: 10px 0;">
                        <?php echo substr(htmlspecialchars($recent_campaign['message']), 0, 150); ?>...
                    </p>
                    <div style="display: flex; gap: 20px; margin-top: 15px;">
                        <span><strong>Recipients:</strong> <?php echo $recent_campaign['recipient_count']; ?></span>
                        <span><strong>Sent:</strong> <?php echo $recent_campaign['sent_count']; ?></span>
                        <?php if ($recent_campaign['failed_count'] > 0): ?>
                            <span style="color: #dc3545;"><strong>Failed:</strong>
                                <?php echo $recent_campaign['failed_count']; ?></span>
                        <?php endif; ?>
                    </div>
                    <small style="color: #666;">
                        Sent <?php echo date('M d, Y H:i', strtotime($recent_campaign['created_at'])); ?>
                        by <?php echo htmlspecialchars($recent_campaign['created_by']); ?>
                    </small>
                </div>
                <div>
                    <a href="admin.php?section=sent_emails&campaign_id=<?php echo $recent_campaign['id']; ?>"
                        class="btn btn-primary">View Details</a>
                </div>
            </div>
        </div>
        <div style="text-align: right; margin-top: 15px;">
            <a href="admin.php?section=sent_emails" class="btn btn-primary">View All Campaigns</a>
        </div>
    </div>
<?php endif; ?>

<!-- System Status Section -->
<div class="card">
    <h3>🔧 System Status</h3>
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 20px;">
        <!-- Email Configuration Status -->
        <div
            style="padding: 15px; border-radius: 8px; <?php echo testEmailConfig() ? 'background: #d4edda; border-left: 4px solid #28a745;' : 'background: #f8d7da; border-left: 4px solid #dc3545;'; ?>">
            <h5 style="margin-top: 0;">📧 Email System</h5>
            <?php if (testEmailConfig()): ?>
                <span style="color: #155724;">✅ Configured & Ready</span>
            <?php else: ?>
                <span style="color: #721c24;">❌ Not Configured</span>
                <br><small><a href="admin.php?section=bulk_email">Configure Email Settings</a></small>
            <?php endif; ?>
        </div>

        <!-- Database Status -->
        <div style="padding: 15px; border-radius: 8px; background: #d4edda; border-left: 4px solid #28a745;">
            <h5 style="margin-top: 0;">🗄️ Database</h5>
            <span style="color: #155724;">✅ Connected (portfolio_db)</span>
        </div>

        <!-- Upload Directory Status -->
        <div
            style="padding: 15px; border-radius: 8px; <?php echo is_writable('uploads/') ? 'background: #d4edda; border-left: 4px solid #28a745;' : 'background: #f8d7da; border-left: 4px solid #dc3545;'; ?>">
            <h5 style="margin-top: 0;">📁 File Uploads</h5>
            <?php if (is_writable('uploads/')): ?>
                <span style="color: #155724;">✅ Upload Directory Writable</span>
            <?php else: ?>
                <span style="color: #721c24;">❌ Upload Directory Not Writable</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    /* Additional styles for the dashboard */
    .btn {
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card {
            margin-bottom: 15px;
        }

        .card h3 {
            font-size: 1.2rem;
        }

        table {
            font-size: 14px;
        }

        .btn {
            padding: 8px 12px;
            font-size: 12px;
        }
    }
</style>
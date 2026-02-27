<?php
require_once 'auth.php';
require_once 'database.php';

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];

// Get user's campaigns
try {
    $campaignsStmt = $pdo->prepare("
        SELECT 
            id, subject, recipient_count, sent_count, failed_count, 
            attachment_name, created_at, completed_at
        FROM bulk_email_campaigns 
        WHERE created_by = ? 
        ORDER BY created_at DESC
    ");
    $campaignsStmt->execute([$username]);
    $campaigns = $campaignsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $campaigns = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Email Campaigns - Portfolio Email System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1000px;
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

        .btn {
            background: #fd7e14;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 20px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #fd7e14;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .status-completed {
            color: #28a745;
            font-weight: bold;
        }

        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .no-campaigns {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📈 My Email Campaigns</h1>
            <p>Track and manage your bulk email campaigns</p>
        </div>

        <a href="user_bulk_email.php" class="btn">📧 Create New Campaign</a>
        <a href="user_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>

        <?php if (!empty($campaigns)): ?>
            <?php
            // Calculate stats
            $totalCampaigns = count($campaigns);
            $totalSent = array_sum(array_column($campaigns, 'sent_count'));
            $totalFailed = array_sum(array_column($campaigns, 'failed_count'));
            $totalRecipients = array_sum(array_column($campaigns, 'recipient_count'));
            ?>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalCampaigns; ?></div>
                    <div>Total Campaigns</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalRecipients; ?></div>
                    <div>Total Recipients</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalSent; ?></div>
                    <div>Emails Sent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php echo $totalSent > 0 ? round(($totalSent / ($totalSent + $totalFailed)) * 100, 1) : 0; ?>%
                    </div>
                    <div>Success Rate</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Recipients</th>
                        <th>Sent</th>
                        <th>Failed</th>
                        <th>Success Rate</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Completed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campaigns as $campaign): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($campaign['subject']); ?></strong>
                                <?php if ($campaign['attachment_name']): ?>
                                    <br><small>📎 <?php echo htmlspecialchars($campaign['attachment_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $campaign['recipient_count']; ?></td>
                            <td><?php echo $campaign['sent_count']; ?></td>
                            <td><?php echo $campaign['failed_count']; ?></td>
                            <td>
                                <?php
                                $total = $campaign['sent_count'] + $campaign['failed_count'];
                                $successRate = $total > 0 ? round(($campaign['sent_count'] / $total) * 100, 1) : 0;
                                echo $successRate . '%';
                                ?>
                            </td>
                            <td>
                                <?php if ($campaign['completed_at']): ?>
                                    <span class="status-completed">✅ Completed</span>
                                <?php else: ?>
                                    <span class="status-pending">⏳ Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M j, Y H:i', strtotime($campaign['created_at'])); ?></td>
                            <td>
                                <?php if ($campaign['completed_at']): ?>
                                    <?php echo date('M j, Y H:i', strtotime($campaign['completed_at'])); ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>
            <div class="no-campaigns">
                <h3>📧 No campaigns yet</h3>
                <p>You haven't created any email campaigns yet.</p>
                <a href="user_bulk_email.php" class="btn">Create Your First Campaign</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
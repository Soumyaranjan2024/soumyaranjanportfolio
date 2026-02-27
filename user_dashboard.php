<?php
require_once 'auth.php';
require_once 'database.php';

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Portfolio Email System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
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
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }

        .nav {
            margin-bottom: 20px;
        }

        .nav a {
            margin-right: 15px;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            background: #007bff;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .nav a:hover {
            background: #0056b3;
        }

        .nav a.reports {
            background: #28a745;
        }

        .nav a.reports:hover {
            background: #1e7e34;
        }

        .nav a.import {
            background: #17a2b8;
        }

        .nav a.import:hover {
            background: #138496;
        }

        .nav a.data {
            background: #6f42c1;
        }

        .nav a.data:hover {
            background: #5a32a3;
        }

        .nav a.bulk-email {
            background: #fd7e14;
        }

        .nav a.bulk-email:hover {
            background: #e8590c;
        }

        .user-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .feature-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .feature-card h4 {
            color: #007bff;
            margin-top: 0;
            margin-bottom: 15px;
        }

        .quick-actions {
            background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .quick-actions h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }

        .quick-actions p {
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .quick-actions .btn {
            background: white;
            color: #fd7e14;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 0 5px;
        }

        .quick-actions .btn:hover {
            background: #f8f9fa;
        }

        .recent-activity {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }

        .activity-item {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 14px;
        }

        .icon-email {
            background: #007bff;
            color: white;
        }

        .icon-report {
            background: #28a745;
            color: white;
        }

        .icon-import {
            background: #17a2b8;
            color: white;
        }

        .icon-bulk {
            background: #fd7e14;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📧 Portfolio Email System</h1>
            <div>
                Welcome,
                <strong><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></strong>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <span
                        style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; margin-left: 10px;">ADMIN</span>
                <?php endif; ?>
                |
                <a href="?logout=1" class="logout-btn">Logout</a>
            </div>
        </div>

        <div class="user-info">
            <strong>Account Information:</strong><br>
            Username: <?php echo htmlspecialchars($_SESSION['username']); ?><br>
            Role: <?php echo (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) ? 'Administrator' : 'User'; ?><br>
            Database: portfolio_db<br>
            Login Time: <?php echo date('Y-m-d H:i:s'); ?>
        </div>

        <?php
        // Get stats using your existing database structure
        try {
            $projectCount = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
            $messageCount = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();

            // Get user-specific stats
            $username = $_SESSION['username'];

            $userReports = $pdo->prepare("SELECT COUNT(*) FROM pdf_reports WHERE generated_by = ?");
            $userReports->execute([$username]);
            $userReportCount = $userReports->fetchColumn();

            $userImports = $pdo->prepare("SELECT COUNT(*) FROM excel_uploads WHERE uploaded_by = ?");
            $userImports->execute([$username]);
            $userImportCount = $userImports->fetchColumn();

            $totalImportedRecords = $pdo->prepare("SELECT COUNT(*) FROM imported_data WHERE uploaded_by = ?");
            $totalImportedRecords->execute([$username]);
            $importedRecordsCount = $totalImportedRecords->fetchColumn();

            $userCampaigns = $pdo->prepare("SELECT COUNT(*) FROM bulk_email_campaigns WHERE created_by = ?");
            $userCampaigns->execute([$username]);
            $campaignCount = $userCampaigns->fetchColumn();

        } catch (Exception $e) {
            $projectCount = $messageCount = $userReportCount = $userImportCount = $importedRecordsCount = $campaignCount = 0;
        }
        ?>

        <?php if ($importedRecordsCount > 0): ?>
            <div class="quick-actions">
                <h3>🚀 Ready to Send Bulk Emails!</h3>
                <p>You have <strong><?php echo $importedRecordsCount; ?></strong> imported contacts ready for bulk email
                    campaigns.</p>
                <a href="user_bulk_email.php" class="btn">📧 Send Bulk Email to Imported Contacts</a>
                <a href="user_imported_data.php" class="btn">👀 View Contacts</a>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $projectCount; ?></div>
                <div>Total Projects</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $messageCount; ?></div>
                <div>Total Messages</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $userReportCount; ?></div>
                <div>My Reports</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $userImportCount; ?></div>
                <div>My Imports</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $importedRecordsCount; ?></div>
                <div>Imported Contacts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $campaignCount; ?></div>
                <div>Email Campaigns</div>
            </div>
        </div>

        <div class="nav">
            <a href="send_email.php">📤 Send Single Email</a>
            <a href="user_bulk_email.php" class="bulk-email">📧 Bulk Email Campaign</a>
            <a href="user_reports.php" class="reports">📊 Generate Reports</a>
            <a href="user_excel_import.php" class="import">📥 Import Excel Data</a>
            <a href="user_imported_data.php" class="data">📋 View Imported Data</a>
            <a href="user_campaigns.php" class="data">📈 My Campaigns</a>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <a href="admin.php">👥 Admin Panel</a>
                <a href="gmail_setup_guide.html">⚙️ Email Setup Guide</a>
            <?php endif; ?>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <h4>📤 Email Features</h4>
                <ul>
                    <li>Send single emails with attachments</li>
                    <li>Bulk email campaigns to imported contacts</li>
                    <li>Email tracking and statistics</li>
                    <li>HTML email templates</li>
                    <li>Gmail/SMTP integration</li>
                    <li>Campaign management</li>
                </ul>
            </div>

            <div class="feature-card">
                <h4>📊 Report Generation</h4>
                <ul>
                    <li>PDF reports from your data</li>
                    <li>Projects, messages, users reports</li>
                    <li>Email campaign reports</li>
                    <li>Date range filtering</li>
                    <li>Professional formatting</li>
                    <li>Download and share</li>
                </ul>
            </div>

            <div class="feature-card">
                <h4>📥 Data Management</h4>
                <ul>
                    <li>Import Excel/CSV files</li>
                    <li>Automatic data validation</li>
                    <li>View and manage contacts</li>
                    <li>Search and filter data</li>
                    <li>Export capabilities</li>
                    <li>Bulk email integration</li>
                </ul>
            </div>

            <div class="feature-card">
                <h4>🚀 Bulk Email Campaigns</h4>
                <ul>
                    <li>Send to all imported contacts</li>
                    <li>Custom email templates</li>
                    <li>Real-time sending progress</li>
                    <li>Success/failure tracking</li>
                    <li>Campaign statistics</li>
                    <li>Resend failed emails</li>
                </ul>
            </div>
        </div>

        <div class="recent-activity">
            <h3>📈 Recent Activity</h3>
            <?php
            try {
                $activities = [];
                $username = $_SESSION['username'];

                // Get recent reports
                $recentReports = $pdo->prepare("
                    SELECT 'report' as type, report_type, generated_at as created_at, filename
                    FROM pdf_reports 
                    WHERE generated_by = ? 
                    ORDER BY generated_at DESC 
                    LIMIT 2
                ");
                $recentReports->execute([$username]);
                $activities = array_merge($activities, $recentReports->fetchAll(PDO::FETCH_ASSOC));

                // Get recent imports
                $recentImports = $pdo->prepare("
                    SELECT 'import' as type, original_name as filename, upload_date as created_at, rows_imported
                    FROM excel_uploads 
                    WHERE uploaded_by = ? 
                    ORDER BY upload_date DESC 
                    LIMIT 2
                ");
                $recentImports->execute([$username]);
                $activities = array_merge($activities, $recentImports->fetchAll(PDO::FETCH_ASSOC));

                // Get recent campaigns
                $recentCampaigns = $pdo->prepare("
                    SELECT 'campaign' as type, subject as filename, created_at, recipient_count
                    FROM bulk_email_campaigns 
                    WHERE created_by = ? 
                    ORDER BY created_at DESC 
                    LIMIT 2
                ");
                $recentCampaigns->execute([$username]);
                $activities = array_merge($activities, $recentCampaigns->fetchAll(PDO::FETCH_ASSOC));

                // Sort by date
                usort($activities, function ($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });

                $activities = array_slice($activities, 0, 5);

                if (empty($activities)) {
                    echo '<div class="activity-item">
                            <div>
                                <span class="activity-icon icon-email">📧</span>
                                <span>Welcome! Start by importing contacts or sending emails.</span>
                            </div>
                            <small>' . date('Y-m-d H:i') . '</small>
                          </div>';
                } else {
                    foreach ($activities as $activity) {
                        $icon = 'icon-email';
                        $iconText = '📧';
                        $text = '';

                        switch ($activity['type']) {
                            case 'report':
                                $icon = 'icon-report';
                                $iconText = '📊';
                                $text = 'Generated ' . ucfirst($activity['report_type']) . ' report';
                                break;
                            case 'import':
                                $icon = 'icon-import';
                                $iconText = '📥';
                                $text = 'Imported ' . $activity['filename'] . ' (' . $activity['rows_imported'] . ' records)';
                                break;
                            case 'campaign':
                                $icon = 'icon-bulk';
                                $iconText = '📧';
                                $text = 'Sent bulk email: ' . $activity['filename'] . ' (' . $activity['recipient_count'] . ' recipients)';
                                break;
                        }

                        echo '<div class="activity-item">
                                <div>
                                    <span class="activity-icon ' . $icon . '">' . $iconText . '</span>
                                    <span>' . htmlspecialchars($text) . '</span>
                                </div>
                                <small>' . date('M j, Y H:i', strtotime($activity['created_at'])) . '</small>
                              </div>';
                    }
                }
            } catch (Exception $e) {
                echo '<div class="activity-item">
                        <div>
                            <span class="activity-icon icon-email">📧</span>
                            <span>Welcome to your enhanced dashboard!</span>
                        </div>
                        <small>' . date('Y-m-d H:i') . '</small>
                      </div>';
            }
            ?>
        </div>
    </div>
</body>

</html>
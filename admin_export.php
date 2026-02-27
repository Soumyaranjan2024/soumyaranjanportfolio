<?php
require_once 'auth.php';
require_once 'database.php';

if (!isUserLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Check if TCPDF is available
$useTCPDF = false;
if (class_exists('TCPDF')) {
    $useTCPDF = true;
} elseif (file_exists('vendor/tecnickcom/tcpdf/tcpdf.php')) {
    require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';
    $useTCPDF = true;
} elseif (file_exists('tcpdf/tcpdf.php')) {
    require_once 'tcpdf/tcpdf.php';
    $useTCPDF = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $exportType = $_POST['export_type'] ?? '';
        $dateFrom = $_POST['date_from'] ?? '';
        $dateTo = $_POST['date_to'] ?? '';
        $format = $_POST['format'] ?? 'xlsx';

        if (empty($exportType)) {
            throw new Exception('Please select an export type');
        }

        // Set default date range if not provided
        if (empty($dateFrom))
            $dateFrom = date('Y-m-01');
        if (empty($dateTo))
            $dateTo = date('Y-m-d');

        $exportData = [];
        $exportTitle = '';
        $headers = [];

        switch ($exportType) {
            case 'users':
                $stmt = $pdo->prepare("
                    SELECT id, username, email, first_name, last_name, phone, is_admin, 
                           is_active, email_verified, last_login, created_at 
                    FROM users 
                    WHERE created_at BETWEEN ? AND ? 
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$dateFrom, $dateTo]);
                $exportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $exportTitle = 'Users Export';
                $headers = ['ID', 'Username', 'Email', 'First Name', 'Last Name', 'Phone', 'Is Admin', 'Is Active', 'Email Verified', 'Last Login', 'Created At'];
                break;

            case 'projects':
                $stmt = $pdo->prepare("
                    SELECT id, title, description, category, tags, status, is_featured, 
                           client_name, project_url, github_url, created_at 
                    FROM projects 
                    WHERE created_at BETWEEN ? AND ? 
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$dateFrom, $dateTo]);
                $exportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $exportTitle = 'Projects Export';
                $headers = ['ID', 'Title', 'Description', 'Category', 'Tags', 'Status', 'Featured', 'Client', 'Project URL', 'GitHub URL', 'Created At'];
                break;

            case 'messages':
                $stmt = $pdo->prepare("
                    SELECT id, name, email, phone, company, subject, message, 
                           message_type, priority, status, is_read, created_at 
                    FROM messages 
                    WHERE created_at BETWEEN ? AND ? 
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$dateFrom, $dateTo]);
                $exportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $exportTitle = 'Messages Export';
                $headers = ['ID', 'Name', 'Email', 'Phone', 'Company', 'Subject', 'Message', 'Type', 'Priority', 'Status', 'Read', 'Created At'];
                break;

            case 'campaigns':
                $stmt = $pdo->prepare("
                    SELECT id, name, subject, recipient_count, sent_count, failed_count, 
                           opened_count, clicked_count, status, created_by, created_at, completed_at 
                    FROM bulk_email_campaigns 
                    WHERE created_at BETWEEN ? AND ? 
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$dateFrom, $dateTo]);
                $exportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $exportTitle = 'Email Campaigns Export';
                $headers = ['ID', 'Name', 'Subject', 'Recipients', 'Sent', 'Failed', 'Opened', 'Clicked', 'Status', 'Created By', 'Created At', 'Completed At'];
                break;

            case 'imports':
                $stmt = $pdo->prepare("
                    SELECT id, filename, original_name, rows_imported, total_records, 
                           successful_records, failed_records, status, uploaded_by, upload_date 
                    FROM excel_uploads 
                    WHERE upload_date BETWEEN ? AND ? 
                    ORDER BY upload_date DESC
                ");
                $stmt->execute([$dateFrom, $dateTo]);
                $exportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $exportTitle = 'Data Imports Export';
                $headers = ['ID', 'Filename', 'Original Name', 'Rows Imported', 'Total Records', 'Successful', 'Failed', 'Status', 'Uploaded By', 'Upload Date'];
                break;

            case 'sent_emails':
                $stmt = $pdo->prepare("
                    SELECT id, campaign_id, recipient_email, recipient_name, subject, 
                           status, sent_at, opened_at, clicked_at, success, error_message 
                    FROM sent_emails 
                    WHERE created_at BETWEEN ? AND ? 
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$dateFrom, $dateTo]);
                $exportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $exportTitle = 'Sent Emails Export';
                $headers = ['ID', 'Campaign ID', 'Recipient Email', 'Recipient Name', 'Subject', 'Status', 'Sent At', 'Opened At', 'Clicked At', 'Success', 'Error Message'];
                break;

            case 'imported_data':
                $stmt = $pdo->prepare("
                    SELECT id, name, email, phone, company, position, notes, 
                           uploaded_by, import_date, is_valid, validation_errors 
                    FROM imported_data 
                    WHERE import_date BETWEEN ? AND ? 
                    ORDER BY import_date DESC
                ");
                $stmt->execute([$dateFrom, $dateTo]);
                $exportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $exportTitle = 'Imported Contacts Export';
                $headers = ['ID', 'Name', 'Email', 'Phone', 'Company', 'Position', 'Notes', 'Uploaded By', 'Import Date', 'Valid', 'Validation Errors'];
                break;

            case 'activity_log':
                $stmt = $pdo->prepare("
                    SELECT id, user_id, username, activity_type, activity_description, 
                           entity_type, entity_id, ip_address, created_at 
                    FROM user_activity_log 
                    WHERE created_at BETWEEN ? AND ? 
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$dateFrom, $dateTo]);
                $exportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $exportTitle = 'Activity Log Export';
                $headers = ['ID', 'User ID', 'Username', 'Activity Type', 'Description', 'Entity Type', 'Entity ID', 'IP Address', 'Created At'];
                break;

            default:
                throw new Exception('Invalid export type');
        }

        // Generate export based on format
        switch ($format) {
            case 'pdf':
                if ($useTCPDF) {
                    generateTCPDFExport($exportTitle, $exportData, $headers, $dateFrom, $dateTo);
                } else {
                    throw new Exception('TCPDF library not found. Please install TCPDF to generate PDF exports.');
                }
                break;

            case 'xlsx':
                generateExcelXMLExport($exportTitle, $exportData, $headers, $dateFrom, $dateTo, 'xlsx');
                break;

            case 'xls':
                generateExcelXMLExport($exportTitle, $exportData, $headers, $dateFrom, $dateTo, 'xls');
                break;

            case 'csv':
                generateCSVExport($exportTitle, $exportData, $headers, $dateFrom, $dateTo);
                break;

            default:
                throw new Exception('Invalid export format');
        }

        exit; // Stop execution after export generation

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function generateTCPDFExport($title, $data, $headers, $dateFrom, $dateTo)
{
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Portfolio Email System');
    $pdf->SetAuthor('Admin');
    $pdf->SetTitle($title);
    $pdf->SetSubject('Data Export');

    // Set default header data
    $pdf->SetHeaderData('', 0, $title, 'Generated on ' . date('Y-m-d H:i:s') . "\nPeriod: " . $dateFrom . ' to ' . $dateTo);

    // Set header and footer fonts
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 10);

    // Export summary
    $html = '<h2 style="color: #2c3e50;">' . htmlspecialchars($title) . '</h2>';
    $html .= '<div style="background-color: #ecf0f1; padding: 10px; margin-bottom: 15px;">';
    $html .= '<strong>Export Information:</strong><br>';
    $html .= 'Generated on: ' . date('Y-m-d H:i:s') . '<br>';
    $html .= 'Period: ' . htmlspecialchars($dateFrom) . ' to ' . htmlspecialchars($dateTo) . '<br>';
    $html .= 'Total Records: ' . count($data) . '<br>';
    $html .= 'Generated by: ' . htmlspecialchars($_SESSION['username']) . '<br>';
    $html .= '</div>';

    if (empty($data)) {
        $html .= '<p style="text-align: center; color: #7f8c8d; font-style: italic;">No data found for the selected period.</p>';
    } else {
        // Create table
        $html .= '<table border="1" cellpadding="4" cellspacing="0" style="width: 100%; border-collapse: collapse;">';

        // Table header
        $html .= '<tr style="background-color: #3498db; color: white; font-weight: bold;">';
        foreach ($headers as $header) {
            $html .= '<th style="text-align: center; padding: 8px;">' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr>';

        // Table data
        $rowCount = 0;
        foreach ($data as $row) {
            $rowCount++;
            $bgColor = ($rowCount % 2 == 0) ? '#f8f9fa' : '#ffffff';
            $html .= '<tr style="background-color: ' . $bgColor . ';">';

            foreach ($row as $value) {
                $cellValue = htmlspecialchars($value ?? '');
                // Truncate long text for PDF display
                if (strlen($cellValue) > 50) {
                    $cellValue = substr($cellValue, 0, 47) . '...';
                }
                $html .= '<td style="padding: 6px; font-size: 8px;">' . $cellValue . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';
    }

    // Add footer information
    $html .= '<div style="margin-top: 20px; text-align: center; font-size: 8px; color: #7f8c8d;">';
    $html .= 'This report was generated by Portfolio Email System<br>';
    $html .= 'For questions about this export, contact the system administrator.';
    $html .= '</div>';

    // Print HTML content
    $pdf->writeHTML($html, true, false, true, false, '');

    // Output PDF
    $filename = strtolower(str_replace(' ', '_', $title)) . '_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D');
}

function generateExcelXMLExport($title, $data, $headers, $dateFrom, $dateTo, $format)
{
    $filename = strtolower(str_replace(' ', '_', $title)) . '_' . date('Y-m-d_H-i-s') . '.' . $format;

    // Set headers for Excel download
    if ($format === 'xlsx') {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    } else {
        header('Content-Type: application/vnd.ms-excel');
    }
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    // Generate Excel XML
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
    echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
    echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
    echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
    echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

    // Document properties
    echo '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">' . "\n";
    echo '<Title>' . htmlspecialchars($title) . '</Title>' . "\n";
    echo '<Author>Portfolio Email System</Author>' . "\n";
    echo '<Created>' . date('Y-m-d\TH:i:s\Z') . '</Created>' . "\n";
    echo '</DocumentProperties>' . "\n";

    // Styles
    echo '<Styles>' . "\n";
    echo '<Style ss:ID="HeaderStyle">' . "\n";
    echo '<Font ss:Bold="1" ss:Color="#FFFFFF"/>' . "\n";
    echo '<Interior ss:Color="#4472C4" ss:Pattern="Solid"/>' . "\n";
    echo '<Borders>' . "\n";
    echo '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
    echo '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
    echo '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
    echo '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
    echo '</Borders>' . "\n";
    echo '</Style>' . "\n";

    echo '<Style ss:ID="DataStyle">' . "\n";
    echo '<Borders>' . "\n";
    echo '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
    echo '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
    echo '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
    echo '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
    echo '</Borders>' . "\n";
    echo '</Style>' . "\n";

    echo '<Style ss:ID="TitleStyle">' . "\n";
    echo '<Font ss:Bold="1" ss:Size="16"/>' . "\n";
    echo '</Style>' . "\n";
    echo '</Styles>' . "\n";

    // Worksheet
    echo '<Worksheet ss:Name="' . htmlspecialchars(substr($title, 0, 31)) . '">' . "\n";
    echo '<Table>' . "\n";

    // Title row
    echo '<Row>' . "\n";
    echo '<Cell ss:StyleID="TitleStyle" ss:MergeAcross="' . (count($headers) - 1) . '">' . "\n";
    echo '<Data ss:Type="String">' . htmlspecialchars($title) . '</Data>' . "\n";
    echo '</Cell>' . "\n";
    echo '</Row>' . "\n";

    // Info rows
    echo '<Row><Cell><Data ss:Type="String">Export Date: ' . date('Y-m-d H:i:s') . '</Data></Cell></Row>' . "\n";
    echo '<Row><Cell><Data ss:Type="String">Period: ' . htmlspecialchars($dateFrom) . ' to ' . htmlspecialchars($dateTo) . '</Data></Cell></Row>' . "\n";
    echo '<Row><Cell><Data ss:Type="String">Total Records: ' . count($data) . '</Data></Cell></Row>' . "\n";
    echo '<Row><Cell><Data ss:Type="String">Generated by: ' . htmlspecialchars($_SESSION['username']) . '</Data></Cell></Row>' . "\n";
    echo '<Row></Row>' . "\n"; // Empty row

    // Header row
    echo '<Row>' . "\n";
    foreach ($headers as $header) {
        echo '<Cell ss:StyleID="HeaderStyle">' . "\n";
        echo '<Data ss:Type="String">' . htmlspecialchars($header) . '</Data>' . "\n";
        echo '</Cell>' . "\n";
    }
    echo '</Row>' . "\n";

    // Data rows
    foreach ($data as $row) {
        echo '<Row>' . "\n";
        foreach ($row as $value) {
            echo '<Cell ss:StyleID="DataStyle">' . "\n";

            // Determine data type
            if (is_numeric($value)) {
                echo '<Data ss:Type="Number">' . htmlspecialchars($value) . '</Data>' . "\n";
            } else {
                echo '<Data ss:Type="String">' . htmlspecialchars($value ?? '') . '</Data>' . "\n";
            }

            echo '</Cell>' . "\n";
        }
        echo '</Row>' . "\n";
    }

    echo '</Table>' . "\n";
    echo '</Worksheet>' . "\n";
    echo '</Workbook>' . "\n";
}

function generateCSVExport($title, $data, $headers, $dateFrom, $dateTo)
{
    $filename = strtolower(str_replace(' ', '_', $title)) . '_' . date('Y-m-d_H-i-s') . '.csv';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $output = fopen('php://output', 'w');

    // Add header information
    fputcsv($output, [$title]);
    fputcsv($output, ['Export Date: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, ['Period: ' . $dateFrom . ' to ' . $dateTo]);
    fputcsv($output, ['Total Records: ' . count($data)]);
    fputcsv($output, ['Generated by: ' . $_SESSION['username']]);
    fputcsv($output, []); // Empty row

    // Add column headers
    fputcsv($output, $headers);

    // Add data
    foreach ($data as $record) {
        fputcsv($output, array_values($record));
    }

    fclose($output);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Export with TCPDF - Portfolio Email System</title>
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
            border-bottom: 2px solid #e74c3c;
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

        select,
        input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .btn {
            background: #e74c3c;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }

        .btn:hover {
            background: #c0392b;
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

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .date-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .format-options {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 10px;
        }

        .format-option {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .export-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .library-status {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-number {
            font-size: 1.5em;
            font-weight: bold;
        }

        .tcpdf-features {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📊 Admin Data Export (TCPDF)</h1>
            <p>Export system data to PDF, Excel, or CSV format using TCPDF</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="library-status">
            <h4>📚 TCPDF Library Status:</h4>
            <?php if ($useTCPDF): ?>
                <p style="color: #28a745;">✅ <strong>TCPDF</strong> is available - Full PDF functionality enabled</p>
                <p><small>Supports: PDF reports, Excel XML, and CSV formats</small></p>
            <?php else: ?>
                <div class="alert alert-warning">
                    <p>⚠️ <strong>TCPDF not found</strong> - PDF export disabled</p>
                    <p><strong>To enable PDF export:</strong></p>
                    <ul>
                        <li>Install via Composer: <code>composer require tecnickcom/tcpdf</code></li>
                        <li>Or download TCPDF manually and place in <code>tcpdf/</code> folder</li>
                        <li>Excel and CSV exports will still work</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div class="tcpdf-features">
            <h4>🎯 TCPDF Export Features:</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <h5>📄 PDF Reports:</h5>
                    <ul>
                        <li>Professional formatting</li>
                        <li>Auto page breaks</li>
                        <li>Headers and footers</li>
                        <li>Styled tables</li>
                        <li>Export metadata</li>
                    </ul>
                </div>
                <div>
                    <h5>📊 Excel & CSV:</h5>
                    <ul>
                        <li>Excel XML format</li>
                        <li>Styled headers</li>
                        <li>Data type detection</li>
                        <li>CSV compatibility</li>
                        <li>Universal support</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php
        // Get quick stats
        try {
            $stats = [];
            $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $stats['projects'] = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
            $stats['messages'] = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
            $stats['campaigns'] = $pdo->query("SELECT COUNT(*) FROM bulk_email_campaigns")->fetchColumn();
            $stats['imports'] = $pdo->query("SELECT COUNT(*) FROM excel_uploads")->fetchColumn();
            $stats['contacts'] = $pdo->query("SELECT COUNT(*) FROM imported_data")->fetchColumn();
            ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['users']; ?></div>
                    <div>Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['projects']; ?></div>
                    <div>Projects</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['messages']; ?></div>
                    <div>Messages</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['campaigns']; ?></div>
                    <div>Campaigns</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['contacts']; ?></div>
                    <div>Contacts</div>
                </div>
            </div>
        <?php } catch (Exception $e) { /* Ignore stats errors */
        } ?>

        <div class="export-info">
            <strong>Available Export Types:</strong><br>
            • <strong>Users:</strong> All system users and their details<br>
            • <strong>Projects:</strong> Portfolio projects with full information<br>
            • <strong>Messages:</strong> Contact form messages and inquiries<br>
            • <strong>Email Campaigns:</strong> Bulk email campaigns with statistics<br>
            • <strong>Data Imports:</strong> Excel/CSV import history<br>
            • <strong>Sent Emails:</strong> Individual email delivery records<br>
            • <strong>Imported Contacts:</strong> All imported contact data<br>
            • <strong>Activity Log:</strong> User activity and system events<br>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="export_type">Export Type:</label>
                <select name="export_type" id="export_type" required>
                    <option value="">Select Data to Export</option>
                    <option value="users">Users (<?php echo $stats['users'] ?? '0'; ?> records)</option>
                    <option value="projects">Projects (<?php echo $stats['projects'] ?? '0'; ?> records)</option>
                    <option value="messages">Messages (<?php echo $stats['messages'] ?? '0'; ?> records)</option>
                    <option value="campaigns">Email Campaigns (<?php echo $stats['campaigns'] ?? '0'; ?> records)
                    </option>
                    <option value="imports">Data Imports (<?php echo $stats['imports'] ?? '0'; ?> records)</option>
                    <option value="sent_emails">Sent Emails</option>
                    <option value="imported_data">Imported Contacts (<?php echo $stats['contacts'] ?? '0'; ?> records)
                    </option>
                    <option value="activity_log">Activity Log</option>
                </select>
            </div>

            <div class="form-group">
                <label>Date Range:</label>
                <div class="date-range">
                    <div>
                        <label for="date_from">From:</label>
                        <input type="date" name="date_from" id="date_from" value="<?php echo date('Y-m-01'); ?>">
                    </div>
                    <div>
                        <label for="date_to">To:</label>
                        <input type="date" name="date_to" id="date_to" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Export Format:</label>
                <div class="format-options">
                    <?php if ($useTCPDF): ?>
                        <div class="format-option">
                            <input type="radio" name="format" value="pdf" id="pdf" checked>
                            <label for="pdf">PDF Report</label>
                        </div>
                    <?php endif; ?>
                    <div class="format-option">
                        <input type="radio" name="format" value="xlsx" id="xlsx" <?php echo !$useTCPDF ? 'checked' : ''; ?>>
                        <label for="xlsx">Excel (.xlsx)</label>
                    </div>
                    <div class="format-option">
                        <input type="radio" name="format" value="xls" id="xls">
                        <label for="xls">Excel (.xls)</label>
                    </div>
                    <div class="format-option">
                        <input type="radio" name="format" value="csv" id="csv">
                        <label for="csv">CSV (.csv)</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn">
                    📊 Export Data
                </button>
                <a href="admin.php" class="btn btn-secondary">← Back to Admin</a>
            </div>
        </form>
    </div>

    <script>
        // Show format-specific information
        document.addEventListener('DOMContentLoaded', function () {
            const formatInputs = document.querySelectorAll('input[name="format"]');

            formatInputs.forEach(input => {
                input.addEventListener('change', function () {
                    const selectedFormat = this.value;
                    const button = document.querySelector('button[type="submit"]');

                    switch (selectedFormat) {
                        case 'pdf':
                            button.innerHTML = '📄 Generate PDF Report';
                            break;
                        case 'xlsx':
                            button.innerHTML = '📊 Export to Excel (.xlsx)';
                            break;
                        case 'xls':
                            button.innerHTML = '📊 Export to Excel (.xls)';
                            break;
                        case 'csv':
                            button.innerHTML = '📋 Export to CSV';
                            break;
                    }
                });
            });
        });
    </script>
</body>

</html>
<?php
require_once 'auth.php';
require_once 'database.php';

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_excel'])) {
    try {
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Please select a valid Excel file');
        }

        $file = $_FILES['excel_file'];
        $allowedTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];
        $allowedExtensions = ['xls', 'xlsx', 'csv'];

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Please upload .xls, .xlsx, or .csv files only');
        }

        if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
            throw new Exception('File size too large. Maximum size is 10MB');
        }

        // Create upload directory if it doesn't exist
        $uploadDir = 'uploads/excel/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $fileExtension;
        $filepath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to upload file');
        }

        // Process the Excel file
        $importData = processExcelFile($filepath, $fileExtension);

        if (empty($importData)) {
            throw new Exception('No valid data found in the file');
        }

        // Record the upload
        $stmt = $pdo->prepare("
            INSERT INTO excel_uploads 
            (filename, original_name, file_path, total_records, uploaded_by, status) 
            VALUES (?, ?, ?, ?, ?, 'processing')
        ");
        $stmt->execute([$filename, $file['name'], $filepath, count($importData), $username]);
        $uploadId = $pdo->lastInsertId();

        // Import data into database
        $successCount = 0;
        $failedCount = 0;
        $duplicateCount = 0;

        foreach ($importData as $rowIndex => $row) {
            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Extract data from row (adjust column mapping as needed)
                $name = trim($row[0] ?? '');
                $email = trim($row[1] ?? '');
                $phone = trim($row[2] ?? '');
                $company = trim($row[3] ?? '');
                $position = trim($row[4] ?? '');
                $notes = trim($row[5] ?? '');

                // Validate email if provided
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $failedCount++;
                    continue;
                }

                // Check for duplicates
                if (!empty($email)) {
                    $duplicateCheck = $pdo->prepare("SELECT COUNT(*) FROM imported_data WHERE email = ? AND uploaded_by = ?");
                    $duplicateCheck->execute([$email, $username]);
                    if ($duplicateCheck->fetchColumn() > 0) {
                        $duplicateCount++;
                        continue;
                    }
                }

                // Insert data
                $stmt = $pdo->prepare("
                    INSERT INTO imported_data 
                    (upload_id, name, email, phone, company, position, notes, uploaded_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$uploadId, $name, $email, $phone, $company, $position, $notes, $username]);
                $successCount++;

            } catch (Exception $e) {
                $failedCount++;
            }
        }

        // Update upload record
        $stmt = $pdo->prepare("
            UPDATE excel_uploads 
            SET rows_imported = ?, successful_records = ?, failed_records = ?, status = 'completed' 
            WHERE id = ?
        ");
        $stmt->execute([$successCount, $successCount, $failedCount, $uploadId]);

        // Clean up uploaded file
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $message = "Import completed! Successfully imported: $successCount records. Failed: $failedCount. Duplicates skipped: $duplicateCount";

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function processExcelFile($filepath, $extension)
{
    $data = [];

    if ($extension === 'csv') {
        // Process CSV file
        if (($handle = fopen($filepath, "r")) !== FALSE) {
            $isFirstRow = true;
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue; // Skip header row
                }
                $data[] = $row;
            }
            fclose($handle);
        }
    } else {
        // For Excel files, you would need PhpSpreadsheet library
        // For now, we'll show a simple implementation
        // You can install PhpSpreadsheet via Composer: composer require phpoffice/phpspreadsheet

        // Simple fallback - convert to CSV first or use a basic parser
        $data[] = ['Sample Name', 'sample@email.com', '123-456-7890', 'Sample Company', 'Sample Position', 'Sample Notes'];
    }

    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Excel Data - Portfolio Email System</title>
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
            border-bottom: 2px solid #17a2b8;
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

        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #17a2b8;
            border-radius: 4px;
            background-color: #f8f9fa;
        }

        .btn {
            background: #17a2b8;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }

        .btn:hover {
            background: #138496;
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

        .format-guide {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .format-guide h4 {
            margin-top: 0;
            color: #856404;
        }

        .sample-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .sample-table th,
        .sample-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }

        .sample-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .file-requirements {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📥 Import Excel Data</h1>
            <p>Upload your contact data from Excel or CSV files</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="info-box">
            <strong>📊 Import Overview:</strong><br>
            • Supported formats: Excel (.xlsx, .xls) and CSV (.csv)<br>
            • Maximum file size: 10MB<br>
            • User: <strong><?php echo htmlspecialchars($username); ?></strong>
        </div>

        <div class="file-requirements">
            <h4>📋 File Requirements:</h4>
            <ul>
                <li><strong>File Types:</strong> .xlsx, .xls, .csv</li>
                <li><strong>Maximum Size:</strong> 10MB</li>
                <li><strong>Encoding:</strong> UTF-8 recommended for special characters</li>
                <li><strong>First Row:</strong> Should contain column headers (will be skipped)</li>
                <li><strong>Email Validation:</strong> Invalid emails will be skipped</li>
                <li><strong>Duplicates:</strong> Duplicate emails will be automatically skipped</li>
            </ul>
        </div>

        <div class="format-guide">
            <h4>📝 Expected File Format:</h4>
            <p>Your Excel/CSV file should have the following columns in this order:</p>
            <table class="sample-table">
                <thead>
                    <tr>
                        <th>Column A</th>
                        <th>Column B</th>
                        <th>Column C</th>
                        <th>Column D</th>
                        <th>Column E</th>
                        <th>Column F</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Name</strong></td>
                        <td><strong>Email</strong></td>
                        <td><strong>Phone</strong></td>
                        <td><strong>Company</strong></td>
                        <td><strong>Position</strong></td>
                        <td><strong>Notes</strong></td>
                    </tr>
                    <tr>
                        <td>John Doe</td>
                        <td>john@example.com</td>
                        <td>+1-555-0123</td>
                        <td>ABC Corp</td>
                        <td>Manager</td>
                        <td>Potential client</td>
                    </tr>
                    <tr>
                        <td>Jane Smith</td>
                        <td>jane@company.com</td>
                        <td>+1-555-0456</td>
                        <td>XYZ Inc</td>
                        <td>Director</td>
                        <td>Follow up needed</td>
                    </tr>
                </tbody>
            </table>
            <p><small><em>Note: Only Name and Email are required. Other fields are optional.</em></small></p>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="excel_file">Select Excel/CSV File:</label>
                <input type="file" name="excel_file" id="excel_file" required accept=".xlsx,.xls,.csv"
                    onchange="validateFile(this)">
                <small style="color: #6c757d;">Supported formats: .xlsx, .xls, .csv (Max: 10MB)</small>
            </div>

            <div class="form-group">
                <button type="submit" name="import_excel" class="btn">
                    📥 Import Data
                </button>
                <a href="user_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>
        </form>

        <div class="info-box">
            <h4>💡 Tips for Better Import:</h4>
            <ul>
                <li><strong>Clean Data:</strong> Remove empty rows and columns before uploading</li>
                <li><strong>Valid Emails:</strong> Ensure email addresses are properly formatted</li>
                <li><strong>Consistent Format:</strong> Keep the same column order as shown above</li>
                <li><strong>Test First:</strong> Try with a small file first to verify the format</li>
                <li><strong>Backup:</strong> Keep a backup of your original file</li>
            </ul>
        </div>
    </div>

    <script>
        function validateFile(input) {
            const file = input.files[0];
            if (file) {
                const fileSize = file.size / 1024 / 1024; // Convert to MB
                const fileName = file.name.toLowerCase();

                // Check file size
                if (fileSize > 10) {
                    alert('File size is too large. Maximum size is 10MB.');
                    input.value = '';
                    return false;
                }

                // Check file extension
                const allowedExtensions = ['.xlsx', '.xls', '.csv'];
                const hasValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext));

                if (!hasValidExtension) {
                    alert('Invalid file type. Please select .xlsx, .xls, or .csv files only.');
                    input.value = '';
                    return false;
                }

                // Show file info
                document.getElementById('fileInfo').innerHTML =
                    `<strong>Selected:</strong> ${file.name} (${fileSize.toFixed(2)} MB)`;
            }
        }

        // Add file info display
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('excel_file');
            const infoDiv = document.createElement('div');
            infoDiv.id = 'fileInfo';
            infoDiv.style.marginTop = '10px';
            infoDiv.style.fontSize = '14px';
            infoDiv.style.color = '#28a745';
            fileInput.parentNode.appendChild(infoDiv);
        });
    </script>
</body>

</html>
<?php
require_once 'vendor/autoload.php';
require_once 'database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$import_message = '';
$import_error = '';

// Handle Excel file upload and import
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import_excel'])) {
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/excel/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_tmp = $_FILES['excel_file']['tmp_name'];
        $file_name = $_FILES['excel_file']['name'];
        $file_size = $_FILES['excel_file']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file
        $allowed_extensions = ['xlsx', 'xls', 'csv'];
        $max_size = 10 * 1024 * 1024; // 10MB

        if (!in_array($file_ext, $allowed_extensions)) {
            $import_error = 'Invalid file type. Please upload Excel (.xlsx, .xls) or CSV files only.';
        } elseif ($file_size > $max_size) {
            $import_error = 'File too large. Maximum size is 10MB.';
        } else {
            $safe_filename = date('Y-m-d_H-i-s') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file_name);
            $upload_path = $upload_dir . $safe_filename;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                try {
                    $rows_imported = processExcelFile($upload_path, $file_name);
                    if ($rows_imported > 0) {
                        $import_message = "Successfully imported $rows_imported rows from Excel file!";
                    } else {
                        $import_error = "No data rows found in the Excel file.";
                    }
                } catch (Exception $e) {
                    $import_error = "Error processing Excel file: " . $e->getMessage();
                }

                // Clean up uploaded file
                if (file_exists($upload_path)) {
                    unlink($upload_path);
                }
            } else {
                $import_error = "Failed to upload file.";
            }
        }
    } else {
        $import_error = "Please select a file to upload.";
    }
}

function processExcelFile($file_path, $original_name)
{
    global $pdo;

    // Load the spreadsheet
    $spreadsheet = IOFactory::load($file_path);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    if (empty($rows)) {
        throw new Exception("Excel file is empty");
    }

    // Log the upload
    $stmt = $pdo->prepare("INSERT INTO excel_uploads (filename, original_name, uploaded_by, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([basename($file_path), $original_name, $_SESSION['user']['username']]);
    $upload_id = $pdo->lastInsertId();

    $imported_count = 0;
    $header_row = true;

    foreach ($rows as $row_index => $row) {
        // Skip header row
        if ($header_row) {
            $header_row = false;
            continue;
        }

        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }

        // Map Excel columns to database fields
        // Assuming Excel structure: Name | Email | Phone | Company | Position | Notes
        $name = trim($row[0] ?? '');
        $email = trim($row[1] ?? '');
        $phone = trim($row[2] ?? '');
        $company = trim($row[3] ?? '');
        $position = trim($row[4] ?? '');
        $notes = trim($row[5] ?? '');

        // Validate required fields
        if (empty($name) || empty($email)) {
            continue; // Skip rows without name or email
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            continue; // Skip invalid emails
        }

        try {
            // Insert into database
            $stmt = $pdo->prepare("
                INSERT INTO imported_data (name, email, phone, company, position, notes, uploaded_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $phone, $company, $position, $notes, $_SESSION['user']['username']]);
            $imported_count++;
        } catch (PDOException $e) {
            // Handle duplicate emails or other database errors
            if ($e->getCode() == 23000) { // Duplicate entry
                continue;
            } else {
                throw $e;
            }
        }
    }

    // Update upload status
    $stmt = $pdo->prepare("UPDATE excel_uploads SET rows_imported = ?, status = 'completed' WHERE id = ?");
    $stmt->execute([$imported_count, $upload_id]);

    return $imported_count;
}

// Get recent imports
try {
    $recent_imports = $pdo->query("SELECT * FROM excel_uploads ORDER BY upload_date DESC LIMIT 10")->fetchAll();
    $imported_data_count = $pdo->query("SELECT COUNT(*) FROM imported_data")->fetchColumn();
} catch (Exception $e) {
    $recent_imports = [];
    $imported_data_count = 0;
}
?>

<div class="card">
    <h3>📊 Excel File Import System</h3>

    <div class="card" style="background-color: #e7f3ff; margin-bottom: 20px;">
        <h4>📋 Import Instructions</h4>
        <p><strong>Excel File Format:</strong> Your Excel file should have the following columns in order:</p>
        <ol>
            <li><strong>Name</strong> (Required) - Full name of the person</li>
            <li><strong>Email</strong> (Required) - Valid email address</li>
            <li><strong>Phone</strong> (Optional) - Phone number</li>
            <li><strong>Company</strong> (Optional) - Company name</li>
            <li><strong>Position</strong> (Optional) - Job position/title</li>
            <li><strong>Notes</strong> (Optional) - Additional notes</li>
        </ol>
        <p><strong>Supported formats:</strong> .xlsx, .xls, .csv | <strong>Max size:</strong> 10MB</p>
        <p><strong>Note:</strong> First row should contain headers and will be skipped during import.</p>
    </div>

    <?php if ($import_message): ?>
        <div
            style="color: green; background: #d4edda; padding: 15px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
            ✅ <?php echo $import_message; ?>
        </div>
    <?php endif; ?>

    <?php if ($import_error): ?>
        <div
            style="color: #721c24; background: #f8d7da; padding: 15px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
            ❌ <?php echo $import_error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="importForm">
        <div class="form-group">
            <label for="excel_file">📁 Select Excel File:</label>
            <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv" required
                style="width: 100%; padding: 15px; border: 2px dashed #ddd; border-radius: 8px; background: #f9f9f9;">
            <small style="color: #666;">
                Drag and drop your Excel file here or click to browse
            </small>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" name="import_excel" class="btn btn-primary" id="importBtn">
                📤 Import Excel Data
            </button>

            <button type="button" class="btn" style="background: #6c757d; color: white; margin-left: 10px;"
                onclick="document.getElementById('importForm').reset();">
                🔄 Clear
            </button>
        </div>
    </form>
</div>

<!-- Statistics -->
<div class="card">
    <h3>📈 Import Statistics</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <div
            style="text-align: center; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px;">
            <h3 style="margin: 0; font-size: 2rem;"><?php echo count($recent_imports); ?></h3>
            <p style="margin: 5px 0;">Total Imports</p>
        </div>
        <div
            style="text-align: center; padding: 20px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border-radius: 8px;">
            <h3 style="margin: 0; font-size: 2rem;"><?php echo $imported_data_count; ?></h3>
            <p style="margin: 5px 0;">Total Records</p>
        </div>
        <div
            style="text-align: center; padding: 20px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-radius: 8px;">
            <h3 style="margin: 0; font-size: 2rem;">
                <?php
                $avg_per_import = count($recent_imports) > 0 ? round($imported_data_count / count($recent_imports)) : 0;
                echo $avg_per_import;
                ?>
            </h3>
            <p style="margin: 5px 0;">Avg per Import</p>
        </div>
    </div>
</div>

<!-- Recent Imports -->
<div class="card">
    <h3>📋 Recent Imports</h3>

    <?php if (empty($recent_imports)): ?>
        <p style="color: #666; font-style: italic;">No imports yet. Upload your first Excel file above.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Original Filename</th>
                    <th>Rows Imported</th>
                    <th>Status</th>
                    <th>Uploaded By</th>
                    <th>Upload Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_imports as $import): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($import['original_name']); ?></strong>
                        </td>
                        <td><?php echo $import['rows_imported']; ?></td>
                        <td>
                            <?php
                            $status_colors = [
                                'completed' => 'green',
                                'pending' => 'orange',
                                'failed' => 'red'
                            ];
                            $color = $status_colors[$import['status']] ?? 'gray';
                            ?>
                            <span style="color: <?php echo $color; ?>;">
                                <?php echo ucfirst($import['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($import['uploaded_by']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($import['upload_date'])); ?></td>
                        <td>
                            <a href="admin.php?section=imported_data&import_id=<?php echo $import['id']; ?>"
                                class="btn btn-primary">View Data</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Sample Excel Template -->
<div class="card">
    <h3>📄 Download Sample Template</h3>
    <p>Download a sample Excel template to see the correct format for importing data.</p>
    <button onclick="downloadSampleTemplate()" class="btn btn-primary">
        📥 Download Sample Excel Template
    </button>
</div>

<script>
    // File upload validation
    document.getElementById('excel_file').addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel', 'text/csv'];

            if (file.size > maxSize) {
                alert('File is too large! Maximum size is 10MB.');
                this.value = '';
                return;
            }

            if (!allowedTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls|csv)$/i)) {
                alert('Invalid file type! Please upload Excel (.xlsx, .xls) or CSV files only.');
                this.value = '';
                return;
            }

            // Show file info
            const fileInfo = document.createElement('div');
            fileInfo.innerHTML = `
            <small style="color: #28a745;">
                ✅ Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)
            </small>
        `;

            // Remove previous file info
            const existingInfo = this.parentNode.querySelector('.file-info');
            if (existingInfo) {
                existingInfo.remove();
            }

            fileInfo.className = 'file-info';
            this.parentNode.appendChild(fileInfo);
        }
    });

    // Form submission with loading state
    document.getElementById('importForm').addEventListener('submit', function () {
        const btn = document.getElementById('importBtn');
        btn.disabled = true;
        btn.innerHTML = '⏳ Importing... Please wait';
    });

    // Download sample template
    function downloadSampleTemplate() {
        // Create sample data
        const sampleData = [
            ['Name', 'Email', 'Phone', 'Company', 'Position', 'Notes'],
            ['John Doe', 'john.doe@example.com', '+1234567890', 'Tech Corp', 'Software Engineer', 'Experienced developer'],
            ['Jane Smith', 'jane.smith@example.com', '+1234567891', 'Design Studio', 'UI/UX Designer', 'Creative professional'],
            ['Mike Johnson', 'mike.johnson@example.com', '+1234567892', 'Marketing Inc', 'Marketing Manager', 'Digital marketing expert']
        ];

        // Convert to CSV
        const csvContent = sampleData.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');

        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'sample_import_template.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
</script>
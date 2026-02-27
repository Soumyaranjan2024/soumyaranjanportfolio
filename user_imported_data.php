<?php
require_once 'auth.php';
require_once 'database.php';

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$message = '';
$error = '';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM imported_data WHERE id = ? AND uploaded_by = ?");
        $stmt->execute([$_GET['delete'], $username]);
        $message = "Contact deleted successfully";
    } catch (Exception $e) {
        $error = "Error deleting contact: " . $e->getMessage();
    }
}

// Handle bulk delete
if (isset($_POST['bulk_delete']) && !empty($_POST['selected_contacts'])) {
    try {
        $placeholders = str_repeat('?,', count($_POST['selected_contacts']) - 1) . '?';
        $stmt = $pdo->prepare("DELETE FROM imported_data WHERE id IN ($placeholders) AND uploaded_by = ?");
        $stmt->execute(array_merge($_POST['selected_contacts'], [$username]));
        $message = "Selected contacts deleted successfully";
    } catch (Exception $e) {
        $error = "Error deleting contacts: " . $e->getMessage();
    }
}

// Pagination and search
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$whereClause = "WHERE uploaded_by = ?";
$params = [$username];

if (!empty($search)) {
    $whereClause .= " AND (name LIKE ? OR email LIKE ? OR company LIKE ? OR position LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

// Get total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM imported_data $whereClause");
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Get data
$stmt = $pdo->prepare("
    SELECT id, name, email, phone, company, position, notes, import_date 
    FROM imported_data 
    $whereClause 
    ORDER BY import_date DESC 
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get import statistics
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_contacts,
        COUNT(DISTINCT company) as unique_companies,
        COUNT(CASE WHEN email IS NOT NULL AND email != '' THEN 1 END) as contacts_with_email,
        COUNT(CASE WHEN phone IS NOT NULL AND phone != '' THEN 1 END) as contacts_with_phone
    FROM imported_data 
    WHERE uploaded_by = ?
");
$statsStmt->execute([$username]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get recent imports
$importsStmt = $pdo->prepare("
    SELECT original_name, rows_imported, upload_date, status 
    FROM excel_uploads 
    WHERE uploaded_by = ? 
    ORDER BY upload_date DESC 
    LIMIT 5
");
$importsStmt->execute([$username]);
$recentImports = $importsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imported Data - Portfolio Email System</title>
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
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #6f42c1;
            padding-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
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

        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-box input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 250px;
        }

        .btn {
            background: #6f42c1;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background: #5a32a3;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-success {
            background: #28a745;
        }

        .btn-success:hover {
            background: #1e7e34;
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
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #6f42c1;
            color: white;
            position: sticky;
            top: 0;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e9ecef;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-decoration: none;
            border-radius: 4px;
        }

        .pagination a:hover {
            background-color: #6f42c1;
            color: white;
        }

        .pagination .current {
            background-color: #6f42c1;
            color: white;
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

        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .recent-imports {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .import-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .import-item:last-child {
            border-bottom: none;
        }

        .bulk-actions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            display: none;
        }

        .bulk-actions.show {
            display: block;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📋 Imported Data Management</h1>
            <p>View and manage your imported contact data</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_contacts']; ?></div>
                <div>Total Contacts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['contacts_with_email']; ?></div>
                <div>With Email</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['contacts_with_phone']; ?></div>
                <div>With Phone</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['unique_companies']; ?></div>
                <div>Companies</div>
            </div>
        </div>

        <?php if (!empty($recentImports)): ?>
            <div class="recent-imports">
                <h3>📥 Recent Imports</h3>
                <?php foreach ($recentImports as $import): ?>
                    <div class="import-item">
                        <div>
                            <strong><?php echo htmlspecialchars($import['original_name']); ?></strong>
                            <span style="color: #6c757d;">- <?php echo $import['rows_imported']; ?> records</span>
                        </div>
                        <div>
                            <span class="btn btn-sm" style="font-size: 12px;">
                                <?php echo ucfirst($import['status']); ?>
                            </span>
                            <small><?php echo date('M j, Y H:i', strtotime($import['upload_date'])); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="controls">
            <div class="search-box">
                <form method="GET" style="display: flex; gap: 10px;">
                    <input type="text" name="search" placeholder="Search contacts..."
                        value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn">🔍 Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="user_imported_data.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <div>
                <a href="user_excel_import.php" class="btn btn-success">📥 Import More Data</a>
                <a href="user_bulk_email.php" class="btn">📧 Send Bulk Email</a>
                <a href="user_dashboard.php" class="btn btn-secondary">← Dashboard</a>
            </div>
        </div>

        <?php if (!empty($contacts)): ?>
            <form method="POST" id="bulkForm">
                <div class="bulk-actions" id="bulkActions">
                    <strong>Bulk Actions:</strong>
                    <button type="submit" name="bulk_delete" class="btn btn-danger"
                        onclick="return confirm('Are you sure you want to delete selected contacts?')">
                        🗑️ Delete Selected
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearSelection()">
                        Clear Selection
                    </button>
                    <span id="selectedCount">0 selected</span>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
                            </th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Import Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $contact): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_contacts[]" value="<?php echo $contact['id']; ?>"
                                        onchange="updateSelection()">
                                </td>
                                <td><?php echo htmlspecialchars($contact['name'] ?: 'N/A'); ?></td>
                                <td>
                                    <?php if ($contact['email']): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>">
                                            <?php echo htmlspecialchars($contact['email']); ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($contact['phone'] ?: 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($contact['company'] ?: 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($contact['position'] ?: 'N/A'); ?></td>
                                <td><?php echo date('M j, Y', strtotime($contact['import_date'])); ?></td>
                                <td>
                                    <a href="?delete=<?php echo $contact['id']; ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to delete this contact?')">
                                        🗑️
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">« Previous</a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next »</a>
                    <?php endif; ?>
                </div>

                <div style="text-align: center; margin-top: 10px; color: #6c757d;">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalRecords); ?>
                    of <?php echo $totalRecords; ?> contacts
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-data">
                <h3>📭 No Imported Data</h3>
                <p>You haven't imported any contact data yet.</p>
                <a href="user_excel_import.php" class="btn btn-success">📥 Import Your First File</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleAll(source) {
            const checkboxes = document.querySelectorAll('input[name="selected_contacts[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = source.checked;
            });
            updateSelection();
        }

        function updateSelection() {
            const checkboxes = document.querySelectorAll('input[name="selected_contacts[]"]:checked');
            const count = checkboxes.length;
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');

            if (count > 0) {
                bulkActions.classList.add('show');
                selectedCount.textContent = count + ' selected';
            } else {
                bulkActions.classList.remove('show');
            }

            // Update "select all" checkbox
            const allCheckboxes = document.querySelectorAll('input[name="selected_contacts[]"]');
            const selectAllCheckbox = document.getElementById('selectAll');
            selectAllCheckbox.checked = count === allCheckboxes.length;
            selectAllCheckbox.indeterminate = count > 0 && count < allCheckboxes.length;
        }

        function clearSelection() {
            const checkboxes = document.querySelectorAll('input[name="selected_contacts[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('selectAll').checked = false;
            updateSelection();
        }

        // Initialize selection state
        document.addEventListener('DOMContentLoaded', function () {
            updateSelection();
        });
    </script>
</body>

</html>
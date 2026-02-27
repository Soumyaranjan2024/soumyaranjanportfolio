<?php
// Get imported data with optional filtering
$import_id = $_GET['import_id'] ?? '';
$where_clause = '';
$params = [];

if ($import_id) {
    // Get specific import info
    $stmt = $pdo->prepare("SELECT * FROM excel_uploads WHERE id = ?");
    $stmt->execute([$import_id]);
    $import_info = $stmt->fetch();

    $where_clause = "WHERE uploaded_by = ? AND DATE(import_date) = DATE(?)";
    $params = [$import_info['uploaded_by'], $import_info['upload_date']];
}

$stmt = $pdo->prepare("SELECT * FROM imported_data $where_clause ORDER BY import_date DESC");
$stmt->execute($params);
$imported_data = $stmt->fetchAll();
?>

<div class="card">
    <h3>📊 Imported Data</h3>

    <?php if ($import_info): ?>
        <div class="card" style="background-color: #f8f9fa; margin-bottom: 20px;">
            <h4>📋 Import Details</h4>
            <p><strong>File:</strong> <?php echo htmlspecialchars($import_info['original_name']); ?></p>
            <p><strong>Uploaded by:</strong> <?php echo htmlspecialchars($import_info['uploaded_by']); ?></p>
            <p><strong>Upload date:</strong> <?php echo date('M d, Y H:i', strtotime($import_info['upload_date'])); ?></p>
            <p><strong>Rows imported:</strong> <?php echo $import_info['rows_imported']; ?></p>
            <a href="admin.php?section=imported_data" class="btn btn-primary">← View All Data</a>
        </div>
    <?php endif; ?>

    <?php if (empty($imported_data)): ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <p style="font-size: 1.2rem;">📭 No imported data found</p>
            <p>Import your first Excel file to see data here.</p>
            <a href="admin.php?section=excel_import" class="btn btn-primary">Import Excel File</a>
        </div>
    <?php else: ?>
        <div style="margin-bottom: 20px;">
            <strong>Total Records:</strong> <?php echo count($imported_data); ?>
            <a href="admin.php?section=reports" class="btn btn-primary" style="float: right;">
                📄 Generate PDF Report
            </a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
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
                <?php foreach ($imported_data as $row): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['company']); ?></td>
                        <td><?php echo htmlspecialchars($row['position']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($row['import_date'])); ?></td>
                        <td>
                            <button onclick="viewDetails(<?php echo $row['id']; ?>)" class="btn btn-primary">
                                View
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Modal for viewing details -->
<div id="detailsModal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div
        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%;">
        <h4>📋 Record Details</h4>
        <div id="detailsContent"></div>
        <button onclick="closeModal()" class="btn btn-primary" style="margin-top: 20px;">Close</button>
    </div>
</div>

<script>
    function viewDetails(id) {
        // Find the record data
        const data = <?php echo json_encode($imported_data); ?>;
        const record = data.find(r => r.id == id);

        if (record) {
            const content = `
            <p><strong>Name:</strong> ${record.name}</p>
            <p><strong>Email:</strong> ${record.email}</p>
            <p><strong>Phone:</strong> ${record.phone || 'N/A'}</p>
            <p><strong>Company:</strong> ${record.company || 'N/A'}</p>
            <p><strong>Position:</strong> ${record.position || 'N/A'}</p>
            <p><strong>Notes:</strong> ${record.notes || 'N/A'}</p>
            <p><strong>Imported by:</strong> ${record.uploaded_by}</p>
            <p><strong>Import Date:</strong> ${new Date(record.import_date).toLocaleString()}</p>
        `;

            document.getElementById('detailsContent').innerHTML = content;
            document.getElementById('detailsModal').style.display = 'block';
        }
    }

    function closeModal() {
        document.getElementById('detailsModal').style.display = 'none';
    }

    // Close modal when clicking outside
    document.getElementById('detailsModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeModal();
        }
    });
</script>
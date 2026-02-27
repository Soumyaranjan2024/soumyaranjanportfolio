<div class="card">
    <h3><?php echo isset($_GET['edit_service']) ? 'Edit Service' : 'Add New Service'; ?></h3>
    <?php if (isset($service_success)): ?>
        <div style="color: green; margin-bottom: 15px;"><?php echo $service_success; ?></div>
    <?php endif; ?>

    <?php 
    $edit_srv = null;
    if (isset($_GET['edit_service'])) {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$_GET['edit_service']]);
        $edit_srv = $stmt->fetch();
    }
    ?>

    <form method="POST">
        <?php if ($edit_srv): ?>
            <input type="hidden" name="id" value="<?php echo $edit_srv['id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="title">Service Title</label>
            <input type="text" id="title" name="title" value="<?php echo $edit_srv ? htmlspecialchars($edit_srv['title']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="icon">Icon Class (FontAwesome, e.g., 'fas fa-code')</label>
            <input type="text" id="icon" name="icon" value="<?php echo $edit_srv ? htmlspecialchars($edit_srv['icon']) : ''; ?>" required>
            <small>Preview: <i id="icon-preview" class="<?php echo $edit_srv ? htmlspecialchars($edit_srv['icon']) : 'fas fa-question'; ?>"></i></small>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3" required><?php echo $edit_srv ? htmlspecialchars($edit_srv['description']) : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label for="order_num">Display Order (Lower numbers show first)</label>
            <input type="number" id="order_num" name="order_num" value="<?php echo $edit_srv ? (int)$edit_srv['order_num'] : '0'; ?>">
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" name="<?php echo $edit_srv ? 'update_service' : 'add_service'; ?>" class="btn btn-primary">
                <?php echo $edit_srv ? 'Update Service' : 'Add Service'; ?>
            </button>
            <?php if ($edit_srv): ?>
                <a href="admin.php?section=services" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <h3>Manage Services</h3>
    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Icon</th>
                <th>Title</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services_list as $srv): ?>
                <tr>
                    <td><?php echo (int)$srv['order_num']; ?></td>
                    <td><i class="<?php echo htmlspecialchars($srv['icon']); ?>"></i></td>
                    <td><?php echo htmlspecialchars($srv['title']); ?></td>
                    <td><?php echo htmlspecialchars(substr($srv['description'], 0, 50)) . (strlen($srv['description']) > 50 ? '...' : ''); ?></td>
                    <td>
                        <a href="admin.php?section=services&edit_service=<?php echo $srv['id']; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $srv['id']; ?>">
                            <button type="submit" name="delete_service" class="btn btn-danger btn-sm" onclick="return confirm('Delete this service?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('icon').addEventListener('input', function() {
    document.getElementById('icon-preview').className = this.value || 'fas fa-question';
});
</script>

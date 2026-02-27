<div class="card">
    <h3><?php echo isset($_GET['edit_journey']) ? 'Edit Milestone' : 'Add New Milestone'; ?></h3>
    
    <?php if (isset($journey_success)): ?>
        <div style="color: green; margin-bottom: 15px;"><?php echo $journey_success; ?></div>
    <?php endif; ?>

    <?php 
    $edit_milestone = null;
    if (isset($_GET['edit_journey'])) {
        $stmt = $pdo->prepare("SELECT * FROM journey WHERE id = ?");
        $stmt->execute([$_GET['edit_journey']]);
        $edit_milestone = $stmt->fetch();
    }
    ?>

    <form method="POST">
        <?php if ($edit_milestone): ?>
            <input type="hidden" name="id" value="<?php echo $edit_milestone['id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="year_range">Year Range (e.g., 2023 - Present)</label>
            <input type="text" id="year_range" name="year_range" value="<?php echo $edit_milestone ? htmlspecialchars($edit_milestone['year_range']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="title">Title / Role</label>
            <input type="text" id="title" name="title" value="<?php echo $edit_milestone ? htmlspecialchars($edit_milestone['title']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="company">Company / Institution</label>
            <input type="text" id="company" name="company" value="<?php echo $edit_milestone ? htmlspecialchars($edit_milestone['company']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" required><?php echo $edit_milestone ? htmlspecialchars($edit_milestone['description']) : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label for="position_side">Display Side</label>
            <select name="position_side" id="position_side">
                <option value="left" <?php echo ($edit_milestone && $edit_milestone['position_side'] == 'left') ? 'selected' : ''; ?>>Left</option>
                <option value="right" <?php echo ($edit_milestone && $edit_milestone['position_side'] == 'right') ? 'selected' : ''; ?>>Right</option>
            </select>
        </div>

        <div class="form-group">
            <label for="order_num">Display Order (Lower numbers show first)</label>
            <input type="number" id="order_num" name="order_num" value="<?php echo $edit_milestone ? $edit_milestone['order_num'] : '0'; ?>">
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" name="<?php echo $edit_milestone ? 'update_journey' : 'add_journey'; ?>" class="btn btn-primary">
                <?php echo $edit_milestone ? 'Update Milestone' : 'Add Milestone'; ?>
            </button>
            <?php if ($edit_milestone): ?>
                <a href="admin.php?section=journey" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <h3>My Journey Milestones</h3>
    <table>
        <thead>
            <tr>
                <th>Year</th>
                <th>Role</th>
                <th>Company</th>
                <th>Side</th>
                <th>Order</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $all_milestones = $pdo->query("SELECT * FROM journey ORDER BY order_num ASC")->fetchAll();
            foreach ($all_milestones as $m): 
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($m['year_range']); ?></td>
                    <td><?php echo htmlspecialchars($m['title']); ?></td>
                    <td><?php echo htmlspecialchars($m['company']); ?></td>
                    <td><?php echo ucfirst($m['position_side']); ?></td>
                    <td><?php echo $m['order_num']; ?></td>
                    <td>
                        <a href="admin.php?section=journey&edit_journey=<?php echo $m['id']; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                            <button type="submit" name="delete_journey" class="btn btn-danger btn-sm" onclick="return confirm('Delete this milestone?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

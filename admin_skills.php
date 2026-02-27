<div class="card">
    <h3>Add New Skill</h3>
    <form method="POST">
        <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="development">Development</option>
                <option value="design">Design</option>
                <option value="tools">Tools</option>

                <option value="marketing">Marketing</option>
                <option value="branding">Branding</option>

            </select>
        </div>
        <div class="form-group">
            <label for="name">Skill Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="proficiency">Proficiency (1-100)</label>
            <input type="number" id="proficiency" name="proficiency" min="1" max="100" required>
        </div>
        <button type="submit" name="add_skill" class="btn btn-primary">Add Skill</button>
    </form>
</div>

<div class="card">
    <h3>All Skills</h3>
    <?php if (isset($skill_success)): ?>
        <div style="color: green; margin-bottom: 15px;"><?php echo $skill_success; ?></div>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Name</th>
                <th>Proficiency</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($skills as $skill): ?>
                <tr>
                    <td><?php echo ucfirst(htmlspecialchars($skill['category'])); ?></td>
                    <td><?php echo htmlspecialchars($skill['name']); ?></td>
                    <td><?php echo htmlspecialchars($skill['proficiency']); ?>%</td>
                    <td>
                        <a href="admin.php?section=skills&edit=<?php echo $skill['id']; ?>" class="btn btn-primary">Edit</a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $skill['id']; ?>">
                            <button type="submit" name="delete_skill" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to delete this skill?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (isset($_GET['edit'])):
    $edit_id = $_GET['edit'];
    $edit_skill = $pdo->prepare("SELECT * FROM skills WHERE id = ?");
    $edit_skill->execute([$edit_id]);
    $skill = $edit_skill->fetch();
    if ($skill):
        ?>
        <div class="card">
            <h3>Edit Skill</h3>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $skill['id']; ?>">
                <div class="form-group">
                    <label for="edit_category">Category</label>
                    <select id="edit_category" name="category" required>
                        <option value="development" <?php echo $skill['category'] == 'development' ? 'selected' : ''; ?>>
                            Development</option>
                        <option value="design" <?php echo $skill['category'] == 'design' ? 'selected' : ''; ?>>Design</option>
                        <option value="tools" <?php echo $skill['category'] == 'tools' ? 'selected' : ''; ?>>Tools</option>
                        <option value="other" <?php echo $skill['category'] == 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_name">Skill Name</label>
                    <input type="text" id="edit_name" name="name" value="<?php echo htmlspecialchars($skill['name']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="edit_proficiency">Proficiency (1-100)</label>
                    <input type="number" id="edit_proficiency" name="proficiency" min="1" max="100"
                        value="<?php echo htmlspecialchars($skill['proficiency']); ?>" required>
                </div>
                <button type="submit" name="update_skill" class="btn btn-primary">Update Skill</button>
            </form>
        </div>
    <?php endif; endif; ?>
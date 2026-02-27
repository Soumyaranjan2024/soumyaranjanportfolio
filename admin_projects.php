<div class="card">
    <h3>Add New Project</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" required></textarea>
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="web">Web Design</option>
                <option value="app">App Design</option>
                <option value="ui">UI/UX</option>
                <option value="marketing">Marketing</option>

            </select>
        </div>
        <div class="form-group">
            <label for="tags">Tags (comma separated)</label>
            <input type="text" id="tags" name="tags" placeholder="React, Node.js, MongoDB">
        </div>
        <div class="form-group">
            <label for="live_link">Live Demo Link</label>
            <input type="url" id="live_link" name="live_link" placeholder="https://example.com">
        </div>
        <div class="form-group">
            <label for="github_link">GitHub Repo Link</label>
            <input type="url" id="github_link" name="github_link" placeholder="https://github.com/username/repo">
        </div>
        <div class="form-group">
            <label for="image">Project Image</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        <button type="submit" name="add_project" class="btn btn-primary">Add Project</button>
    </form>
</div>

<div class="card">
    <h3>All Projects</h3>
    <?php if (isset($project_success)): ?>
        <div style="color: green; margin-bottom: 15px;"><?php echo $project_success; ?></div>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?php echo htmlspecialchars($project['title']); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($project['category'])); ?></td>
                    <td><?php echo date('M d, Y', strtotime($project['created_at'])); ?></td>
                    <td>
                        <a href="admin.php?section=projects&edit=<?php echo $project['id']; ?>"
                            class="btn btn-primary">Edit</a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                            <button type="submit" name="delete_project" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to delete this project?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (isset($_GET['edit'])):
    $edit_id = $_GET['edit'];
    $edit_project = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $edit_project->execute([$edit_id]);
    $project = $edit_project->fetch();
    if ($project):
        ?>
        <div class="card">
            <h3>Edit Project</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($project['image_url']); ?>">
                <div class="form-group">
                    <label for="edit_title">Title</label>
                    <input type="text" id="edit_title" name="title" value="<?php echo htmlspecialchars($project['title']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description"
                        required><?php echo htmlspecialchars($project['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_category">Category</label>
                    <select id="edit_category" name="category" required>
                        <option value="web" <?php echo $project['category'] == 'web' ? 'selected' : ''; ?>>Web Design</option>
                        <option value="app" <?php echo $project['category'] == 'app' ? 'selected' : ''; ?>>App Design</option>
                        <option value="ui" <?php echo $project['category'] == 'ui' ? 'selected' : ''; ?>>UI/UX</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_tags">Tags (comma separated)</label>
                    <input type="text" id="edit_tags" name="tags" value="<?php echo htmlspecialchars($project['tags']); ?>"
                        placeholder="React, Node.js, MongoDB">
                </div>
                <div class="form-group">
                    <label for="edit_live">Live Demo Link</label>
                    <input type="url" id="edit_live" name="live_link" value="<?php echo htmlspecialchars($project['live_link'] ?? ''); ?>" placeholder="https://example.com">
                </div>
                <div class="form-group">
                    <label for="edit_github">GitHub Repo Link</label>
                    <input type="url" id="edit_github" name="github_link" value="<?php echo htmlspecialchars($project['github_link'] ?? ''); ?>" placeholder="https://github.com/username/repo">
                </div>
                <div class="form-group">
                    <label>Current Image</label>
                    <?php if ($project['image_url']): ?>
                        <img src="../<?php echo htmlspecialchars($project['image_url']); ?>"
                            style="max-width: 200px; display: block; margin-bottom: 10px;">
                    <?php else: ?>
                        <p>No image uploaded</p>
                    <?php endif; ?>
                    <label for="edit_image">New Image (leave blank to keep current)</label>
                    <input type="file" id="edit_image" name="image" accept="image/*">
                </div>
                <button type="submit" name="update_project" class="btn btn-primary">Update Project</button>
            </form>
        </div>
    <?php endif; endif; ?>
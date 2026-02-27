<?php require_once 'config/tinymce_config.php'; ?>
<!-- TinyMCE Rich Text Editor -->
<script src="<?php echo getTinyMceConfig()['script_url']; ?>" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#blog_content',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 400,
        images_upload_url: 'upload_handler.php',
        automatic_uploads: true,
        image_advtab: true,
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
    });
</script>

<div class="card">
    <h3><?php echo isset($_GET['edit']) ? 'Edit' : 'Add New'; ?> Blog Post</h3>
    
    <?php if (isset($blog_success)): ?>
        <div style="color: green; margin-bottom: 15px;"><?php echo $blog_success; ?></div>
    <?php endif; ?>

    <?php 
    $edit_post = null;
    if (isset($_GET['edit'])) {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_post = $stmt->fetch();
    }
    ?>

    <form method="POST" enctype="multipart/form-data">
        <?php if ($edit_post): ?>
            <input type="hidden" name="id" value="<?php echo $edit_post['id']; ?>">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($edit_post['featured_image']); ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?php echo $edit_post ? htmlspecialchars($edit_post['title']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="excerpt">Excerpt (Short summary)</label>
            <textarea id="excerpt" name="excerpt" rows="3"><?php echo $edit_post ? htmlspecialchars($edit_post['excerpt']) : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label for="blog_content">Content</label>
            <textarea id="blog_content" name="content"><?php echo $edit_post ? $edit_post['content'] : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="published" <?php echo ($edit_post && $edit_post['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                <option value="draft" <?php echo ($edit_post && $edit_post['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
            </select>
        </div>

        <div class="form-group">
            <label for="featured_image">Featured Image</label>
            <?php if ($edit_post && $edit_post['featured_image']): ?>
                <img src="../<?php echo htmlspecialchars($edit_post['featured_image']); ?>" style="max-width: 200px; display: block; margin-bottom: 10px; border-radius: 8px;">
            <?php endif; ?>
            <input type="file" id="featured_image" name="featured_image" accept="image/*">
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" name="<?php echo $edit_post ? 'update_blog' : 'add_blog'; ?>" class="btn btn-primary">
                <?php echo $edit_post ? 'Update Post' : 'Publish Post'; ?>
            </button>
            <?php if ($edit_post): ?>
                <a href="admin.php?section=blog" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if (!isset($_GET['edit'])): ?>
<div class="card">
    <h3>Manage Blog Posts</h3>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($blog_posts as $post): ?>
                <tr>
                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                    <td><?php echo htmlspecialchars($post['author']); ?></td>
                    <td>
                        <span class="badge" style="background: <?php echo $post['status'] == 'published' ? '#51cf66' : '#fab005'; ?>; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">
                            <?php echo ucfirst($post['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                    <td>
                        <a href="admin.php?section=blog&edit=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                            <button type="submit" name="delete_blog" class="btn btn-danger btn-sm" onclick="return confirm('Delete this post?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        <a href="../blog-post.php?slug=<?php echo $post['slug']; ?>" target="_blank" class="btn btn-secondary btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

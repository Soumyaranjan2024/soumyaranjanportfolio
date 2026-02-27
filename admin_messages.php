<div class="card">
    <h3>All Messages</h3>
    <?php if (isset($message_success)): ?>
        <div style="color: green; margin-bottom: 15px;"><?php echo $message_success; ?></div>
    <?php endif; ?>
    <?php if (isset($message_error)): ?>
        <div style="color: red; margin-bottom: 15px;"><?php echo $message_error; ?></div>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $message): ?>
                <tr style="<?php echo $message['is_read'] ? '' : 'background-color: #f8f9fa; font-weight: 500;'; ?>">
                    <td><?php echo htmlspecialchars($message['name']); ?></td>
                    <td><?php echo htmlspecialchars($message['email']); ?></td>
                    <td><?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?></td>
                    <td><?php echo date('M d, Y', strtotime($message['created_at'])); ?></td>
                    <td><?php echo $message['is_read'] ? 'Read' : 'Unread'; ?></td>
                    <td>
                        <a href="admin.php?section=messages&view=<?php echo $message['id']; ?>"
                            class="btn btn-primary">View</a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                            <button type="submit" name="delete_message" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to delete this message?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (isset($_GET['view'])):
    $view_id = $_GET['view'];
    $view_message = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $view_message->execute([$view_id]);
    $message = $view_message->fetch();
    if ($message):
        // Mark as read when viewing
        if (!$message['is_read']) {
            $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE id = ?")->execute([$message['id']]);
        }
        ?>
        <div class="card">
            <h3>Message Details</h3>
            <div style="margin-bottom: 20px;">
                <p><strong>From:</strong> <?php echo htmlspecialchars($message['name']); ?>
                    &lt;<?php echo htmlspecialchars($message['email']); ?>&gt;</p>
                <p><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></p>
                <?php if ($message['subject']): ?>
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($message['subject']); ?></p>
                <?php endif; ?>
            </div>
            <div class="card" style="background-color: #f8f9fa; padding: 20px;">
                <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
            </div>
            <div style="margin-top: 20px;">
                <button onclick="document.getElementById('reply-form').style.display='block'" class="btn btn-primary">Reply</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                    <button type="submit" name="delete_message" class="btn btn-danger"
                        onclick="return confirm('Are you sure you want to delete this message?')">Delete</button>
                </form>
            </div>

            <!-- Threaded Replies -->
            <?php
            $replies_stmt = $pdo->prepare("SELECT * FROM message_replies WHERE message_id = ? ORDER BY created_at ASC");
            $replies_stmt->execute([$message['id']]);
            $replies = $replies_stmt->fetchAll();
            if (count($replies) > 0): ?>
                <div style="margin-top: 30px;">
                    <h4>Conversation History</h4>
                    <?php foreach ($replies as $reply): ?>
                        <div class="card" style="background-color: #eef2ff; border-left: 4px solid var(--primary-color); margin-bottom: 15px; padding: 15px;">
                            <div style="font-size: 0.85rem; color: var(--gray-color); margin-bottom: 8px;">
                                <strong>You</strong> replied on <?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?>
                            </div>
                            <div class="reply-content">
                                <?php echo $reply['reply_text']; // Allow rich text ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Reply Form -->
            <div id="reply-form" class="card" style="display: none; margin-top: 20px; background-color: #fff; border: 1px solid #ddd;">
                <h3>Compose Reply</h3>
                <form method="POST">
                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                    <div class="form-group">
                        <textarea id="reply_text" name="reply_text" rows="6" placeholder="Type your reply here..."></textarea>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="reply_message" class="btn btn-primary">Send Reply</button>
                        <button type="button" onclick="document.getElementById('reply-form').style.display='none'" class="btn btn-secondary">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TinyMCE for Reply -->
        <?php require_once 'config/tinymce_config.php'; ?>
        <script src="<?php echo getTinyMceConfig()['script_url']; ?>" referrerpolicy="origin"></script>
        <script>
            tinymce.init({
                selector: '#reply_text',
                plugins: 'link lists emoticons',
                toolbar: 'bold italic | bullist numlist | link emoticons',
                menubar: false,
                height: 200
            });
        </script>
    <?php endif; endif; ?>

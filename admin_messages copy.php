<div class="card">
    <h3>All Messages</h3>
    <?php if (isset($message_success)): ?>
        <div style="color: green; margin-bottom: 15px;"><?php echo $message_success; ?></div>
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
                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-primary">Reply</a>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                    <button type="submit" name="delete_message" class="btn btn-danger"
                        onclick="return confirm('Are you sure you want to delete this message?')">Delete</button>
                </form>
            </div>
        </div>
    <?php endif; endif; ?>
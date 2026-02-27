<?php
require_once 'auth.php';
requireAdmin();
require_once 'database.php';

$message = '';

// Add User Functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    try {
        // Validate inputs
        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception('All fields are required');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Check for existing user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            throw new Exception('Username or email already exists');
        }

        // Insert new user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password, $is_admin]);

        $message = '<div class="success">User added successfully!</div>';
    } catch (Exception $e) {
        $message = '<div class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Delete User Functionality
if (isset($_GET['delete'])) {
    $user_id = (int) $_GET['delete'];

    try {
        // Prevent self-deletion
        if ($_SESSION['user']['id'] === $user_id) {
            throw new Exception('You cannot delete your own account');
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        if ($stmt->rowCount() > 0) {
            $message = '<div class="success">User deleted successfully!</div>';
        } else {
            throw new Exception('User not found');
        }
    } catch (Exception $e) {
        $message = '<div class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Fetch all users
try {
    $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $message = '<div class="error">Error loading users: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>

<div class="card">
    <?= $message ?>

    <div class="card">
        <h3>Add New User</h3>
        <form method="POST">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_admin"> Admin Privileges
                </label>
            </div>
            <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
        </form>
    </div>

    <div class="card">
        <h3>Manage Users</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Admin</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= $user['is_admin'] ? 'Yes' : 'No' ?></td>
                        <td><?= date('M j, Y H:i', strtotime($user['created_at'])) ?></td>
                        <td>
                            <?php if ($user['id'] != $_SESSION['user']['id']): ?>
                                <a href="admin.php?section=users&delete=<?= $user['id'] ?>" class="btn btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this user?')">
                                    Delete
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
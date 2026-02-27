<?php
// Projects CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_project'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $tags = $_POST['tags'];
        $live_link = $_POST['live_link'] ?? '';
        $github_link = $_POST['github_link'] ?? '';

        // Handle image upload
        $image_url = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/projects/';
            $absoluteDir = __DIR__ . '/' . $uploadDir; // Fixed to use project root
            if (!is_dir($absoluteDir)) {
                mkdir($absoluteDir, 0755, true);
            }
            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $absoluteDir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image_url = $uploadDir . $filename;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO projects (title, description, image_url, category, tags, live_link, github_link) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $image_url, $category, $tags, $live_link, $github_link]);
        $project_success = "Project added successfully!";
    }

    if (isset($_POST['update_project'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $tags = $_POST['tags'];
        $live_link = $_POST['live_link'] ?? '';
        $github_link = $_POST['github_link'] ?? '';

        // Handle image update
        $image_url = $_POST['current_image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/projects/';
            $absoluteDir = __DIR__ . '/' . $uploadDir;
            if (!is_dir($absoluteDir)) {
                mkdir($absoluteDir, 0755, true);
            }
            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $absoluteDir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                // Delete old image if exists
                if ($image_url && file_exists(__DIR__ . '/' . $image_url)) {
                    unlink(__DIR__ . '/' . $image_url);
                }
                $image_url = $uploadDir . $filename;
            }
        }

        $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ?, image_url = ?, category = ?, tags = ?, live_link = ?, github_link = ? WHERE id = ?");
        $stmt->execute([$title, $description, $image_url, $category, $tags, $live_link, $github_link, $id]);
        $project_success = "Project updated successfully!";
    }

    if (isset($_POST['delete_project'])) {
        $id = $_POST['id'];

        // Delete image if exists
        $stmt = $pdo->prepare("SELECT image_url FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch();
        if ($project && $project['image_url'] && file_exists(__DIR__ . '/' . $project['image_url'])) {
            unlink(__DIR__ . '/' . $project['image_url']);
        }

        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $project_success = "Project deleted successfully!";
    }

    // Skills CRUD
    if (isset($_POST['add_skill'])) {
        $category = $_POST['category'];
        $name = $_POST['name'];
        $proficiency = $_POST['proficiency'];

        $stmt = $pdo->prepare("INSERT INTO skills (category, name, proficiency) VALUES (?, ?, ?)");
        $stmt->execute([$category, $name, $proficiency]);
        $skill_success = "Skill added successfully!";
    }

    if (isset($_POST['update_skill'])) {
        $id = $_POST['id'];
        $category = $_POST['category'];
        $name = $_POST['name'];
        $proficiency = $_POST['proficiency'];

        $stmt = $pdo->prepare("UPDATE skills SET category = ?, name = ?, proficiency = ? WHERE id = ?");
        $stmt->execute([$category, $name, $proficiency, $id]);
        $skill_success = "Skill updated successfully!";
    }

    if (isset($_POST['delete_skill'])) {
        $id = $_POST['id'];

        $stmt = $pdo->prepare("DELETE FROM skills WHERE id = ?");
        $stmt->execute([$id]);
        $skill_success = "Skill deleted successfully!";
    }

    // Mark message as read
    if (isset($_POST['mark_as_read'])) {
        $id = $_POST['id'];

        $stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE id = ?");
        $stmt->execute([$id]);
        $message_success = "Message marked as read!";
    }

    // Delete message
    if (isset($_POST['delete_message'])) {
        $id = $_POST['id'];

        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$id]);
        $message_success = "Message deleted successfully!";
    }

    // Blog CRUD
    if (isset($_POST['add_blog'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $excerpt = $_POST['excerpt'];
        $status = $_POST['status'];
        $author = $_SESSION['username'] ?? 'Admin';
        
        // Generate slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        
        // Handle image upload
        $featured_image = '';
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/blog/';
            $absoluteDir = __DIR__ . '/' . $uploadDir;
            if (!is_dir($absoluteDir)) {
                mkdir($absoluteDir, 0755, true);
            }
            $filename = uniqid() . '_' . basename($_FILES['featured_image']['name']);
            $targetPath = $absoluteDir . $filename;
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetPath)) {
                $featured_image = $uploadDir . $filename;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, featured_image, author, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $author, $status]);
        $blog_success = "Blog post created successfully!";
    }

    if (isset($_POST['update_blog'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $excerpt = $_POST['excerpt'];
        $status = $_POST['status'];
        
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

        // Handle image update
        $featured_image = $_POST['current_image'];
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/blog/';
            $absoluteDir = __DIR__ . '/' . $uploadDir;
            if (!is_dir($absoluteDir)) {
                mkdir($absoluteDir, 0755, true);
            }
            $filename = uniqid() . '_' . basename($_FILES['featured_image']['name']);
            $targetPath = $absoluteDir . $filename;
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetPath)) {
                if ($featured_image && file_exists(__DIR__ . '/' . $featured_image)) {
                    unlink(__DIR__ . '/' . $featured_image);
                }
                $featured_image = $uploadDir . $filename;
            }
        }

        $stmt = $pdo->prepare("UPDATE blog_posts SET title = ?, slug = ?, content = ?, excerpt = ?, featured_image = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $status, $id]);
        $blog_success = "Blog post updated successfully!";
    }

    if (isset($_POST['delete_blog'])) {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("SELECT featured_image FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        if ($post && $post['featured_image'] && file_exists(__DIR__ . '/' . $post['featured_image'])) {
            unlink(__DIR__ . '/' . $post['featured_image']);
        }

        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        $blog_success = "Blog post deleted successfully!";
    }

    // Services CRUD
    if (isset($_POST['add_service'])) {
        $title = $_POST['title'];
        $icon = $_POST['icon'];
        $description = $_POST['description'];
        $order_num = (int)$_POST['order_num'];

        $stmt = $pdo->prepare("INSERT INTO services (title, icon, description, order_num) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $icon, $description, $order_num]);
        $service_success = "Service added successfully!";
    }

    if (isset($_POST['update_service'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $icon = $_POST['icon'];
        $description = $_POST['description'];
        $order_num = (int)$_POST['order_num'];

        $stmt = $pdo->prepare("UPDATE services SET title = ?, icon = ?, description = ?, order_num = ? WHERE id = ?");
        $stmt->execute([$title, $icon, $description, $order_num, $id]);
        $service_success = "Service updated successfully!";
    }

    if (isset($_POST['delete_service'])) {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $service_success = "Service deleted successfully!";
    }

    // Journey CRUD
    if (isset($_POST['add_journey'])) {
        $year_range = $_POST['year_range'];
        $title = $_POST['title'];
        $company = $_POST['company'];
        $description = $_POST['description'];
        $position_side = $_POST['position_side'];
        $order_num = (int)$_POST['order_num'];

        $stmt = $pdo->prepare("INSERT INTO journey (year_range, title, company, description, position_side, order_num) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$year_range, $title, $company, $description, $position_side, $order_num]);
        $journey_success = "Journey milestone added successfully!";
    }

    if (isset($_POST['update_journey'])) {
        $id = $_POST['id'];
        $year_range = $_POST['year_range'];
        $title = $_POST['title'];
        $company = $_POST['company'];
        $description = $_POST['description'];
        $position_side = $_POST['position_side'];
        $order_num = (int)$_POST['order_num'];

        $stmt = $pdo->prepare("UPDATE journey SET year_range = ?, title = ?, company = ?, description = ?, position_side = ?, order_num = ? WHERE id = ?");
        $stmt->execute([$year_range, $title, $company, $description, $position_side, $order_num, $id]);
        $journey_success = "Journey milestone updated successfully!";
    }

    if (isset($_POST['delete_journey'])) {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM journey WHERE id = ?");
        $stmt->execute([$id]);
        $journey_success = "Journey milestone deleted successfully!";
    }

    // Message Reply
    if (isset($_POST['reply_message'])) {
        $message_id = $_POST['message_id'];
        $reply_text = $_POST['reply_text'];

        if (!empty($reply_text)) {
            // Save reply to database
            $stmt = $pdo->prepare("INSERT INTO message_replies (message_id, reply_text) VALUES (?, ?)");
            $stmt->execute([$message_id, $reply_text]);

            // Mark original message as read
            $stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE id = ?");
            $stmt->execute([$message_id]);

            // Send email to user (optional but recommended)
            $stmt = $pdo->prepare("SELECT email, name, subject FROM messages WHERE id = ?");
            $stmt->execute([$message_id]);
            $msg = $stmt->fetch();
            
            if ($msg) {
                require_once 'auto_reply.php';
                if (sendAdminReply($msg['email'], $msg['name'], $msg['subject'], $reply_text)) {
                    $message_success = "Reply sent and saved successfully!";
                } else {
                    $message_error = "Reply saved to database, but email delivery failed. Please check SMTP settings.";
                }
            }
        } else {
            $message_error = "Reply cannot be empty.";
        }
    }
}

// Get all services
$services_list = $pdo->query("SELECT * FROM services ORDER BY order_num ASC, created_at DESC")->fetchAll();

// Get all blog posts
$blog_posts = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC")->fetchAll();

// Get all projects
$projects = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC")->fetchAll();

// Get all skills
$skills = $pdo->query("SELECT * FROM skills ORDER BY category, name")->fetchAll();

// Get all messages
$messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();

// Get stats for dashboard
$total_projects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$total_skills = $pdo->query("SELECT COUNT(*) FROM skills")->fetchColumn();
$total_messages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$unread_messages = $pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = FALSE")->fetchColumn();
?>
<?php
require_once 'database.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: blog.php');
    exit;
}

// Get recent posts for sidebar
$recent_posts = $pdo->query("SELECT * FROM blog_posts WHERE slug != '$slug' AND status = 'published' ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($post['excerpt']); ?>">
    <title><?php echo htmlspecialchars($post['title']); ?> - Soumya Portfolio</title>
    
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .blog-single {
            padding: 120px 0 80px;
        }
        .post-container {
            display: grid;
            grid-template-columns: 8fr 4fr;
            gap: 40px;
        }
        .post-header {
            margin-bottom: 30px;
        }
        .post-header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--dark-color);
        }
        .post-featured-image {
            width: 100%;
            height: 450px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
        }
        .post-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-color);
        }
        .post-content p {
            margin-bottom: 20px;
        }
        .post-content h2, .post-content h3 {
            margin: 30px 0 15px;
        }
        .post-content img {
            max-width: 100%;
            border-radius: 8px;
            margin: 20px 0;
        }
        .sidebar-widget {
            background: #fff;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }
        .sidebar-widget h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
        }
        .recent-post-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .recent-post-img {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
        }
        .recent-post-info h4 {
            font-size: 0.95rem;
            margin-bottom: 5px;
        }
        .recent-post-info span {
            font-size: 0.8rem;
            color: var(--gray-color);
        }
        
        @media (max-width: 992px) {
            .post-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="blog-single">
        <div class="container">
            <div class="post-container">
                <article class="post-main">
                    <div class="post-header">
                        <div class="blog-meta">
                            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($post['author']); ?></span>
                            <span><i class="fas fa-calendar"></i> <?php echo date('F d, Y', strtotime($post['created_at'])); ?></span>
                        </div>
                        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
                    </div>

                    <?php if ($post['featured_image']): ?>
                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" class="post-featured-image" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <?php endif; ?>

                    <div class="post-content">
                        <?php echo $post['content']; ?>
                    </div>
                </article>

                <aside class="post-sidebar">
                    <div class="sidebar-widget">
                        <h3>Recent Posts</h3>
                        <?php foreach ($recent_posts as $recent): ?>
                            <div class="recent-post-item">
                                <?php if ($recent['featured_image']): ?>
                                    <img src="<?php echo htmlspecialchars($recent['featured_image']); ?>" class="recent-post-img" alt="">
                                <?php endif; ?>
                                <div class="recent-post-info">
                                    <h4><a href="blog-post.php?slug=<?php echo $recent['slug']; ?>"><?php echo htmlspecialchars($recent['title']); ?></a></h4>
                                    <span><?php echo date('M d, Y', strtotime($recent['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="sidebar-widget">
                        <h3>Connect With Me</h3>
                        <div class="social-links" style="justify-content: flex-start;">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-github"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>

</html>

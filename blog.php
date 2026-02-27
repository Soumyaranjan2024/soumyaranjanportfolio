<?php
require_once 'database.php';

$blog_posts = $pdo->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Insights, tutorials, and updates from Soumya Ranjan Padhi, Web Developer & Designer.">
    <title>Blog - Soumya Portfolio</title>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .blog-archive {
            padding: 120px 0 80px;
            background-color: var(--light-color);
        }
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        .blog-post {
            background: #fff;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }
        .blog-post:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .blog-image {
            height: 220px;
            overflow: hidden;
        }
        .blog-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .blog-post:hover .blog-image img {
            transform: scale(1.1);
        }
        .blog-content {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .blog-content h3 {
            font-size: 1.4rem;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        .blog-content h3 a {
            color: var(--dark-color);
        }
        .blog-content h3 a:hover {
            color: var(--primary-color);
        }
        .blog-meta {
            font-size: 0.9rem;
            color: var(--gray-color);
            margin-bottom: 15px;
            display: flex;
            gap: 15px;
        }
        .blog-meta i {
            margin-right: 5px;
            color: var(--primary-color);
        }
        .blog-excerpt {
            margin-bottom: 20px;
            color: var(--text-color);
            line-height: 1.6;
        }
        .read-more {
            font-weight: 600;
            color: var(--primary-color);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: auto;
        }
        .read-more:hover {
            gap: 10px;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <section class="blog-archive">
        <div class="container">
            <div class="section-title">
                <h2>Latest from the Blog</h2>
                <div class="underline"></div>
                <p style="margin-top: 20px; color: var(--gray-color);">Tutorials, design trends, and my development journey.</p>
            </div>
            
            <?php if (count($blog_posts) > 0): ?>
                <div class="blog-grid">
                    <?php foreach ($blog_posts as $post): ?>
                        <article class="blog-post reveal">
                            <div class="blog-image">
                                <a href="blog-post.php?slug=<?php echo $post['slug']; ?>">
                                    <?php if ($post['featured_image']): ?>
                                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/600x400?text=No+Image" alt="No image available">
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="blog-content">
                                <div class="blog-meta">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($post['author']); ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                </div>
                                <h3><a href="blog-post.php?slug=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                                <p class="blog-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                                <a href="blog-post.php?slug=<?php echo $post['slug']; ?>" class="read-more">Read Full Story <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 100px 0;">
                    <i class="fas fa-newspaper" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
                    <h3>No blog posts yet.</h3>
                    <p>Check back soon for new content!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>

</html>

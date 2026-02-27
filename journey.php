<?php
require_once 'database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journey - Soumya Portfolio</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .journey-section { padding: 120px 0 80px; background: var(--light-color); overflow: hidden; }
        .timeline-container { position: relative; max-width: 1200px; margin: 80px auto; padding: 40px 0; }
        
        /* Tree Branch Visualization */
        .timeline-tree { position: absolute; left: 50%; top: 0; bottom: 0; width: 6px; background: #e2e8f0; transform: translateX(-50%); border-radius: 10px; }
        .timeline-tree::before { content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 20px; height: 20px; background: var(--primary-color); border-radius: 50%; box-shadow: 0 0 20px var(--primary-color); }
        .timeline-tree-progress { position: absolute; left: 0; top: 0; width: 100%; background: var(--primary-color); border-radius: 10px; transition: height 0.5s ease; height: 0; }

        .timeline-item { position: relative; width: 50%; padding: 40px; box-sizing: border-box; z-index: 1; }
        .timeline-item.left { left: 0; text-align: right; }
        .timeline-item.right { left: 50%; text-align: left; }

        /* Branch Nodes */
        .timeline-node { position: absolute; top: 50%; width: 24px; height: 24px; background: white; border: 4px solid var(--primary-color); border-radius: 50%; z-index: 2; transform: translateY(-50%); transition: var(--transition); }
        .left .timeline-node { right: -12px; }
        .right .timeline-node { left: -12px; }
        
        .timeline-item:hover .timeline-node { background: var(--primary-color); transform: translateY(-50%) scale(1.3); box-shadow: 0 0 15px var(--primary-color); }

        /* Branch Lines */
        .timeline-branch { position: absolute; top: 50%; height: 3px; background: #cbd5e1; width: 40px; transform: translateY(-50%); z-index: 0; transition: var(--transition); }
        .left .timeline-branch { right: 12px; }
        .right .timeline-branch { left: 12px; }
        .timeline-item:hover .timeline-branch { background: var(--primary-color); width: 50px; }

        .timeline-content { background: white; padding: 30px; border-radius: 20px; box-shadow: var(--box-shadow); transition: var(--transition); display: inline-block; max-width: 100%; position: relative; overflow: hidden; }
        .timeline-content::before { content: ''; position: absolute; top: 0; left: 0; width: 5px; height: 100%; background: var(--primary-color); opacity: 0; transition: var(--transition); }
        .left .timeline-content::before { left: auto; right: 0; }
        
        .timeline-item:hover .timeline-content { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .timeline-item:hover .timeline-content::before { opacity: 1; }

        .date-badge { display: inline-block; padding: 6px 15px; background: var(--primary-color); color: white; border-radius: 30px; font-size: 0.85rem; font-weight: 600; margin-bottom: 15px; }
        .timeline-content h3 { font-size: 1.4rem; margin-bottom: 8px; color: var(--dark-color); }
        .timeline-content h4 { font-size: 1rem; color: var(--primary-color); margin-bottom: 12px; font-weight: 500; }
        .timeline-content p { color: var(--gray-color); line-height: 1.6; font-size: 0.95rem; }

        @media screen and (max-width: 768px) {
            .timeline-tree { left: 30px; }
            .timeline-item { width: 100%; padding: 20px 20px 20px 60px; text-align: left !important; }
            .timeline-node { left: 18px !important; }
            .timeline-branch { left: 42px !important; width: 20px; }
            .timeline-item.right { left: 0; }
            .left .timeline-content::before { left: 0; right: auto; }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="journey-section">
        <div class="container">
            <div class="section-title">
                <h2>My Professional Journey</h2>
                <div class="underline"></div>
            </div>
            <div class="timeline-container">
                <div class="timeline-tree">
                    <div class="timeline-tree-progress" id="tree-progress"></div>
                </div>
                
                <?php
                $milestones = $pdo->query("SELECT * FROM journey ORDER BY order_num ASC")->fetchAll();
                foreach ($milestones as $m):
                ?>
                <div class="timeline-item <?php echo $m['position_side']; ?> reveal">
                    <div class="timeline-node"></div>
                    <div class="timeline-branch"></div>
                    <div class="timeline-content">
                        <span class="date-badge"><?php echo htmlspecialchars($m['year_range']); ?></span>
                        <h3><?php echo htmlspecialchars($m['title']); ?></h3>
                        <h4><?php echo htmlspecialchars($m['company']); ?></h4>
                        <p><?php echo htmlspecialchars($m['description']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php include 'footer.php'; ?>
    <script>
        window.addEventListener('scroll', () => {
            const tree = document.querySelector('.timeline-container');
            const progress = document.getElementById('tree-progress');
            if (tree && progress) {
                const rect = tree.getBoundingClientRect();
                const windowHeight = window.innerHeight;
                if (rect.top < windowHeight) {
                    const scrolled = windowHeight - rect.top;
                    const totalHeight = rect.height;
                    let percentage = (scrolled / totalHeight) * 100;
                    progress.style.height = Math.min(Math.max(percentage, 0), 100) + '%';
                }
            }
        });
    </script>
</body>
</html>

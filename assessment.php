<?php
require_once 'database.php';
$skills = $pdo->query("SELECT * FROM skills ORDER BY proficiency DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skill Assessment - Soumya Portfolio</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .assessment-section { padding: 120px 0 80px; background: #fff; }
        .category-nav { display: flex; justify-content: center; gap: 15px; margin: 40px 0; flex-wrap: wrap; }
        .cat-btn { padding: 10px 25px; border-radius: 30px; border: 2px solid var(--primary-color); background: transparent; color: var(--primary-color); font-weight: 600; cursor: pointer; transition: var(--transition); }
        .cat-btn.active, .cat-btn:hover { background: var(--primary-color); color: white; }
        
        .assessment-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; margin-top: 20px; }
        .skill-card { background: white; padding: 30px; border-radius: 20px; box-shadow: var(--box-shadow); text-align: center; transition: var(--transition); border: 1px solid #f1f5f9; position: relative; overflow: hidden; }
        .skill-card::after { content: ''; position: absolute; top: 0; right: 0; width: 40px; height: 40px; background: var(--primary-color); clip-path: polygon(100% 0, 0 0, 100% 100%); opacity: 0.1; }
        .skill-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); border-color: var(--primary-color); }
        
        .skill-icon-box { width: 70px; height: 70px; background: var(--light-color); border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: var(--primary-color); font-size: 1.8rem; transition: var(--transition); }
        .skill-card:hover .skill-icon-box { background: var(--primary-color); color: white; transform: rotate(10deg); }
        
        .skill-name { font-size: 1.3rem; font-weight: 700; margin-bottom: 5px; color: var(--dark-color); }
        .skill-cat-tag { font-size: 0.8rem; color: var(--gray-color); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; display: block; }
        
        .progress-circle { width: 100px; height: 100px; border-radius: 50%; background: conic-gradient(var(--primary-color) calc(var(--percentage) * 1%), #f1f5f9 0); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; position: relative; transition: var(--transition); }
        .progress-circle::after { content: attr(data-percent) '%'; position: absolute; width: 84px; height: 84px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; color: var(--dark-color); }
        
        .skill-desc { font-size: 0.9rem; color: var(--gray-color); line-height: 1.5; margin-bottom: 20px; height: 45px; overflow: hidden; }
        
        .badge { display: inline-block; padding: 6px 18px; border-radius: 30px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; }
        .expert { background: #ecfdf5; color: #059669; }
        .advanced { background: #eff6ff; color: #2563eb; }
        .intermediate { background: #fffbeb; color: #d97706; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="assessment-section">
        <div class="container">
            <div class="section-title">
                <h2>Skill Assessment Tool</h2>
                <div class="underline"></div>
                <p style="margin-top: 20px;">A comprehensive overview of my technical expertise and professional capabilities.</p>
            </div>

            <div class="category-nav">
                <button class="cat-btn active" data-filter="all">All Skills</button>
                <button class="cat-btn" data-filter="Frontend">Frontend</button>
                <button class="cat-btn" data-filter="Backend">Backend</button>
                <button class="cat-btn" data-filter="Database">Database</button>
                <button class="cat-btn" data-filter="Design">Design</button>
                <button class="cat-btn" data-filter="Other">Tools & Other</button>
            </div>

            <div class="assessment-grid">
                <?php foreach ($skills as $skill): 
                    $levelClass = $skill['proficiency'] >= 90 ? 'expert' : ($skill['proficiency'] >= 75 ? 'advanced' : 'intermediate');
                    $levelText = $skill['proficiency'] >= 90 ? 'Expert' : ($skill['proficiency'] >= 75 ? 'Advanced' : 'Intermediate');
                    
                    // Assign icons based on name
                    $icon = 'fas fa-code';
                    $name = strtolower($skill['name']);
                    if (strpos($name, 'html') !== false || strpos($name, 'css') !== false) $icon = 'fab fa-html5';
                    if (strpos($name, 'javascript') !== false || strpos($name, 'js') !== false) $icon = 'fab fa-js';
                    if (strpos($name, 'react') !== false) $icon = 'fab fa-react';
                    if (strpos($name, 'php') !== false) $icon = 'fab fa-php';
                    if (strpos($name, 'node') !== false) $icon = 'fab fa-node-js';
                    if (strpos($name, 'mysql') !== false || strpos($name, 'sql') !== false) $icon = 'fas fa-database';
                    if (strpos($name, 'figma') !== false) $icon = 'fab fa-figma';
                    if (strpos($name, 'git') !== false) $icon = 'fab fa-git-alt';
                    
                    // Descriptions
                    $desc = "Proficient in " . $skill['name'] . " for building scalable and efficient solutions.";
                    if ($skill['name'] == 'HTML5/CSS3') $desc = "Expert in semantic HTML and advanced CSS layouts including Flexbox and Grid.";
                    if ($skill['name'] == 'JavaScript (ES6+)') $desc = "Deep understanding of asynchronous programming, DOM manipulation, and modern syntax.";
                ?>
                <div class="skill-card reveal" data-category="<?php echo htmlspecialchars($skill['category']); ?>">
                    <div class="skill-icon-box"><i class="<?php echo $icon; ?>"></i></div>
                    <span class="skill-cat-tag"><?php echo htmlspecialchars($skill['category']); ?></span>
                    <span class="skill-name"><?php echo htmlspecialchars($skill['name']); ?></span>
                    <p class="skill-desc"><?php echo $desc; ?></p>
                    
                    <div class="progress-circle" style="--percentage: <?php echo $skill['proficiency']; ?>;" data-percent="<?php echo $skill['proficiency']; ?>"></div>
                    <span class="badge <?php echo $levelClass; ?>"><?php echo $levelText; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php include 'footer.php'; ?>
    <script>
        document.querySelectorAll('.cat-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const filter = btn.getAttribute('data-filter');
                document.querySelectorAll('.skill-card').forEach(card => {
                    if (filter === 'all' || card.getAttribute('data-category') === filter) {
                        card.style.display = 'block';
                        setTimeout(() => card.style.opacity = '1', 10);
                    } else {
                        card.style.opacity = '0';
                        setTimeout(() => card.style.display = 'none', 300);
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
require_once 'database.php';

// Get projects from database
$projects = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC")->fetchAll();

// Get skills from database
$skills = $pdo->query("SELECT * FROM skills ORDER BY category, name")->fetchAll();

// Group skills by category
$skillsByCategory = [];
foreach ($skills as $skill) {
    if (!isset($skillsByCategory[$skill['category']])) {
        $skillsByCategory[$skill['category']] = [];
    }
    $skillsByCategory[$skill['category']][] = $skill;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Professional portfolio of Soumya Ranjan Padhi, a web developer and designer creating responsive and user-friendly websites.">
    <meta name="keywords"
        content="Soumya Ranjan Padhi, web developer, front-end developer, web designer, portfolio, UI/UX, responsive design">
    <meta name="author" content="Soumya Ranjan Padhi">

    <title>Soumya portfolio</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Confetti Library -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <!-- Fun Interactions -->
    <script src="js/fun_interactions.js" defer></script>
</head>

<body>
    <button id="fix-it-btn" onclick="fixPortfolio()">Fix it! 🛠️</button>
    
    <div id="hire-modal" class="hire-me-modal">
        <h2>Hire Soumya? 🚀</h2>
        <p>You know you want to!</p>
        <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">
            <button onclick="handleHireClick('yes')" class="btn btn-primary">Yes, Let's go!</button>
            <button onclick="handleHireClick('no')" class="btn btn-secondary">No</button>
        </div>
    </div>

    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <section id="hero" class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Hi, I'm <span>Soumya Ranjan Padhi</span></h1>
                    <h2>Web Developer & Designer</h2>
                    <p>I craft responsive websites where technology meets creativity</p>
                    <div class="hero-btns">
                        <a href="javascript:void(0)" onclick="openHireModal()" class="btn btn-primary">Hire Me or Cry 😢</a>
                        <a href="#projects" class="btn btn-secondary">View My Work</a>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="profile-img">
                        <img src="uploads\projects\my.jpg" alt="Profile Image"
                            style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about section-padding reveal">
        <div class="container">
            <div class="section-title">
                <h2>About Me</h2>
                <div class="underline"></div>
            </div>
            <div class="hero-image">
                <div class="profile-img">
                    <img src="uploads/projects/me.jpg" alt="Profile Image"
                        style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
            </div>
            <br>
            <br>
            <div class="about-content">
                <div class="about-text">
                    <p>Hi, I'm Soumya Ranjan Padhi, a passionate student at GIET University. I'm a code enthusiast who
                        loves exploring new technologies, solving problems, and constantly learning in the world of
                        programming and development. Whether it's building something from scratch or debugging tricky
                        code, I enjoy the process and the challenges it brings</p>
                    <p>I specialize in creating high-performance, responsive websites using modern technologies. With a strong foundation in both frontend and backend development, I bridge the gap between complex logic and beautiful design. My goal is to build digital experiences that are not only visually stunning but also highly functional and user-centric.</p>
                    <p>When I'm not coding, you can find me exploring new design trends, contributing to open-source projects, or sharing my journey on my blog. I am always open to new opportunities and collaborations that challenge my skills and help me grow as a professional developer.</p>
                </div>

                <div class="about-details">
                    <div class="detail-item">
                        <h3>Name:</h3>
                        <p>Soumya Ranjan Padhi</p>
                    </div>
                    <div class="detail-item">
                        <h3>Email:</h3>
                        <p>soumyaranjanpadhi936@gmail.com</p>
                    </div>
                    <div class="detail-item">
                        <h3>Based in:</h3>
                        <p>Bhubaneswar, India</p>
                    </div>
                    <div class="detail-item">
                        <h3>Freelance:</h3>
                        <p>Available</p>
                    </div>

                    <a href="images/soumya-ranjan-padhi-cv.pdf" class="btn btn-primary"
                        download="Soumya_Ranjan_Padhi_CV.pdf">Download CV</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Skills Section - Now Dynamic -->
    <section id="skills" class="skills section-padding reveal">
        <div class="container">
            <div class="section-title">
                <h2>My Skills</h2>
                <div class="underline"></div>
            </div>
            <div class="skills-content">
                <div class="skill-categories">
                    <?php foreach ($skillsByCategory as $category => $categorySkills): ?>
                        <div class="skill-category">
                            <h3><?php echo htmlspecialchars(ucfirst($category)); ?></h3>
                            <div class="skills-list">
                                <?php foreach ($categorySkills as $skill): ?>
                                <div class="skill-item">
                                    <div class="skill-info">
                                        <span><?php echo htmlspecialchars($skill['name']); ?></span>
                                        <span><?php echo htmlspecialchars($skill['proficiency']); ?>%</span>
                                    </div>
                                    <div class="skill-bar">
                                        <div class="skill-progress" style="--progress: <?php echo htmlspecialchars($skill['proficiency']); ?>%;"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Projects Section - Now Dynamic -->
    <section id="projects" class="projects section-padding reveal">
        <div class="container">
            <div class="section-title">
                <h2>My Projects</h2>
                <div class="underline"></div>
            </div>
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="web">Web Design</button>
                <button class="filter-btn" data-filter="app">App Design</button>
                <button class="filter-btn" data-filter="ui">UI/UX</button>

            </div>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-item" data-category="<?php echo htmlspecialchars($project['category']); ?>">
                        <div class="project-img img-skeleton">
                            <?php if ($project['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($project['image_url']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" loading="lazy">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x300?text=Project+Preview" alt="No image available" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="project-info">
                            <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                            <p><?php echo htmlspecialchars($project['description']); ?></p>
                            <div class="project-tags">
                                <?php
                                $tags = explode(',', $project['tags']);
                                foreach ($tags as $tag):
                                    ?>
                                    <span><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="project-links">
                                <a href="<?php echo htmlspecialchars($project['live_link'] ?? '#'); ?>" class="btn-sm btn-primary" target="_blank"><i class="fas fa-external-link-alt"></i> Demo</a>
                                <a href="<?php echo htmlspecialchars($project['github_link'] ?? '#'); ?>" class="btn-sm btn-secondary" target="_blank"><i class="fab fa-github"></i> Code</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact section-padding reveal">
        <div class="container">
            <div class="section-title">
                <h2>Contact Me</h2>
                <div class="underline"></div>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <h3>Let's Connect</h3>
                    <p>I'm always open to discussing new projects, creative ideas or opportunities to be part of your
                        vision.</p>
                    <div class="contact-details">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Location</h4>
                                <p>bhubaneswar, india</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email</h4>
                                <p>soumyaranjanpadhi936@gmail.com</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h4>Phone</h4>
                                <p>+91 7205574037</p>
                            </div>
                        </div>
                    </div>
                    <div class="social-links">
                        <a href="https://www.linkedin.com/in/soumya-ranjan-padhi-5b5a6a304?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app"
                            aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://github.com/Soumyaranjan2024" aria-label="GitHub"><i
                                class="fab fa-github"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.instagram.com/s0umyya_?igsh=cHdqaTdvY3doczFv" aria-label="Instagram"><i
                                class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <form id="contactForm" action="submit_contact.php" method="POST">
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" placeholder="What's this about?">
                    </div>

                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" rows="5" required
                            placeholder="Tell me about your project or inquiry..."></textarea>
                    </div>

                    <button type="submit" id="submitBtn">
                        <span id="btnText">Send Message</span>
                        <span id="btnLoader" style="display: none;">Sending...</span>
                    </button>
                </form>

                <div id="contactMessage" style="display: none; margin-top: 15px; padding: 15px; border-radius: 4px;">
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>

</html>
<?php
require_once 'database.php';

echo "Populating professional content...\n";

try {
    // 1. Populate Services
    $pdo->exec("DELETE FROM services");
    $services = [
        ['Web Development', 'fas fa-code', 'Custom websites built with modern PHP, MySQL, and JavaScript frameworks for performance and scalability.', 1],
        ['UI/UX Design', 'fas fa-paint-brush', 'Creating intuitive user interfaces and engaging user experiences using industry-standard design principles.', 2],
        ['Mobile App Development', 'fas fa-mobile-alt', 'Cross-platform mobile applications that provide seamless performance across iOS and Android devices.', 3],
        ['SEO Optimization', 'fas fa-search', 'Optimizing website structure and content to improve search engine visibility and organic traffic.', 4],
        ['API Integration', 'fas fa-plug', 'Connecting your application with third-party services like Payment Gateways, Social Media, and Maps.', 5],
        ['Maintenance & Support', 'fas fa-tools', 'Providing continuous updates, security patches, and technical support to keep your site running smoothly.', 6]
    ];
    $stmt = $pdo->prepare("INSERT INTO services (title, icon, description, order_num) VALUES (?, ?, ?, ?)");
    foreach ($services as $service) {
        $stmt->execute($service);
    }
    echo "Services populated.\n";

    // 2. Populate Projects with high-quality images
    $pdo->exec("DELETE FROM projects");
    $projects = [
        [
            'E-commerce Platform',
            'A full-featured e-commerce solution with product management, cart, and secure checkout.',
            'https://images.unsplash.com/photo-1557821552-17105176677c?q=80&w=1000&auto=format&fit=crop',
            'web',
            'PHP, MySQL, Stripe API',
            '#',
            '#'
        ],
        [
            'Task Management App',
            'A productivity tool for teams to collaborate and track project progress in real-time.',
            'https://images.unsplash.com/photo-1540350394557-8d14678e7f91?q=80&w=1000&auto=format&fit=crop',
            'app',
            'React, Node.js, MongoDB',
            '#',
            '#'
        ],
        [
            'Fitness Tracker UI',
            'Modern UI design for a fitness application focusing on user metrics and goal tracking.',
            'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=1000&auto=format&fit=crop',
            'ui',
            'Figma, Adobe XD',
            '#',
            '#'
        ]
    ];
    $stmt = $pdo->prepare("INSERT INTO projects (title, description, image_url, category, tags, live_link, github_link) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($projects as $project) {
        $stmt->execute($project);
    }
    echo "Projects populated.\n";

    // 3. Populate Skills
    $pdo->exec("DELETE FROM skills");
    $skills = [
        ['Frontend', 'HTML5/CSS3', 95],
        ['Frontend', 'JavaScript (ES6+)', 90],
        ['Frontend', 'React.js', 85],
        ['Frontend', 'Next.js', 80],
        ['Frontend', 'Tailwind CSS', 90],
        ['Backend', 'PHP (Laravel/Core)', 92],
        ['Backend', 'Node.js', 80],
        ['Backend', 'Python (Flask)', 75],
        ['Database', 'MySQL/PostgreSQL', 88],
        ['Database', 'MongoDB', 82],
        ['Design', 'Figma', 85],
        ['Design', 'Adobe XD', 80],
        ['Other', 'Git/GitHub', 90],
        ['Other', 'Docker', 70],
        ['Other', 'AWS (Basic)', 65]
    ];
    $stmt = $pdo->prepare("INSERT INTO skills (category, name, proficiency) VALUES (?, ?, ?)");
    foreach ($skills as $skill) {
        $stmt->execute($skill);
    }
    echo "Skills populated.\n";

    // 3. Populate Journey
    $pdo->exec("DELETE FROM journey");
    $milestones = [
        ['2023 - Present', 'Full Stack Developer', 'GIET University (Tech Team)', 'Leading the development of performance-optimized web applications. Mentoring junior developers and implementing clean code architectures using React and PHP.', 'left', 1],
        ['2022 - 2023', 'UI/UX Designer Intern', 'Digital Solutions Inc.', 'Collaborated with product teams to design high-fidelity prototypes. Reduced user friction by 30% through iterative usability testing and design systems.', 'right', 2],
        ['2021 - 2022', 'Frontend Specialist', 'Freelance / Open Source', 'Specialized in building responsive, accessible interfaces. Contributed to various open-source projects and delivered 15+ custom client websites.', 'left', 3],
        ['2020 - 2021', 'Early Tech Explorer', 'Personal Projects', 'Started my engineering journey. Built my first automated script and fell in love with the power of software to solve real-world problems.', 'right', 4]
    ];
    $stmt = $pdo->prepare("INSERT INTO journey (year_range, title, company, description, position_side, order_num) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($milestones as $m) {
        $stmt->execute($m);
    }
    echo "Journey milestones populated.\n";

    echo "Content population successful!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

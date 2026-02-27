<?php
require_once 'database.php';

// Get services (static for now, can be dynamic later)
$services = [
    [
        'icon' => 'fas fa-code',
        'title' => 'Web Development',
        'description' => 'Building responsive, fast, and secure websites using modern technologies like PHP, MySQL, and JavaScript.'
    ],
    [
        'icon' => 'fas fa-paint-brush',
        'title' => 'UI/UX Design',
        'description' => 'Creating intuitive and engaging user interfaces with a focus on user experience and aesthetic appeal.'
    ],
    [
        'icon' => 'fas fa-mobile-alt',
        'title' => 'Mobile-First Design',
        'description' => 'Ensuring your website looks and performs perfectly on all devices, from smartphones to desktops.'
    ],
    [
        'icon' => 'fas fa-search',
        'title' => 'SEO Optimization',
        'description' => 'Optimizing your website to rank higher in search results and attract more organic traffic.'
    ],
    [
        'icon' => 'fas fa-envelope',
        'title' => 'Email Systems',
        'description' => 'Implementing robust email notification and bulk email systems for better client engagement.'
    ],
    [
        'icon' => 'fas fa-tools',
        'title' => 'Maintenance & Support',
        'description' => 'Providing ongoing updates, security patches, and technical support to keep your site running smoothly.'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - Soumya Portfolio</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .services-page { background: #f8fafc; }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 35px;
            margin-top: 60px;
        }
        .service-card {
            background: #fff;
            padding: 50px 40px;
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #f1f5f9;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            transform: scaleX(0);
            transition: transform 0.4s ease;
            transform-origin: left;
        }
        .service-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            border-color: var(--primary-color);
        }
        .service-card:hover::before { transform: scaleX(1); }
        
        .service-icon-wrapper {
            width: 90px;
            height: 90px;
            background: #eff6ff;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: var(--primary-color);
            font-size: 2.2rem;
            transition: all 0.4s ease;
        }
        .service-card:hover .service-icon-wrapper {
            background: var(--primary-color);
            color: white;
            transform: rotateY(360deg);
        }
        
        .service-card h3 {
            font-size: 1.6rem;
            margin-bottom: 15px;
            color: var(--dark-color);
            font-weight: 700;
        }
        .service-card p {
            color: var(--gray-color);
            line-height: 1.7;
            margin-bottom: 30px;
            font-size: 1rem;
        }
        .service-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 25px;
            display: block;
        }
        .service-price span {
            font-size: 0.9rem;
            color: var(--gray-color);
            font-weight: 400;
        }
        .btn-service {
            padding: 12px 25px;
            border-radius: 12px;
            background: var(--light-color);
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            border: 1px solid var(--primary-color);
        }
        .service-card:hover .btn-service {
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="services-page section-padding">
        <div class="container">
            <div class="section-title">
                <h2>My Services</h2>
                <div class="underline"></div>
                <p style="text-align: center; max-width: 700px; margin: 20px auto 0; color: #64748b;">
                    I offer a wide range of digital services to help businesses and individuals establish a strong online presence.
                </p>
            </div>

            <div class="services-grid">
                <?php 
                $srv_stmt = $pdo->query("SELECT * FROM services ORDER BY order_num ASC, created_at DESC");
                $db_services = $srv_stmt->fetchAll();
                
                // Static prices and CTA links for static and dynamic services
                $service_meta = [
                    'Web Development' => ['price' => '$999', 'link' => '#contact'],
                    'UI/UX Design' => ['price' => '$599', 'link' => '#contact'],
                    'Mobile App Development' => ['price' => '$1499', 'link' => '#contact'],
                    'SEO Optimization' => ['price' => '$299', 'link' => '#contact'],
                    'API Integration' => ['price' => '$499', 'link' => '#contact'],
                    'Maintenance & Support' => ['price' => '$199', 'link' => '#contact'],
                    'Mobile-First Design' => ['price' => '$499', 'link' => '#contact'],
                    'Email Systems' => ['price' => '$399', 'link' => '#contact'],
                ];

                if (count($db_services) > 0):
                    foreach ($db_services as $service): 
                        $meta = $service_meta[$service['title']] ?? ['price' => 'Custom', 'link' => '#contact'];
                    ?>
                        <div class="service-card reveal">
                            <div>
                                <div class="service-icon-wrapper">
                                    <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                                </div>
                                <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                                <p><?php echo htmlspecialchars($service['description']); ?></p>
                            </div>
                            <div>
                                <span class="service-price">Starting from <?php echo $meta['price']; ?> <span>/ project</span></span>
                                <a href="<?php echo $meta['link']; ?>" class="btn-service">Get Started</a>
                            </div>
                        </div>
                    <?php endforeach; 
                else:
                    // Fallback to static services if DB is empty
                    foreach ($services as $service): 
                        $meta = $service_meta[$service['title']] ?? ['price' => 'Custom', 'link' => '#contact'];
                    ?>
                        <div class="service-card reveal">
                            <div>
                                <div class="service-icon-wrapper">
                                    <i class="<?php echo $service['icon']; ?>"></i>
                                </div>
                                <h3><?php echo $service['title']; ?></h3>
                                <p><?php echo $service['description']; ?></p>
                            </div>
                            <div>
                                <span class="service-price">Starting from <?php echo $meta['price']; ?> <span>/ project</span></span>
                                <a href="<?php echo $meta['link']; ?>" class="btn-service">Get Started</a>
                            </div>
                        </div>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>

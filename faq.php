<?php
require_once 'database.php';

// FAQ data
$faqs = [
    [
        'question' => 'How can I hire you for a project?',
        'answer' => 'You can hire me by filling out the contact form on the home page or by sending me an email directly. I will get back to you within 24 hours.'
    ],
    [
        'question' => 'What technologies do you use for web development?',
        'answer' => 'I primarily use PHP, MySQL, JavaScript, HTML5, and CSS3. I am also proficient in modern frameworks like Bootstrap and can work with various CMS platforms.'
    ],
    [
        'question' => 'Do you offer custom designs?',
        'answer' => 'Yes, I offer fully custom designs tailored to your specific needs and brand identity. I don\'t just use templates; I build unique experiences.'
    ],
    [
        'question' => 'How much do you charge for a website?',
        'answer' => 'Pricing depends on the complexity and scope of the project. Please contact me with your project details for a customized quote.'
    ],
    [
        'question' => 'Can you help with website maintenance?',
        'answer' => 'Absolutely! I provide ongoing maintenance and support services to ensure your website stays up-to-date, secure, and performing at its best.'
    ],
    [
        'question' => 'Is your work mobile-responsive?',
        'answer' => 'Yes, every website I build is designed to be fully responsive and optimized for all devices, including smartphones, tablets, and desktops.'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Soumya Portfolio</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .faq-list {
            max-width: 800px;
            margin: 50px auto 0;
        }
        .faq-item {
            background: #fff;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .faq-question {
            padding: 20px 25px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: #1e293b;
            transition: all 0.3s ease;
        }
        .faq-question:hover {
            background-color: #f8fafc;
        }
        .faq-question i {
            transition: transform 0.3s ease;
            color: #667eea;
        }
        .faq-answer {
            padding: 0 25px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: #64748b;
            line-height: 1.6;
        }
        .faq-item.active .faq-answer {
            padding: 15px 25px 25px;
            max-height: 300px;
        }
        .faq-item.active .faq-question {
            background-color: #f8fafc;
            color: #667eea;
        }
        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="faq-page section-padding">
        <div class="container">
            <div class="section-title">
                <h2>Frequently Asked Questions</h2>
                <div class="underline"></div>
                <p style="text-align: center; max-width: 700px; margin: 20px auto 0; color: #64748b;">
                    Find answers to common questions about my work, process, and services.
                </p>
            </div>

            <div class="faq-list">
                <?php foreach ($faqs as $index => $faq): ?>
                    <div class="faq-item reveal">
                        <div class="faq-question">
                            <span><?php echo $faq['question']; ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p><?php echo $faq['answer']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script>
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const item = question.parentElement;
                
                // Close other items
                document.querySelectorAll('.faq-item').forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
                
                item.classList.toggle('active');
            });
        });
    </script>
</body>
</html>

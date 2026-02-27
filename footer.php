<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-logo">
                <a href="index.php#hero">its<span>Soumya</span></a>
                <p>Building digital experiences that matter.</p>
                <!-- Easter Egg -->
                <button class="easter-egg-btn" onclick="breakPortfolio()" title="Easter Egg">
                    <i class="fas fa-ghost"></i>
                </button>
            </div>
            <p>&copy; <?php echo date('Y'); ?> Soumya Ranjan Padhi All Rights Reserved.</p>
        </div>
    </div>
</footer>

<script>
    // Mobile Menu Toggle
    const mobileMenu = document.getElementById('mobile-menu');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenu) {
        mobileMenu.addEventListener('click', function () {
            this.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
    }

    // Close mobile menu when a link is clicked
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (mobileMenu) {
                mobileMenu.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });
    });

    // Reveal animations on scroll
    const revealElements = document.querySelectorAll('section, .card, .project-item, .blog-post, .service-card, .reveal, .timeline-item');
    const revealOnScroll = () => {
        revealElements.forEach(el => {
            const rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight * 0.85) {
                el.classList.add('reveal-active');
            }
        });
    };
    window.addEventListener('scroll', revealOnScroll);
    revealOnScroll(); // Initial check

    // AJAX Contact Form
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnLoader = document.getElementById('btnLoader');
            const messageDiv = document.getElementById('contactMessage');

            // Show loading state
            submitBtn.disabled = true;
            if (btnText) btnText.style.display = 'none';
            if (btnLoader) btnLoader.style.display = 'inline';

            const formData = new FormData(this);
            formData.append('ajax', '1');

            fetch('submit_contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Show message
                messageDiv.style.display = 'block';
                messageDiv.innerHTML = data.message;

                if (data.success) {
                    messageDiv.style.backgroundColor = '#d4edda';
                    messageDiv.style.color = '#155724';
                    messageDiv.style.border = '1px solid #c3e6cb';
                    this.reset(); // Clear form
                } else {
                    messageDiv.style.backgroundColor = '#f8d7da';
                    messageDiv.style.color = '#721c24';
                    messageDiv.style.border = '1px solid #f5c6cb';
                }

                // Scroll to message
                messageDiv.scrollIntoView({behavior: 'smooth' });
            })
            .catch(error => {
                messageDiv.style.display = 'block';
                messageDiv.innerHTML = 'Error: ' + error.message;
                messageDiv.style.backgroundColor = '#f8d7da';
                messageDiv.style.color = '#721c24';
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                if (btnText) btnText.style.display = 'inline';
                if (btnLoader) btnLoader.style.display = 'none';
            });
        });
    }

    // Check for URL parameters on page load
    window.addEventListener('load', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const contact = urlParams.get('contact');
        const msg = urlParams.get('msg');

        if (contact && msg) {
            const messageDiv = document.getElementById('contactMessage');
            if (messageDiv) {
                messageDiv.style.display = 'block';
                messageDiv.innerHTML = decodeURIComponent(msg);

                if (contact === 'success') {
                    messageDiv.style.backgroundColor = '#d4edda';
                    messageDiv.style.color = '#155724';
                    messageDiv.style.border = '1px solid #c3e6cb';
                } else {
                    messageDiv.style.backgroundColor = '#f8d7da';
                    messageDiv.style.color = '#721c24';
                    messageDiv.style.border = '1px solid #f5c6cb';
                }
            }
        }
    });

    // Project filtering
    const filterButtons = document.querySelectorAll('.filter-btn');
    const projectItems = document.querySelectorAll('.project-item');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            button.classList.add('active');

            const filterValue = button.getAttribute('data-filter');

            projectItems.forEach(item => {
                if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
</script>

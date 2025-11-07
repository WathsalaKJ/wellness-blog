<?php
/**
 * Categories Page
 * Displays blog posts filtered by category
 */

session_start();
require_once 'config/config.php';
require_once 'config/database.php';

$categories = [
    [
        'name' => 'Yoga Practices',
        'description' => 'Calm your mind, find peace',
        'icon' => '6684e66d2c36507218ac2fc8_service-thumbnail-img-4.png'
    ],
    [
        'name' => 'Meditation',
        'description' => 'Inner stillness and clarity',
        'icon' => '6684e40542090cadd7784c8c_service-thumbnail-img-1.png'
    ],
    [
        'name' => 'Nutrition',
        'description' => 'Wholesome recipes & Ayurveda',
        'icon' => '66a86a79e8d3d468816a493d_why-choose-us-img-6.png'
    ],
    
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore SoulBalance blog categories - Yoga, Meditation, Nutrition, Wellness, and more wellness topics.">
    <meta name="keywords" content="categories, yoga, meditation, nutrition">
    <meta name="author" content="SoulBalance">
    <meta property="og:title" content="Blog Categories - SoulBalance">
    <meta property="og:description" content="Browse wellness content by category.">
    <meta property="og:type" content="website">
    <title>Blog Categories - SoulBalance Wellness</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='45' fill='%23ff6f00'/><text x='50' y='60' font-size='50' font-weight='bold' text-anchor='middle' fill='white' font-family='serif'>S</text></svg>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Source+Sans+Pro:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <header class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>SoulBalance</h1>
            </div>
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="index.php">Blog</a>
                <a href="categories.php" class="active">Categories</a>
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
            </nav>
            <div class="nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-info">
                        <span class="username">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="dashboard.php" class="btn btn-primary btn-sm">Dashboard</a>
                        <a href="logout.php" class="btn btn-secondary btn-sm logout-link">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-sm">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Breadcrumb Hero Section -->
    <section class="breadcrumb-hero">
        <div class="container">
            <div class="breadcrumb-content">
                <div class="breadcrumb">
                    <img src="assets/images/about-hero.jpg" alt="Our wellness community practicing together">
                    <a href="index.php">Home</a>
                    <span>&gt;&gt;</span>
                    <span>Categories</span>
                </div>
                <h1 class="page-title">Blog <span>Categories</span></h1>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Categories Grid -->
            <section class="categories-section">
                <div class="categories-grid">
                    <?php foreach ($categories as $index => $category): ?>
                        <div class="category-card fade-in" style="animation-delay: <?php echo ($index * 0.1); ?>s;">
                            <div class="category-icon">
                                <img src="assets/images/<?php echo htmlspecialchars($category['icon']); ?>" 
                                     alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                     onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\"fas fa-spa\"></i>';">
                            </div>
                            <h3 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                            <a href="index.php?category=<?php echo urlencode($category['name']); ?>" class="category-read-more">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <!-- Enhanced Footer -->
    <footer class="footer-enhanced">
        <div class="container">
            <div class="footer-content">
                <!-- Brand & Description -->
                <div class="footer-brand">
                    <h2>
                        <span class="brand-soul">Soul</span><span class="brand-balance">Balance</span>
                    </h2>
                    <p>We are passionate about creating a space where every body is family.</p>
                    
                    <div class="footer-address">
                        <strong>Address:</strong>
                        <p>University of Moratuwa,<br>Katubedda,<br>Colombo</p>
                    </div>
                    
                    <div class="footer-contact">
                        <strong>Contact Details:</strong>
                        <p>0711234568<br>0112556984<br>soulblance@gmail.com</p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <div class="footer-links">
                        <a href="index.php">Home</a>
                        <a href="index.php#blog">Blog</a>
                        <a href="categories.php">Categories</a>
                        <a href="about.php">About</a>
                    </div>
                </div>

                <!-- Explore -->
                <div class="footer-column">
                    <h3>Explore</h3>
                    <div class="footer-links">
                        <a href="categories.php?cat=yoga">Yoga Practices</a>
                        <a href="categories.php?cat=meditation">Meditation & Mindfulness</a>
                        <a href="categories.php?cat=nutrition">Nutrition & Ayurveda</a>
                    </div>
                </div>

                <!-- Community -->
                <div class="footer-column">
                    <h3>Community</h3>
                    <div class="footer-links">
                        <a href="about.php">Join Us</a>
                        <a href="register.php">Create Account</a>
                        <a href="dashboard.php">Write for Us</a>
                        <a href="#faq">FAQ</a>
                    </div>
                </div>
            </div>

            <!-- Decorative Image -->
            <div class="footer-decoration">
                <img src="assets/images/footer-decoration.jpg" alt="Yoga decoration" loading="lazy" onerror="this.parentElement.style.display='none'">
            </div>
        </div>

        <!-- Copyright Bar -->
        <div class="footer-bottom">
            <p>© 2025, SoulBalance.com</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all animated elements
        document.querySelectorAll('.fade-in, .scale-in, .slide-in-left, .slide-in-right').forEach(el => {
            observer.observe(el);
        });

        // Add loading animation for category cards
        document.addEventListener('DOMContentLoaded', function() {
            const categoryCards = document.querySelectorAll('.category-card');
            
            categoryCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>
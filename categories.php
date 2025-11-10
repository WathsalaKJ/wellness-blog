<?php
/**
 * Categories Page
 * Displays blog posts filtered by category
 */

session_start();
require_once 'config/config.php';
require_once 'config/database.php';

try {
    $db = getDB();
    $stmt = $db->query("
        SELECT category, COUNT(*) as count 
        FROM blogPost 
        WHERE category IS NOT NULL 
        GROUP BY category
    ");
    $categoryCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    $categoryCounts = [];
}

$categories = [
    [
        'name' => 'Yoga Practices',
        'description' => 'Calm your mind, find peace',
        'icon' => '6684e66d2c36507218ac2fc8_service-thumbnail-img-4.png',
        'count' => $categoryCounts['Yoga Practices'] ?? 0
    ],
    [
        'name' => 'Meditation',
        'description' => 'Inner stillness and clarity',
        'icon' => '6684e40542090cadd7784c8c_service-thumbnail-img-1.png',
        'count' => $categoryCounts['Meditation'] ?? 0
    ],
    [
        'name' => 'Nutrition',
        'description' => 'Wholesome recipes & Ayurveda',
        'icon' => '66a86a79e8d3d468816a493d_why-choose-us-img-6.png',
        'count' => $categoryCounts['Nutrition'] ?? 0
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
            <img src="assets/images/logo.jpg" alt="SoulBalance Logo" class="nav-logo">
            <h1>SoulBalance</h1>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="active">Home</a>
            <a href="latest_blogs.php">Blog</a>
            <a href="categories.php">Categories</a>
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

   <!-- Enhanced Breadcrumb Hero Section - Same Style as About Page -->
<section class="breadcrumb-hero">
    <img src="assets/images/about-hero.jpg" alt="Wellness background" onerror="this.src='assets/images/about-hero.jpg'">
    <div class="container">
        <div class="breadcrumb-content">
            <h1 class="page-title">Blog <span>Categories</span></h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> <span>&gt;&gt;Categories</span>
            </div>
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
                        <div class="category-content">
                        <div class="category-icon">
                            <img src="assets/images/<?php echo htmlspecialchars($category['icon']); ?>" 
                                 alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                 onerror="this.style.display='none';">
                        </div>
                        
                        <h3 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                        <p class="category-count">
                            <?php echo $category['count']; ?> <?php echo $category['count'] === 1 ? 'post' : 'posts'; ?>
                        </p>        
                        
                        <a href="category_posts.php?category=<?php echo urlencode($category['name']); ?>" class="category-read-more">
                            Read More <i class="fas fa-arrow-right"></i>
                        </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

     <footer class="footer">
        <?php include 'includes/footer.php'; ?>
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
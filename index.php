<?php
/**
 * Home Page / Blog Listing
 * Displays all blog posts with brief previews
 */

session_start();
require_once 'config/config.php';
require_once 'config/database.php';

// Get all blog posts with author information
try {
    $db = getDB();
    
    // Pagination setup
    $postsPerPage = 9;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $postsPerPage;
    
    // Get total posts count
    $countStmt = $db->query("SELECT COUNT(*) as total FROM blogPost");
    $totalPosts = $countStmt->fetch()['total'];
    $totalPages = ceil($totalPosts / $postsPerPage);
    
    // Get posts for current page
    // Get posts for current page
   $stmt = $db->prepare("
    SELECT bp.id, bp.title, bp.content, bp.category, bp.featured_image, bp.created_at, u.username, u.id as user_id
    FROM blogPost bp
    JOIN user u ON bp.user_id = u.id
    ORDER BY bp.created_at DESC
    LIMIT ? OFFSET ?
  ");
    $stmt->bindValue(1, $postsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = "Failed to load posts";
    $posts = [];
}

// Helper function to create post preview (first 150 characters)
function getPreview($content, $length = 150) {
    $text = strip_tags($content);
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

// Helper function to format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

$categories = ['Yoga', 'Meditation', 'Nutrition'];


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Enhanced meta tags for SEO and social sharing -->
    <meta name="description" content="SoulBalance - Your wellness journey starts here. Explore yoga practices, meditation, nutrition guides, and mindfulness techniques for a healthier lifestyle.">
    <meta name="keywords" content="yoga, wellness, meditation, nutrition, mindfulness">
    <meta name="author" content="SoulBalance">
    <meta property="og:title" content="SoulBalance - Wellness Blog">
    <meta property="og:description" content="Transform your life with yoga, meditation, and wellness tips.">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <title>SoulBalance - Your Wellness Journey Starts Here</title>
    <!-- Added favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='45' fill='%23ff7a00'/><text x='50' y='60' font-size='50' font-weight='bold' text-anchor='middle' fill='white' font-family='serif'>S</text></svg>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
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

<!-- Enhanced Hero Section - Matching Figma Design -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <!-- Left Side - Image Circle -->
            <div class="hero-image-circle slide-in-left">
                <img src="assets/images/hero-yoga.png" alt="Yoga lifestyle" loading="eager">
            </div>
            
            <!-- Right Side - Text Content with Background Shape -->
            <div class="hero-text slide-in-right">
                <!-- Decorative Background Shape -->
                <div class="hero-text-decoration">
                    <img src="assets/images/testimonial-background.png" alt="Decorative shape" loading="lazy">
                </div>
                
                <p class="hero-tagline">ELEVATE YOUR WELL BEING</p>
                <h1>Start a healthy way of life, today!</h1>
                <p class="hero-description">We believe in the strength of connection; connection with your physical self and connection to the greater world.</p>
                <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'register.php'; ?>" class="btn btn-primary hero-btn">Get Start</a>
            </div>
        </div>
        
        <!-- Testimonial Quote - Below Content -->
        <div class="hero-testimonial fade-in">
            <div class="testimonial-avatar">
                <img src="assets/images/thumbnail-5.png" alt="Testimonial" loading="lazy">
            </div>
            <blockquote>
                <svg class="quote-icon" width="22" height="22" viewBox="0 0 22 22" fill="#ff6f00">
                    <path d="M9.5 4L4 9.5L9.5 15V11C14 11 17 13 18.5 17C17.5 12.5 15 8 9.5 7V4Z"/>
                </svg>
                <p>Find harmony in body and mind with our yoga and fitness workshop.</p>
            </blockquote>
        </div>
    </div>
</section>

<!-- Latest Blog Section with Hero and Box -->
<section class="latest-blog-section">
    <!-- Hero Section -->
    <div class="latest-blog-hero">
        <img src="assets/images/blog-hero-bg.jpg" alt="Blog background" class="latest-blog-hero-image" onerror="this.src='assets/images/about-hero.jpg'">
        <div class="latest-blog-hero-content fade-in">
            <h2 class="latest-blog-title">Featured <span>Blog</span></h2>
            <div class="latest-blog-breadcrumb">
                <a href="index.php">Home</a>
                <span>&gt;&gt;</span>
                <span>Blog</span>
            </div>
        </div>
    </div>

    <!-- Blog Content -->
    <div class="latest-blog-content">
        <?php
        try {
            $db = getDB();
            $stmt = $db->prepare("
                SELECT bp.id, bp.title, bp.content, bp.featured_image, bp.created_at, u.username
                FROM blogPost bp
                JOIN user u ON bp.user_id = u.id
                ORDER BY bp.created_at DESC
                LIMIT 1
            ");
            $stmt->execute();
            $latestPost = $stmt->fetch();

            if ($latestPost):
                $preview = strlen($latestPost['content']) > 300 ? substr(strip_tags($latestPost['content']), 0, 300) . '...' : strip_tags($latestPost['content']);
        ?>
            <div class="blog-post-container fade-in">
    <div class="blog-post-text">
        <h3 class="blog-post-heading"><?php echo htmlspecialchars($latestPost['title']); ?></h3>
        <p class="blog-post-description"><?php echo htmlspecialchars($preview); ?></p>
        <a href="view_blog.php?id=<?php echo $latestPost['id']; ?>" class="blog-read-more">
            Read More →
        </a>
    </div>
    <div class="blog-post-image">
        <?php if (!empty($latestPost['featured_image']) && file_exists($latestPost['featured_image'])): ?>
            <img src="<?php echo htmlspecialchars($latestPost['featured_image']); ?>" alt="<?php echo htmlspecialchars($latestPost['title']); ?>" loading="lazy">
        <?php else: ?>
            <img src="assets/images/blog-hero-bg.jpg" alt="<?php echo htmlspecialchars($latestPost['title']); ?>" loading="lazy">
        <?php endif; ?>
    </div>
</div>
            </div>
        <?php 
            else:
                echo '<p style="text-align: center; padding: 60px 20px; color: #ffffff;">No blog posts available yet.</p>';
            endif;
        } catch (Exception $e) {
            echo '<p style="text-align: center; padding: 60px 20px; color: #ffffff;">Error loading blog posts.</p>';
        }
        ?>
    </div>
</section>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <?php include 'includes/footer.php'; ?>
    </footer>

    <!-- Enhanced JavaScript -->
    <script>
        
        // ENHANCED HERO ANIMATIONS

        
        document.addEventListener('DOMContentLoaded', function() {
            
            // Parallax effect for hero image
            const heroImageCircle = document.querySelector('.hero-image-circle');
            const heroText = document.querySelector('.hero-text');
            const latestBlogsSection = document.querySelector('.latest-blogs-section');
            
            if (heroImageCircle && heroText) {
                window.addEventListener('scroll', function() {
                    const scrolled = window.pageYOffset;
                    const rate = scrolled * 0.3;
                    
                    heroImageCircle.style.transform = `translateY(${rate * 0.5}px)`;
                    heroText.style.transform = `translateY(${rate * 0.3}px)`;
                });
            }

            // Smooth scroll reveal
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, index * 100);
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.fade-in, .scale-in, .slide-in-left, .slide-in-right, .blog-card-small').forEach(el => {
                observer.observe(el);
            });

            // Magnetic button effect
            const buttons = document.querySelectorAll('.btn-primary, .btn-secondary');
            
            buttons.forEach(button => {
                button.addEventListener('mousemove', function(e) {
                    const rect = button.getBoundingClientRect();
                    const x = e.clientX - rect.left - rect.width / 2;
                    const y = e.clientY - rect.top - rect.height / 2;
                    
                    button.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px) scale(1.05)`;
                });
                
                button.addEventListener('mouseleave', function() {
                    button.style.transform = 'translate(0, 0) scale(1)';
                });
            });

            // Hero text stagger animation
            const heroElements = document.querySelectorAll('.hero-tagline, .hero-text h1, .hero-description, .hero-text .btn');
            
            heroElements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    el.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 200 + (index * 150));
            });

            // Blog card hover tilt effect
            const blogCards = document.querySelectorAll('.blog-card-small');
            
            blogCards.forEach(card => {
                card.addEventListener('mousemove', function(e) {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    
                    const rotateX = (y - centerY) / 20;
                    const rotateY = (centerX - x) / 20;
                    
                    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-15px) scale(1.02)`;
                });
                
                card.addEventListener('mouseleave', function() {
                    card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0) scale(1)';
                });
            });

            // Latest Blogs parallax
            if (latestBlogsSection) {
                window.addEventListener('scroll', function() {
                    const scrolled = window.pageYOffset;
                    const sectionTop = latestBlogsSection.offsetTop;
                    const sectionHeight = latestBlogsSection.offsetHeight;
                    
                    if (scrolled > sectionTop - window.innerHeight && scrolled < sectionTop + sectionHeight) {
                        const bgImage = latestBlogsSection.querySelector('.page-hero-image');
                        if (bgImage) {
                            const rate = (scrolled - sectionTop + window.innerHeight) * 0.3;
                            bgImage.style.transform = `translateY(${rate}px)`;
                        }
                    }
                });
            }

            // Page load animation
            setTimeout(() => {
                document.body.classList.add('page-loaded');
            }, 100);
        });
    </script>
</body>
</html>
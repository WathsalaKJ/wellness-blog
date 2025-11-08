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
            <h1>SoulBalance</h1>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="active">Home</a>
            <a href="latest_blogs.php">Blog</a> <!-- Changed link -->
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-image-circle slide-in-left">
                    <img src="assets/images/hero-yoga.png" alt="Yoga lifestyle - Group practicing yoga" loading="eager">
                </div>
                <div class="hero-text slide-in-right">
                    <p class="hero-tagline">ELEVATE YOUR WELL BEING</p>
                    <h1>start a healthy way of life, today!</h1>
                    <p class="hero-description">We believe in the strength of connection; connection with your physical self and connection to the greater world.</p>
                    <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'register.php'; ?>" class="btn btn-primary btn-lg">Get Started</a>
                </div>
            </div>
            
            <!-- Testimonial Quote -->
            <div class="hero-testimonial fade-in">
                <div class="testimonial-avatar">
                    <img src="assets/images/testimonial-avatar.png" alt="Testimonial" loading="lazy">
                </div>
                <blockquote>
                    <p>"Find harmony in body and mind with our yoga and fitness workshop."</p>
                </blockquote>
            </div>
        </div>
    </section>
    <!-- Latest Blogs Footer Section -->
    <section class="latest-blogs-section">
        <div class="container">
            <div class="section-header">
                 <img src="assets/images/blog-hero-bg.jpg" alt="Blog background" class="page-hero-image" onerror="this.src='assets/images/about-hero.jpg'">
                <h2>Latest Blog Posts</h2>
                <p>Stay inspired with our latest wellness insights</p>
            </div>
            <div class="blog-grid-footer fade-in">
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
               $latestPosts = $stmt->fetchAll();
    
                 foreach ($latestPosts as $index => $post):
                   $preview = strlen($post['content']) > 100 ? substr(strip_tags($post['content']), 0, 100) . '...' : strip_tags($post['content']);
                    ?>
                      <article class="blog-card-small fade-in" style="animation-delay: <?php echo ($index * 0.1); ?>s;">
                       <div class="blog-card-image">
                           <?php if (!empty($post['featured_image']) && file_exists($post['featured_image'])): ?>
                             <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                          <?php else: ?>
                               <img src="/placeholder.svg?height=150&width=300" alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                         <?php endif; ?>
                        </div>
                      <div class="blog-card-content">
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                           <p><?php echo htmlspecialchars($preview); ?></p>
                           <a href="view_blog.php?id=<?php echo $post['id']; ?>" class="read-more">Read More →</a>
                          </div>
                        </article>
                     <?php endforeach;
                          } catch (Exception $e) {
                           echo '<p>No blog posts available yet.</p>';
                              }
                           ?>
            </div>
             <div style="text-align: center; margin-top: var(--spacing-xl);">
                <a href="latest_blogs.php" class="btn btn-primary btn-lg">View All Blogs</a>
            </div>
        </div>
    </section>

    

    <!-- Footer -->
    <footer class="footer">
        <?php include 'includes/footer.php'; ?>
    </footer>

    <!-- Added scroll animation script -->
    <script src="assets/css/js/main.js"></script>
    <script>
        // Initialize intersection observer for scroll animations
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
    </script>
</body>
</html>

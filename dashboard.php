<?php
/**
 * User Dashboard
 * Only accessible to logged-in users
 * Shows user's blog posts and dashboard stats
 */

session_start();
require_once 'config/config.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get user's blog posts
try {
    $db = getDB();
    
    $stmt = $db->prepare("
    SELECT id, title, content, featured_image, created_at, updated_at 
    FROM blogPost 
    WHERE user_id = ? 
    ORDER BY created_at DESC
   ");
    $stmt->execute([$userId]);
    $userPosts = $stmt->fetchAll();
    
    // Get dashboard stats
    $totalPosts = count($userPosts);
    $lastPost = $userPosts[0]['updated_at'] ?? 'Never';
    
} catch (Exception $e) {
    $error = "Failed to load dashboard";
    $userPosts = [];
    $totalPosts = 0;
    $lastPost = 'N/A';
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage your SoulBalance blog posts and wellness content.">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard - SoulBalance</title>
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
                <a href="index.php">Home</a>
                <a href="index.php">Blog</a>
                <a href="categories.php">Categories</a>
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
            </nav>
            <div class="nav-actions">
                <div class="user-info">
                    <span class="username"><?php echo htmlspecialchars($username); ?></span>
                    <a href="logout.php" class="btn btn-secondary btn-sm logout-link">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Enhanced dashboard welcome section -->
            <div class="dashboard-welcome fade-in">
                <h1>Welcome back, <?php echo htmlspecialchars($username); ?>! 👋</h1>
                <p>Manage your wellness content and grow your community</p>
            </div>
            
            <!-- Dashboard Stats -->
            <div class="dashboard-stats fade-in">
                <div class="stat-card scale-in" style="animation-delay: 0s;">
                    <h3>Total Posts</h3>
                    <p class="stat-number"><?php echo $totalPosts; ?></p>
                </div>
                <div class="stat-card scale-in" style="animation-delay: 0.1s;">
                    <h3>Last Updated</h3>
                    <p class="stat-date"><?php echo $lastPost !== 'Never' ? formatDate($lastPost) : $lastPost; ?></p>
                </div>
            </div>
            
            <!-- Create New Post Button -->
            <div class="create-post-section fade-in" style="margin: var(--spacing-xl) 0;">
                <a href="add_blog.php" class="btn btn-primary btn-lg">+ Create New Blog Post</a>
            </div>
            
            <!-- User's Blog Posts -->
            <section class="my-posts-section fade-in">
                <h2>My Blog Posts</h2>
                
                <?php if (empty($userPosts)): ?>
                    <div class="empty-state">
                        <h3>No blog posts yet</h3>
                        <p>Create your first blog post to share your wellness journey!</p>
                        <a href="add_blog.php" class="btn btn-primary">Create First Post</a>
                    </div>
                <?php else: ?>
                    <div class="blog-grid">
                        <?php foreach ($userPosts as $post): ?>
                            <article class="blog-card fade-in">
                               <div class="blog-card-image">
                                <?php if (!empty($post['featured_image']) && file_exists($post['featured_image'])): ?>
                                  <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                                <?php else: ?>
                                   <img src="/placeholder.svg?height=220&width=400" alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                                 <?php endif; ?>
                             </div>
                                <div class="blog-card-content">
                                    <h3 class="blog-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <div class="blog-meta">
                                        <span class="date"><?php echo formatDate($post['created_at']); ?></span>
                                    </div>
                                    <p class="blog-preview"><?php echo substr(strip_tags($post['content']), 0, 150); ?>...</p>
                                    <div class="blog-card-actions">
                                        <a href="view_blog.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary btn-sm">View</a>
                                        <a href="edit_blog.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                        <a href="delete_blog.php?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- Footer -->
       <footer class="footer">
        <?php include 'includes/footer.php'; ?>
    </footer>

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

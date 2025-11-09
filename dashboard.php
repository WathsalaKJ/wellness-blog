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
                <a href="latest_blogs.php">Blog</a>
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
    <main class="main-content dashboard-main">
        <div class="container">
            <!-- Session Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success fade-in" style="margin-bottom: var(--spacing-lg);">
                    <?php 
                    echo htmlspecialchars($_SESSION['success_message']); 
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error fade-in" style="margin-bottom: var(--spacing-lg);">
                    <?php 
                    echo htmlspecialchars($_SESSION['error_message']); 
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Dashboard Header -->
            <div class="dashboard-header fade-in">
                <img src="assets/images/blog-hero-bg.jpg" alt="Dashboard background" class="dashboard-header-image" onerror="this.src='assets/images/about-hero.jpg'">
                <div class="dashboard-header-overlay"></div>
                <div class="dashboard-header-content">
                    <div class="dashboard-greeting">
                        <h1>Welcome back, <span class="username-highlight"><?php echo htmlspecialchars($username); ?></span>! 👋</h1>
                        <p class="dashboard-subtitle">Manage your wellness content and grow your community</p>
                    </div>
                    <div class="dashboard-header-actions">
                        <a href="add_blog.php" class="btn btn-primary btn-lg btn-create-post">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/>
                            </svg>
                            Create New Post
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Stats Grid -->
            <div class="dashboard-stats-grid fade-in">
                <div class="stat-card-modern">
                    <div class="stat-icon stat-icon-posts">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-label">Total Posts</h3>
                        <p class="stat-value"><?php echo $totalPosts; ?></p>
                        <p class="stat-description">Published articles</p>
                    </div>
                </div>

                <div class="stat-card-modern">
                    <div class="stat-icon stat-icon-update">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-label">Last Activity</h3>
                        <p class="stat-value-small"><?php echo $lastPost !== 'Never' ? formatDate($lastPost) : $lastPost; ?></p>
                        <p class="stat-description">Most recent update</p>
                    </div>
                </div>

                <div class="stat-card-modern">
                    <div class="stat-icon stat-icon-profile">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-label">Account Status</h3>
                        <p class="stat-value-small">Active Author</p>
                        <p class="stat-description">Member since <?php echo date('M Y', strtotime($_SESSION['created_at'] ?? 'now')); ?></p>
                    </div>
                </div>
            </div>

            <!-- Posts Section -->
            <section class="dashboard-posts-section fade-in">
                <div class="section-header-dashboard">
                    <div>
                        <h2>My Blog Posts</h2>
                        <p class="section-subtitle">Manage and edit your published content</p>
                    </div>
                    <?php if (!empty($userPosts)): ?>
                        <div class="posts-filter">
                            <span class="posts-count"><?php echo $totalPosts; ?> <?php echo $totalPosts === 1 ? 'post' : 'posts'; ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($userPosts)): ?>
                    <div class="empty-state-modern">
                        <div class="empty-state-icon">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="12" y1="18" x2="12" y2="12"></line>
                                <line x1="9" y1="15" x2="15" y2="15"></line>
                            </svg>
                        </div>
                        <h3>No blog posts yet</h3>
                        <p>Start sharing your wellness journey by creating your first blog post</p>
                        <a href="add_blog.php" class="btn btn-primary btn-lg">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/>
                            </svg>
                            Create Your First Post
                        </a>
                    </div>
                <?php else: ?>
                    <div class="posts-grid-dashboard">
                        <?php foreach ($userPosts as $index => $post): ?>
                            <article class="post-card-dashboard fade-in" style="animation-delay: <?php echo ($index * 0.05); ?>s;">
                                <div class="post-card-image-dashboard">
                                    <?php if (!empty($post['featured_image']) && file_exists($post['featured_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="post-card-placeholder">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                <polyline points="21 15 16 10 5 21"></polyline>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <div class="post-status-badge">
                                        <span class="status-dot"></span>
                                        Published
                                    </div>
                                </div>
                                
                                <div class="post-card-content-dashboard">
                                    <div class="post-card-header">
                                        <h3 class="post-title-dashboard">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </h3>
                                        <div class="post-meta-dashboard">
                                            <span class="post-date">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <polyline points="12 6 12 12 16 14"></polyline>
                                                </svg>
                                                <?php echo formatDate($post['created_at']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <p class="post-excerpt-dashboard">
                                        <?php echo substr(strip_tags($post['content']), 0, 120); ?>...
                                    </p>
                                    
                                    <div class="post-card-actions">
                                        <a href="view_blog.php?id=<?php echo $post['id']; ?>" class="btn-action btn-view">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                            View
                                        </a>
                                        <a href="edit_blog.php?id=<?php echo $post['id']; ?>" class="btn-action btn-edit">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                            Edit
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $post['id']; ?>)" class="btn-action btn-delete">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                            Delete
                                        </button>
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

 <script>
        function confirmDelete(postId) {
            if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                window.location.href = 'delete_blog.php?id=' + postId;
            }
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alert.remove(), 400);
                }, 5000);
            });
        });

        // Intersection observer for animations
        const observers = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observers.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.fade-in').forEach(el => observers.observe(el));
    </script>
</html>

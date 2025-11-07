<?php
/**
 * Category Posts Page
 * Displays blog posts filtered by category
 */

session_start();
require_once 'config/config.php';
require_once 'config/database.php';

// Get category from URL
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

if (empty($category)) {
    header('Location: categories.php');
    exit();
}

// Get blog posts for this category
try {
    $db = getDB();
    
    // Pagination setup
    $postsPerPage = 9;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $postsPerPage;
    
    // Get total posts count for this category
    $countStmt = $db->prepare("SELECT COUNT(*) as total FROM blogPost WHERE category = ?");
    $countStmt->execute([$category]);
    $totalPosts = $countStmt->fetch()['total'];
    $totalPages = ceil($totalPosts / $postsPerPage);
    
    // Get posts for current page
   $stmt = $db->prepare("
    SELECT bp.id, bp.title, bp.content, bp.category, bp.featured_image, bp.created_at, u.username, u.id as user_id
    FROM blogPost bp
    JOIN user u ON bp.user_id = u.id
    WHERE bp.category = ?
    ORDER BY bp.created_at DESC
    LIMIT ? OFFSET ?
   ");
    $stmt->execute([$category, $postsPerPage, $offset]);
    $posts = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = "Failed to load posts";
    $posts = [];
}

function getPreview($content, $length = 150) {
    $text = strip_tags($content);
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
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
    <meta name="description" content="Browse <?php echo htmlspecialchars($category); ?> posts on SoulBalance - Your wellness journey.">
    <title><?php echo htmlspecialchars($category); ?> - SoulBalance</title>
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

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Category Header -->
            <div class="section-header fade-in">
                <h2><?php echo htmlspecialchars($category); ?></h2>
                <p>Explore our <?php echo htmlspecialchars(strtolower($category)); ?> articles and insights</p>
                <a href="categories.php" class="btn btn-secondary btn-sm" style="margin-top: var(--spacing-md);">← Back to Categories</a>
            </div>

            <!-- Blog Grid -->
            <section class="blog-grid fade-in">
                <?php if (empty($posts)): ?>
                    <div class="empty-state" style="grid-column: 1/-1;">
                        <h2>No posts in this category yet</h2>
                        <p>Check back soon for <?php echo htmlspecialchars(strtolower($category)); ?> content.</p>
                        <a href="categories.php" class="btn btn-primary">Browse Other Categories</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $index => $post): ?>
                        <article class="blog-card fade-in" style="animation-delay: <?php echo ($index * 0.1); ?>s;">
                            <div class="blog-card-image">
                                <?php if (!empty($post['featured_image']) && file_exists($post['featured_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                                <?php else: ?>
                                    <img src="/placeholder.svg?height=220&width=400" alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                                <?php endif; ?>
                                 <span class="category-badge"><?php echo htmlspecialchars($post['category']); ?></span>
                            </div>
                            <div class="blog-card-content">
                                <h3 class="blog-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                <div class="blog-meta">
                                    <span class="author">By <?php echo htmlspecialchars($post['username']); ?></span>
                                    <span class="date"><?php echo formatDate($post['created_at']); ?></span>
                                </div>
                                <p class="blog-preview"><?php echo getPreview($post['content']); ?></p>
                                <a href="view_blog.php?id=<?php echo $post['id']; ?>" class="read-more">Read More →</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="category_posts.php?category=<?php echo urlencode($category); ?>&page=<?php echo $page - 1; ?>" class="btn btn-secondary">Previous</a>
                    <?php endif; ?>
                    
                    <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="category_posts.php?category=<?php echo urlencode($category); ?>&page=<?php echo $page + 1; ?>" class="btn btn-secondary">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <?php include 'includes/footer.php'; ?>
    </footer>

    <script>
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

        document.querySelectorAll('.fade-in, .scale-in').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>
<?php
/**
 * View Single Blog Post
 * Displays the full content of a blog post
 */

session_start();
require_once 'config/config.php';
require_once 'config/database.php';

// Get blog post ID from URL
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($postId === 0) {
    header('Location: index.php');
    exit();
}

// Get the blog post
try {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT bp.id, bp.user_id, bp.title, bp.content, bp.created_at, bp.updated_at, u.username
        FROM blogPost bp
        JOIN user u ON bp.user_id = u.id
        WHERE bp.id = ?
    ");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    if (!$post) {
        header('Location: index.php');
        exit();
    }
} catch (Exception $e) {
    header('Location: index.php');
    exit();
}

// Check if current user is the post owner
$isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] === $post['user_id'];

function formatDate($date) {
    return date('F j, Y \a\t g:i A', strtotime($date));
}

function getAvatarColor($username) {
    $colors = ['#ff7a00', '#10b981', '#3b82f6', '#f59e0b', '#ef4444'];
    $hash = hash('md5', $username);
    $colorIndex = hexdec(substr($hash, 0, 2)) % count($colors);
    return $colors[$colorIndex];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars(substr(strip_tags($post['content']), 0, 160)); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($post['title']); ?> - SoulBalance">
    <meta property="og:description" content="<?php echo htmlspecialchars(substr(strip_tags($post['content']), 0, 160)); ?>">
    <title><?php echo htmlspecialchars($post['title']); ?> - SoulBalance</title>
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
                <a href="index.php" class="active">Blog</a>
                <a href="categories.php">Categories</a>
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
            </nav>
            <div class="nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-info">
                        <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
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
            <!-- Add featured image -->
            <div class="blog-featured-image fade-in">
                <img src="/placeholder.svg?height=400&width=800" alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
            </div>

            <article class="blog-post fade-in">
                <div class="blog-header">
                    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
                    <!-- Enhanced author and meta information -->
                    <div class="blog-author-meta">
                        <div class="author-info">
                            <div class="author-avatar" style="background-color: <?php echo getAvatarColor($post['username']); ?>;">
                                <?php echo strtoupper(substr($post['username'], 0, 1)); ?>
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                                <p>Author</p>
                            </div>
                        </div>
                        <div class="blog-meta">
                            <span><?php echo formatDate($post['created_at']); ?></span>
                            <?php if ($post['created_at'] !== $post['updated_at']): ?>
                                <span>(Updated: <?php echo formatDate($post['updated_at']); ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="blog-content">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>
                
                <?php if ($isOwner): ?>
                    <div class="blog-actions">
                        <a href="edit_blog.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary">✏️ Edit Post</a>
                        <a href="delete_blog.php?id=<?php echo $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this post?');"> 🗑️ Delete Post</a>
                    </div>
                <?php endif; ?>
            </article>
            
            <div class="back-button fade-in">
                <a href="index.php" class="btn btn-secondary">← Back to Blog</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
       <?php include 'includes/footer.php'; ?>
    </footer>

    <script src="assets/css/js/main.js"></script>
    <script>
        // Observe animations
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
    </script>
</body>
</html>

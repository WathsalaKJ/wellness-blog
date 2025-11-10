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
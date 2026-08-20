<?php $activePage = $activePage ?? ''; ?>
<!-- Navigation -->
<header class="navbar">
    <div class="container">
        <div class="nav-brand">
            <a href="index.php"><h1>SoulBalance</h1></a>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="<?php echo $activePage === 'home' ? 'active' : ''; ?>">Home</a>
            <a href="latest_blogs.php" class="<?php echo $activePage === 'blog' ? 'active' : ''; ?>">Blog</a>
            <a href="categories.php" class="<?php echo $activePage === 'categories' ? 'active' : ''; ?>">Categories</a>
            <a href="about.php" class="<?php echo $activePage === 'about' ? 'active' : ''; ?>">About</a>
            <a href="contact.php" class="<?php echo $activePage === 'contact' ? 'active' : ''; ?>">Contact</a>
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
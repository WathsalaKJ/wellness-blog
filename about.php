<?php
/**
 * About Us Page
 * Displays information about SoulBalance, mission, and team
 */

session_start();
require_once 'config/config.php';
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="About SoulBalance - Learn about our mission, values, and team dedicated to wellness and yoga.">
    <meta name="keywords" content="about, wellness, yoga, team, mission">
    <meta name="author" content="SoulBalance">
    <meta property="og:title" content="About SoulBalance">
    <meta property="og:description" content="Learn about our wellness mission and values.">
    <meta property="og:type" content="website">
    <title>About SoulBalance - Our Wellness Mission</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='45' fill='%23ff7a00'/><text x='50' y='60' font-size='50' font-weight='bold' text-anchor='middle' fill='white' font-family='serif'>S</text></svg>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Source+Sans+Pro:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="about.php" class="active">About</a>
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
    <section class="about-hero">
        <div class="container">
            <img src="assets/images/about-hero.jpg" alt="Our wellness community practicing together">
            <div class="about-hero-content">
                <h1>About <em>Us</em></h1>
                <div class="breadcrumb">
                    <a href="index.php">Home</a> >> <span>About Us</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Our Journey Section -->
            <section class="about-section fade-in">
                <div class="section-header">
                    <h2>Our Journey</h2>
                </div>
                <div class="about-content">
                    <div class="about-text">
                        <p>SoulBalance was born from a simple belief: that everyone deserves access to the transformative power of yoga and holistic wellness. In a world that moves at an overwhelming pace, we recognized the need for a sanctuary where people could learn, share, and grow together on their wellness journey.</p>
                        <p>What started as a small community of yoga enthusiasts has blossomed into a thriving platform where thousands of wellness seekers from around the globe connect, inspire, and support each other. We're more than just a blog – we're a movement dedicated to making wellness accessible, inclusive, and sustainable for all.</p>
                    </div>
                    <div class="about-image">
                        <img src="assets/images/about-journey.jpg" alt="Our wellness community practicing together">
                    </div>
                </div>
            </section>

            <!-- Our Mission Section -->
            <section class="about-section fade-in">
                <div class="section-header">
                    <h2>Our Mission</h2>
                </div>
                <div class="mission-grid">
                    <div class="mission-card scale-in">
                        <div class="mission-icon">
                            <i class="fas fa-heartbeat fa-2x"></i>
                        </div>
                        <h3>Healthy Body</h3>
                        <p>We believe that yoga is more than just physical practice. It's about building strength, flexibility, and vitality.</p>
                    </div>
                    <div class="mission-card scale-in" style="animation-delay: 0.1s;">
                        <div class="mission-icon">
                            <i class="fas fa-brain fa-2x"></i>
                        </div>
                        <h3>Calm Mind</h3>
                        <p>We believe that yoga is more than just physical practice. It's about cultivating mental clarity and peace.</p>
                    </div>
                    <div class="mission-card scale-in" style="animation-delay: 0.2s;">
                        <div class="mission-icon">
                            <i class="fas fa-spa fa-2x"></i>
                        </div>
                        <h3>Stress Relief</h3>
                        <p>We believe that yoga is more than just physical practice. It's about releasing tension and finding inner peace.</p>
                    </div>
                </div>
            </section>

            <!-- Values Section -->
            <section class="about-section fade-in">
                <div class="section-header">
                    <h2>Our Values</h2>
                </div>
                <div class="values-grid">
                    <div class="value-item">
                        <h4>Inclusivity</h4>
                        <p>Wellness is for everyone, regardless of age, ability, or background.</p>
                    </div>
                    <div class="value-item">
                        <h4>Authenticity</h4>
                        <p>We honor the true principles of yoga and wellness practices.</p>
                    </div>
                    <div class="value-item">
                        <h4>Community</h4>
                        <p>Together, we create a supportive environment for shared growth.</p>
                    </div>
                    <div class="value-item">
                        <h4>Sustainability</h4>
                        <p>We're committed to creating a wellness movement that lasts.</p>
                    </div>
                </div>
            </section>

            
        </div>
    </main>

     <footer class="footer">
        <?php include 'includes/footer.php'; ?>
    </footer>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

    
</body>
</html>
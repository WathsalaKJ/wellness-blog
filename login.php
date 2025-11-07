<?php
/**
 * Login Page - Enhanced Design
 * Split screen with image and form
 */

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/config.php';
    require_once 'config/database.php';
    
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, username, email, password, role FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - SoulBalance</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='45' fill='%23ff7a00'/><text x='50' y='60' font-size='50' font-weight='bold' text-anchor='middle' fill='white' font-family='serif'>S</text></svg>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Nunito+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
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

    <!-- Auth Split Screen -->
    <div class="auth-split-screen">
        <!-- Left Side - Image with Quote -->
        <div class="auth-image-side">
            <img src="assets/images/login-yoga.jpg" alt="Woman in meditation pose" class="auth-bg-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22800%22 height=%221024%22%3E%3Crect fill=%22%23f2e9e3%22 width=%22800%22 height=%221024%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22sans-serif%22 font-size=%2248%22 fill=%22%23ff7a00%22%3EYoga Wellness%3C/text%3E%3C/svg%3E'">
            <div class="auth-quote">
                <svg class="quote-icon" width="22" height="22" viewBox="0 0 22 22" fill="#ff6f00">
                    <path d="M9.5 4L4 9.5L9.5 15V11C14 11 17 13 18.5 17C17.5 12.5 15 8 9.5 7V4Z"/>
                </svg>
                <blockquote>
                    <p>"The body benefits from movement, and the mind benefits from stillness."</p>
                    <cite>- Sakyong Mipham</cite>
                </blockquote>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="auth-form-side">
            <div class="auth-form-container">
                <div class="auth-form-header">
                    <h1>Login to your Account</h1>
                    <p>See what is going on with your business</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="mail@abc.com" 
                            required 
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="••••••••" 
                            required
                        >
                    </div>

                    <div class="form-extras">
                        <label class="checkbox-container">
                            <input type="checkbox" name="remember">
                            <span class="checkmark"></span>
                            Remember Me
                        </label>
                        <a href="#" class="forgot-link">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full btn-auth">Login</button>
                </form>

                <div class="auth-footer-link">
                    <span>Not Registered Yet?</span>
                    <a href="register.php">Create an account</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.auth-image-side, .auth-form-side').forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = index === 0 ? 'translateX(-30px)' : 'translateX(30px)';
            setTimeout(() => {
                el.style.transition = 'all 0.6s ease';
                el.style.opacity = '1';
                el.style.transform = 'translateX(0)';
            }, index * 100);
        });
    </script>
</body>
</html>
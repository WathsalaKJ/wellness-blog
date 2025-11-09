<?php
/*Registration Page - Enhanced Design*/

// Start session to manage user authentication state
session_start();

// Redirect to dashboard if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Initialize error message variable
$error = '';

// Process form submission when POST request is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include configuration and database connection files
    require_once 'config/config.php';
    require_once 'config/database.php';
    
    // Sanitize and retrieve form input values
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate form inputs
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'All fields are required';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        // If validation passes, attempt to register user
        try {
            // Get database connection
            $db = getDB();
            
            // Check if email or username already exists
            $checkStmt = $db->prepare("SELECT id FROM user WHERE email = ? OR username = ?");
            $checkStmt->execute([$email, $username]);
            
            if ($checkStmt->fetch()) {
                $error = 'Email or username already exists';
            } else {
                // Hash password for secure storage
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user into database with default 'user' role
                $stmt = $db->prepare("INSERT INTO user (username, email, password, role) VALUES (?, ?, ?, 'user')");
                $stmt->execute([$username, $email, $hashedPassword]);
                
                // Get the newly created user ID and set session variables
                $userId = $db->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'user';
                
                // Redirect to dashboard after successful registration
                header('Location: dashboard.php');
                exit();
            }
        } catch (Exception $e) {
            // Handle any database errors
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - SoulBalance</title>
    <!-- Custom SVG favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='45' fill='%23ff7a00'/><text x='50' y='60' font-size='50' font-weight='bold' text-anchor='middle' fill='white' font-family='serif'>S</text></svg>">
    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Nunito+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Navigation Header -->
    <header class="navbar">
        <div class="container">
            <!-- Brand/Logo -->
            <div class="nav-brand">
                <h1>SoulBalance</h1>
            </div>
            <!-- Navigation Links -->
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="index.php" class="active">Blog</a>
                <a href="categories.php">Categories</a>
                <a href="about.php">About</a>
                <a href="#contact.php">Contact</a>
            </nav>
            <!-- User Actions -->
            <div class="nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Display user info and actions when logged in -->
                    <div class="user-info">
                        <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="dashboard.php" class="btn btn-primary btn-sm">Dashboard</a>
                        <a href="logout.php" class="btn btn-secondary btn-sm logout-link">Logout</a>
                    </div>
                <?php else: ?>
                    <!-- Display sign in button when not logged in -->
                    <a href="login.php" class="btn btn-primary btn-sm">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Split Screen Authentication Layout -->
    <div class="auth-split-screen register-layout">
        <!-- Left Side - Registration Form -->
        <div class="auth-form-side auth-form-left">
            <div class="auth-form-container">
                <!-- Form Header -->
                <div class="auth-form-header">
                    <h1>Begin Your Journey</h1>
                    <p>Join our wellness community today</p>
                </div>

                <!-- Display error message if any -->
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form method="POST" class="auth-form">
                    <!-- Username Field -->
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            placeholder="Choose a unique user name" 
                            required 
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        >
                        <small>This will be displayed on your blog posts</small>
                    </div>

                    <!-- Email Field -->
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

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Create a strong password" 
                            required
                        >
                        <small>At least 8 characters with letters and numbers</small>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Re-enter your password" 
                            required
                        >
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-full btn-auth">Create Account</button>
                </form>

                <!-- Link to Login Page -->
                <div class="auth-footer-link">
                    <span>Already have an account?</span>
                    <a href="login.php">Log in here</a>
                </div>
            </div>
        </div>

        <!-- Right Side - Image with Inspirational Quote -->
        <div class="auth-image-side auth-image-right">
            <!-- Background Image with fallback SVG -->
            <img src="assets/images/register-meditation.jpg" alt="Woman meditating outdoors" class="auth-bg-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22800%22 height=%221024%22%3E%3Crect fill=%22%23f2e9e3%22 width=%22800%22 height=%221024%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22sans-serif%22 font-size=%2248%22 fill=%22%23ff7a00%22%3EMeditation%3C/text%3E%3C/svg%3E'">
            <!-- Inspirational Quote -->
            <div class="auth-quote auth-quote-bottom">
                <!-- Quote Icon -->
                <svg class="quote-icon" width="22" height="22" viewBox="0 0 22 22" fill="#ff6f00">
                    <path d="M9.5 4L4 9.5L9.5 15V11C14 11 17 13 18.5 17C17.5 12.5 15 8 9.5 7V4Z"/>
                </svg>
                <p>We are passionate about creating a space where every body is family.</p>
            </div>
        </div>
    </div>

    <!-- Animation Script for Smooth Page Load -->
    <script>
        // Animate form and image sides on page load
        document.querySelectorAll('.auth-form-side, .auth-image-side').forEach((el, index) => {
            // Set initial state for animation
            el.style.opacity = '0';
            el.style.transform = index === 0 ? 'translateX(-30px)' : 'translateX(30px)';
            
            // Animate elements into view with staggered timing
            setTimeout(() => {
                el.style.transition = 'all 0.6s ease';
                el.style.opacity = '1';
                el.style.transform = 'translateX(0)';
            }, index * 100);
        });
    </script>
</body>
</html>
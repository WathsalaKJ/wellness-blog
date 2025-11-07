<?php
/**
 * Enhanced Contact Page
 * Professional design with better validation and database storage
 */

session_start();
require_once 'config/config.php';
require_once 'config/database.php';

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Comprehensive validation
    if (empty($firstName)) {
        $error = 'First name is required';
    } elseif (strlen($firstName) < 2) {
        $error = 'First name must be at least 2 characters';
    } elseif (empty($lastName)) {
        $error = 'Last name is required';
    } elseif (strlen($lastName) < 2) {
        $error = 'Last name must be at least 2 characters';
    } elseif (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (!empty($phone) && !preg_match('/^[0-9\s\-\+\(\)]+$/', $phone)) {
        $error = 'Please enter a valid phone number';
    } elseif (empty($subject)) {
        $error = 'Please select a subject';
    } elseif (empty($message)) {
        $error = 'Message is required';
    } elseif (strlen($message) < 10) {
        $error = 'Message must be at least 10 characters';
    } elseif (strlen($message) > 2000) {
        $error = 'Message must not exceed 2000 characters';
    } else {
        try {
            // Save to database
            $db = getDB();
            $stmt = $db->prepare("
                INSERT INTO contact_messages 
                (first_name, last_name, email, phone, subject, message, created_at, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
            ");
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $fullName = $firstName . ' ' . $lastName;
            
            $stmt->execute([
                $firstName,
                $lastName,
                $email,
                $phone,
                $subject,
                $message,
                $ipAddress
            ]);
            
            // Send email notification (configure mail settings in production)
            $to = "hello@soulbalance.com";
            $emailSubject = "New Contact Form: " . $subject;
            $emailBody = "
                New contact form submission from SoulBalance website
                
                Name: $fullName
                Email: $email
                Phone: " . ($phone ?: 'Not provided') . "
                Subject: $subject
                
                Message:
                $message
                
                ---
                Submitted: " . date('Y-m-d H:i:s') . "
                IP Address: $ipAddress
            ";
            
            $headers = "From: noreply@soulbalance.com\r\n";
            $headers .= "Reply-To: $email\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            // Uncomment in production with proper mail configuration
            // mail($to, $emailSubject, $emailBody, $headers);
            
            $success = 'Thank you for reaching out, ' . htmlspecialchars($firstName) . '! We\'ve received your message and will get back to you at ' . htmlspecialchars($email) . ' within 24-48 hours.';
            
            // Clear form data on success
            $_POST = array();
            
        } catch (PDOException $e) {
            error_log("Contact form error: " . $e->getMessage());
            $error = 'Sorry, there was a problem sending your message. Please try again later or contact us directly.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contact SoulBalance - Get in touch with us for wellness inquiries, yoga guidance, meditation support, or collaboration opportunities.">
    <meta name="keywords" content="contact, wellness, yoga, meditation, support, inquiries, collaboration">
    <meta name="author" content="SoulBalance">
    <title>Contact Us - SoulBalance | Wellness & Mindfulness Center</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='45' fill='%23ff7a00'/><text x='50' y='65' font-size='50' font-weight='bold' text-anchor='middle' fill='white' font-family='serif'>S</text></svg>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <header class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php"><h1>SoulBalance</h1></a>
            </div>
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="index.php#blog">Blog</a>
                <a href="categories.php">Categories</a>
                <a href="about.php">About</a>
                <a href="contact.php" class="active">Contact Us</a>
            </nav>
            <div class="nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-info">
                        <span class="username">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="dashboard.php" class="btn btn-primary btn-sm">Dashboard</a>
                        <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-sm">Sign In</a>
                    <a href="register.php" class="btn btn-secondary btn-sm">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="contact-hero-content fade-in">
            <img src="assets/images/about-hero.jpg" alt="Our wellness community practicing together">
            <h1>Contact <span class="highlight">us</span></h1>
            <div class="contact-breadcrumb">

                <a href="index.php">Home</a>
                <span>»</span>
                <span>Contact us</span>
            </div>
        </div>
    </section>

    <!-- Main Contact Section -->
    <main class="contact-main">
        <div class="contact-container">
            <div class="contact-layout">
                <!-- Contact Form Section -->
                <div class="contact-form-section fade-in">
                    <p class="section-title">Contact Now</p>
                    <h2>Have some questions!</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" class="contact-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input 
                                    type="text" 
                                    id="firstName" 
                                    name="firstName" 
                                    required 
                                    placeholder="First Name"
                                    value="<?php echo htmlspecialchars($_POST['firstName'] ?? ''); ?>"
                                    minlength="2"
                                    maxlength="50"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input 
                                    type="text" 
                                    id="lastName" 
                                    name="lastName" 
                                    required 
                                    placeholder="Last Name"
                                    value="<?php echo htmlspecialchars($_POST['lastName'] ?? ''); ?>"
                                    minlength="2"
                                    maxlength="50"
                                >
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    required 
                                    placeholder="Email"
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input 
                                    type="tel" 
                                    id="phone" 
                                    name="phone" 
                                    placeholder="Phone"
                                    value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                >
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <select id="subject" name="subject" required>
                                <option value="">Subject</option>
                                <option value="General Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="Wellness Support" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Wellness Support') ? 'selected' : ''; ?>>Wellness Support</option>
                                <option value="Yoga Classes" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Yoga Classes') ? 'selected' : ''; ?>>Yoga Classes</option>
                                <option value="Meditation Guidance" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Meditation Guidance') ? 'selected' : ''; ?>>Meditation Guidance</option>
                                <option value="Collaboration" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Collaboration') ? 'selected' : ''; ?>>Collaboration</option>
                                <option value="Technical Issue" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Technical Issue') ? 'selected' : ''; ?>>Technical Issue</option>
                                <option value="Feedback" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Feedback') ? 'selected' : ''; ?>>Feedback</option>
                                <option value="Other" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea 
                                id="message" 
                                name="message" 
                                required 
                                minlength="10"
                                maxlength="2000"
                                placeholder="Send us your message and we will contact you asap"
                            ><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            <small><?php echo isset($_POST['message']) ? strlen($_POST['message']) : 0; ?> / 20 characters</small>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            Submit
                        </button>
                    </form>
                </div>

                <!-- Contact Image & Info Section -->
                <div class="contact-image-section fade-in" style="animation-delay: 0.2s;">
                    <div class="contact-image-wrapper">
                        <img src="assets/images/contact-yoga.jpg" alt="Woman practicing yoga in peaceful outdoor setting">
                        
                        <div class="contact-info-overlay">
                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                </div>
                                <div class="contact-details">
                                    <p>0112556984</p>
                                </div>
                            </div>
                            
                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <svg viewBox="0 0 20 20" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                </div>
                                <div class="contact-details">
                                    <p>soulbalance@gmail.com</p>
                                </div>
                            </div>
                            
                            <div class="contact-info-item">
                                <div class="contact-icon">
                                    <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                </div>
                                <div class="contact-details">
                                    <p>University of Moratuwa,Katubedda,Colombo</p>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="faq-container">
            <div class="faq-header fade-in">
                <p class="section-title">FAQ</p>
                <h2>Frequently Asked Questions</h2>
                <p>Quick answers to common questions about our wellness services</p>
            </div>
            
            <div class="faq-list">
                <details class="faq-item fade-in">
                    <summary>How quickly will I receive a response?</summary>
                    <div class="faq-answer">
                        <p>We typically respond to all inquiries within 24-48 hours during business days (Monday to Friday, 9am-6pm EST). For urgent matters, please call us directly at (603) 555-0123-2345, and we'll be happy to assist you immediately.</p>
                    </div>
                </details>

                <details class="faq-item fade-in" style="animation-delay: 0.1s;">
                    <summary>Can I schedule a wellness consultation?</summary>
                    <div class="faq-answer">
                        <p>Absolutely! We offer personalized wellness consultations both in-person and virtually. Please mention "Wellness Consultation" in the subject line of your message, and include your preferred days and times. Our wellness coordinator will reach out to schedule a convenient time for your session.</p>
                    </div>
                </details>

                <details class="faq-item fade-in" style="animation-delay: 0.2s;">
                    <summary>Do you offer collaboration opportunities?</summary>
                    <div class="faq-answer">
                        <p>Yes! We're always looking to collaborate with wellness practitioners, yoga instructors, meditation guides, nutritionists, and wellness brands. Select "Collaboration" as your subject and tell us about your expertise and how you'd like to work together. We'll review your proposal and get back to you within 5 business days.</p>
                    </div>
                </details>

                <details class="faq-item fade-in" style="animation-delay: 0.3s;">
                    <summary>What types of yoga classes do you offer?</summary>
                    <div class="faq-answer">
                        <p>We offer a wide variety of yoga practices including Hatha Yoga, Vinyasa Flow, Yin Yoga, Restorative Yoga, and Power Yoga. Classes are available for all levels - from complete beginners to advanced practitioners. Contact us to learn more about our current class schedule and find the perfect practice for your needs.</p>
                    </div>
                </details>

                <details class="faq-item fade-in" style="animation-delay: 0.4s;">
                    <summary>Are your meditation sessions suitable for beginners?</summary>
                    <div class="faq-answer">
                        <p>Definitely! Our meditation sessions are designed to accommodate all experience levels. We offer guided meditation for beginners, breathwork techniques, mindfulness practices, and advanced meditation workshops. Our instructors provide clear guidance and create a welcoming, judgment-free environment for everyone.</p>
                    </div>
                </details>

                <details class="faq-item fade-in" style="animation-delay: 0.5s;">
                    <summary>Can I visit your wellness center in person?</summary>
                    <div class="faq-answer">
                        <p>Yes, we welcome visitors! Our center is located at 3517 W. Gray St. Pennsylvania 57867. We're open Monday through Friday, 9am-6pm EST. We recommend scheduling an appointment in advance to ensure we can give you our full attention and provide a proper tour of our facilities.</p>
                    </div>
                </details>

                <details class="faq-item fade-in" style="animation-delay: 0.6s;">
                    <summary>Do you offer virtual wellness programs?</summary>
                    <div class="faq-answer">
                        <p>Yes! We understand that not everyone can visit us in person. We offer comprehensive virtual wellness programs including online yoga classes, guided meditation sessions, nutrition consultations, and wellness coaching via video calls. All our virtual programs are interactive and designed to deliver the same quality experience as in-person sessions.</p>
                    </div>
                </details>
            </div>
        </div>
    </section>

    <!-- Enhanced Footer -->
     <footer class="footer">
        <?php include 'includes/footer.php'; ?>
    </footer>
    <script>
        // Character counter for message
        const messageTextarea = document.getElementById('message');
        const charCount = messageTextarea.nextElementSibling;
        
        messageTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = `${length} / 20 characters`;
            
            if (length > 20) {
                charCount.style.color = 'var(--danger)';
            } else {
                charCount.style.color = 'var(--text-light)';
            }
        });
        
        // Form validation enhancement
        const form = document.querySelector('.contact-form');
        form.addEventListener('submit', function(e) {
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('email').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if (firstName.length < 2 || lastName.length < 2) {
                e.preventDefault();
                alert('First name and last name must be at least 2 characters long');
                return false;
            }
            
            if (message.length < 10) {
                e.preventDefault();
                alert('Message must be at least 10 characters long');
                return false;
            }
        });
        
        // Auto-hide success message
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.opacity = '0';
                successAlert.style.transform = 'translateY(-20px)';
                setTimeout(() => successAlert.remove(), 400);
            }, 8000);
        }
        
        // Intersection observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in, .scale-in').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>
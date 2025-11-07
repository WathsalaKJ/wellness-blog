<?php
/**
 * Add/Create Blog Post Page with Image Upload - FIXED
 * Only accessible to logged-in users
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
$error = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    
    // Validate inputs
    if (empty($title) || empty($content)) {
        $error = 'Title and content are required';
    } else {
        try {
            $db = getDB();
            
            // Handle image upload
            $imagePath = null;
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/blogs/';
                
                // Create upload directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileInfo = pathinfo($_FILES['featured_image']['name']);
                $extension = strtolower($fileInfo['extension']);
                $fileName = uniqid() . '_' . time() . '.' . $extension;
                $uploadPath = $uploadDir . $fileName;
                
                // Allowed extensions
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($extension, $allowed)) {
                    $error = 'Invalid image format. Allowed: JPG, PNG, GIF, WEBP';
                } else if ($_FILES['featured_image']['size'] > 5 * 1024 * 1024) { // 5MB limit
                    $error = 'Image size must be less than 5MB';
                } else {
                    // Move uploaded file
                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $uploadPath)) {
                        $imagePath = $uploadPath;
                    } else {
                        $error = 'Failed to upload image';
                    }
                }
            }
            
            if (empty($error)) {
                // Insert blog post
                $stmt = $db->prepare("
                    INSERT INTO blogPost (user_id, title, content, category, featured_image, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([$userId, $title, $content, $category, $imagePath]);
                
                // Redirect to the new post
                $postId = $db->lastInsertId();
                header('Location: view_blog.php?id=' . $postId);
                exit();
            }
        } catch (Exception $e) {
            $error = 'Failed to create post: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Blog Post - SoulBalance</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='45' fill='%23ff7a00'/><text x='50' y='60' font-size='50' font-weight='bold' text-anchor='middle' fill='white' font-family='serif'>S</text></svg>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            </nav>
            <div class="nav-actions">
                <div class="user-info">
                    <span class="username"><?php echo htmlspecialchars($username); ?></span>
                    <a href="dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
                    <a href="logout.php" class="btn btn-secondary btn-sm logout-link">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="editor-container fade-in">
            <div class="editor-header">
                <div>
                    <h2>Create New Blog Post</h2>
                    <p class="editor-subtitle">Share your wellness insights with the community</p>
                </div>
                <div class="editor-actions">
                    <button type="submit" form="blog-form" class="btn btn-primary btn-lg">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"/>
                        </svg>
                        Publish Post
                    </button>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="editor-form" id="blog-form">
                <div class="editor-layout">
                    <!-- Main Content -->
                    <div class="editor-main">
                        <div class="form-group">
                            <label for="title">Post Title *</label>
                            <input type="text" id="title" name="title" placeholder="Give your post a compelling title..." required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="category">Category *</label>
                            <select id="category" name="category" required>
                                <option value="">Select a Category</option>
                                <option value="Yoga Practices">Yoga Practices</option>
                                <option value="Meditation">Meditation</option>
                                <option value="Nutrition">Nutrition</option>
                                
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Content *</label>
                            <textarea id="content" name="content" placeholder="Write your wellness article here..." required rows="15"><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="editor-sidebar">
                        <div class="featured-image-section">
                            <h4>Featured Image</h4>
                            <div class="image-upload-area" id="dropzone">
                                <div class="image-upload-content" id="uploadContent">
                                    <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <h5>Upload Featured Image</h5>
                                    <p>Drag & drop or click to browse</p>
                                    <p class="upload-info">JPG, PNG, GIF, WEBP. Max 5MB</p>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('featured_image').click()">Browse Files</button>
                                </div>
                                <input type="file" id="featured_image" name="featured_image" accept="image/*" style="display: none;">
                                <div id="image-preview" class="image-preview" style="display: none;">
                                    <img id="preview-img" src="" alt="Preview">
                                    <button type="button" class="btn btn-danger btn-xs" onclick="removeImage()">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 SoulBalance - Your Wellness Journey</p>
        </div>
    </footer>

    <script>
        // Image upload preview
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('featured_image');
        const imagePreview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');
        const uploadContent = document.getElementById('uploadContent');

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Highlight drop area
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.add('highlight'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.remove('highlight'), false);
        });

        // Handle dropped files
        dropzone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = dt.files;
                previewImage();
            }
        }

        // File input change
        fileInput.addEventListener('change', previewImage);

        function previewImage() {
            const file = fileInput.files[0];
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, PNG, GIF, WEBP)');
                    return;
                }
                
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    uploadContent.style.display = 'none';
                    imagePreview.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            }
        }

        function removeImage() {
            fileInput.value = '';
            imagePreview.style.display = 'none';
            uploadContent.style.display = 'block';
            previewImg.src = '';
        }

        // Fade in animation
        const observer = new IntersectionObserver(entries => {
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
<?php
/**
 * Edit Blog Post Page - FIXED
 * Only accessible to post owner
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
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';

if ($postId === 0) {
    header('Location: dashboard.php');
    exit();
}

// Get the blog post
try {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT id, user_id, title, content, category, featured_image FROM blogPost WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    if (!$post || $post['user_id'] !== $userId) {
        header('Location: dashboard.php');
        exit();
    }
} catch (Exception $e) {
    header('Location: dashboard.php');
    exit();
}

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
            // Handle new image upload
            $imagePath = $post['featured_image']; // Keep existing image by default
            
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/blogs/';
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileInfo = pathinfo($_FILES['featured_image']['name']);
                $extension = strtolower($fileInfo['extension']);
                $fileName = uniqid() . '_' . time() . '.' . $extension;
                $uploadPath = $uploadDir . $fileName;
                
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($extension, $allowed)) {
                    $error = 'Invalid image format';
                } else if ($_FILES['featured_image']['size'] > 5 * 1024 * 1024) {
                    $error = 'Image size must be less than 5MB';
                } else {
                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $uploadPath)) {
                        // Delete old image if exists
                        if ($post['featured_image'] && file_exists($post['featured_image'])) {
                            unlink($post['featured_image']);
                        }
                        $imagePath = $uploadPath;
                    } else {
                        $error = 'Failed to upload image';
                    }
                }
            }
            
            if (empty($error)) {
                // Update blog post
                $updateStmt = $db->prepare("
                    UPDATE blogPost 
                    SET title = ?, content = ?, category = ?, featured_image = ?, updated_at = NOW()
                    WHERE id = ? AND user_id = ?
                ");
                $updateStmt->execute([$title, $content, $category, $imagePath, $postId, $userId]);
                
                // Redirect to the post
                header('Location: view_blog.php?id=' . $postId);
                exit();
            }
        } catch (Exception $e) {
            $error = 'Failed to update post: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog Post - SoulBalance</title>
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
                <a href="dashboard.php">Dashboard</a>
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
    <main class="main-content">
        <div class="editor-container fade-in">
            <div class="editor-header">
                <div>
                    <h2>Edit Blog Post</h2>
                    <p class="editor-subtitle">Update your wellness article</p>
                </div>
                <div class="editor-actions">
                    <button type="submit" form="blog-form" class="btn btn-primary btn-lg">
                        💾 Save Changes
                    </button>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="editor-form" id="blog-form">
                <div class="editor-layout">
                    <div class="editor-main">
                        <div class="form-group">
                            <label for="title">Post Title *</label>
                            <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($post['title']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="category">Category *</label>
                            <select id="category" name="category" required>
                                <option value="">Select a Category</option>
                                <?php
                                $categories = ['Yoga Practices', 'Meditation', 'Nutrition', 'Wellness', 'Mindfulness', 'Fitness', 'Health'];
                                foreach ($categories as $cat) {
                                    $selected = ($post['category'] === $cat) ? 'selected' : '';
                                    echo "<option value=\"$cat\" $selected>$cat</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Content *</label>
                            <textarea id="content" name="content" required rows="15"><?php echo htmlspecialchars($post['content']); ?></textarea>
                        </div>
                    </div>

                    <div class="editor-sidebar">
                        <div class="featured-image-section">
                            <h4>Featured Image</h4>
                            
                            <?php if ($post['featured_image'] && file_exists($post['featured_image'])): ?>
                                <div class="current-image">
                                    <p><strong>Current Image:</strong></p>
                                    <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="Current featured image" style="width: 100%; border-radius: var(--radius); margin-bottom: var(--spacing-sm);">
                                </div>
                            <?php endif; ?>
                            
                            <div class="image-upload-area" id="dropzone">
                                <div class="image-upload-content" id="uploadContent">
                                    <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <h5>Upload New Image</h5>
                                    <p>Replace current featured image</p>
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
                    <a href="view_blog.php?id=<?php echo $postId; ?>" class="btn btn-secondary">Cancel</a>
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
        const fileInput = document.getElementById('featured_image');
        const imagePreview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');
        const uploadContent = document.getElementById('uploadContent');

        fileInput.addEventListener('change', previewImage);

        function previewImage() {
            const file = fileInput.files[0];
            if (file) {
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file');
                    return;
                }
                
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
        }

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
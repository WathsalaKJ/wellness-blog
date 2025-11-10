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
    
    // Enhanced authorization check with type casting
    if (!$post) {
        $_SESSION['error_message'] = 'Post not found';
        header('Location: dashboard.php');
        exit();
    }
    
    // Ensure both values are integers for comparison
    if ((int)$post['user_id'] !== (int)$userId) {
        $_SESSION['error_message'] = 'You do not have permission to edit this post';
        header('Location: dashboard.php');
        exit();
    }
} catch (Exception $e) {
    error_log("Edit Blog Error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to load post';
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
    <<!-- Navigation -->
    <header class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>SoulBalance</h1>
            </div>
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="latest_blogs.php">Blog</a>
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
                            <div class="editor-toolbar">
                                <button type="button" class="editor-btn" onclick="formatText('bold')" title="Bold (Ctrl+B)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path>
                                        <path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path>
                                    </svg>
                                    <span>Bold</span>
                                </button>
                                <button type="button" class="editor-btn" onclick="formatText('italic')" title="Italic (Ctrl+I)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="19" y1="4" x2="10" y2="4"></line>
                                        <line x1="14" y1="20" x2="5" y2="20"></line>
                                        <line x1="15" y1="4" x2="9" y2="20"></line>
                                    </svg>
                                    <span>Italic</span>
                                </button>
                                <button type="button" class="editor-btn" onclick="formatText('underline')" title="Underline (Ctrl+U)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 3v7a6 6 0 0 0 6 6 6 6 0 0 0 6-6V3"></path>
                                        <line x1="4" y1="21" x2="20" y2="21"></line>
                                    </svg>
                                    <span>Underline</span>
                                </button>
                                <div class="toolbar-divider"></div>
                                <button type="button" class="editor-btn" onclick="formatText('insertUnorderedList')" title="Bullet List">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="8" y1="6" x2="21" y2="6"></line>
                                        <line x1="8" y1="12" x2="21" y2="12"></line>
                                        <line x1="8" y1="18" x2="21" y2="18"></line>
                                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                    </svg>
                                    <span>List</span>
                                </button>
                                <button type="button" class="editor-btn" onclick="formatText('insertOrderedList')" title="Numbered List">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="10" y1="6" x2="21" y2="6"></line>
                                        <line x1="10" y1="12" x2="21" y2="12"></line>
                                        <line x1="10" y1="18" x2="21" y2="18"></line>
                                        <path d="M4 6h1v4"></path>
                                        <path d="M4 10h2"></path>
                                        <path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"></path>
                                    </svg>
                                    <span>Numbered</span>
                                </button>
                                <div class="toolbar-divider"></div>
                                <button type="button" class="editor-btn" onclick="insertLink()" title="Insert Link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                    </svg>
                                    <span>Link</span>
                                </button>
                                <button type="button" class="editor-btn" onclick="formatText('formatBlock', 'h2')" title="Heading">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 12h8"></path>
                                        <path d="M4 18V6"></path>
                                        <path d="M12 18V6"></path>
                                        <path d="M17 12h3"></path>
                                        <path d="M17 18h3"></path>
                                        <path d="M17 6h3"></path>
                                    </svg>
                                    <span>Heading</span>
                                </button>
                            </div>
                            <div id="contentEditor" class="content-editor" contenteditable="true" placeholder="Write your wellness article here..."></div>
                            <textarea id="content" name="content" required rows="15" style="display: none;"><?php echo htmlspecialchars($post['content']); ?></textarea>
                            <div class="editor-footer">
                                <span class="word-count" id="wordCount">0 words</span>
                                <span class="char-count" id="charCount">0 characters</span>
                            </div>
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
        <?php include 'includes/footer.php'; ?>
    </footer>

    <script>
    const fileInput = document.getElementById('featured_image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const uploadContent = document.getElementById('uploadContent');
    const contentEditor = document.getElementById('contentEditor');
    const contentTextarea = document.getElementById('content');
    const wordCount = document.getElementById('wordCount');
    const charCount = document.getElementById('charCount');

    // Load existing content into the editor on page load
    window.addEventListener('DOMContentLoaded', function() {
        if (contentTextarea.value) {
            contentEditor.innerHTML = contentTextarea.value;
            updateCounts();
        }
    });

    // Sync contenteditable div with hidden textarea
    contentEditor.addEventListener('input', function() {
        contentTextarea.value = contentEditor.innerHTML;
        updateCounts();
    });

    // Update word and character counts
    function updateCounts() {
        const text = contentEditor.innerText || '';
        const words = text.trim().split(/\s+/).filter(word => word.length > 0).length;
        const chars = text.length;
        
        wordCount.textContent = `${words} word${words !== 1 ? 's' : ''}`;
        charCount.textContent = `${chars} character${chars !== 1 ? 's' : ''}`;
    }

    // Text formatting functions
    function formatText(command, value = null) {
        document.execCommand(command, false, value);
        contentEditor.focus();
    }

    function insertLink() {
        const url = prompt('Enter URL:');
        if (url) {
            document.execCommand('createLink', false, url);
        }
        contentEditor.focus();
    }

    // Image upload handlers
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

    // Intersection observer for animations
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
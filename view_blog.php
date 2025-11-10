<?php
/**
 * View Single Blog Post - FIXED Comments and Ratings
 */

session_start();
require_once 'config/config.php';
require_once 'config/database.php';

$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($postId === 0) {
    header('Location: index.php');
    exit();
}

try {
    $db = getDB();
    
    // Get the blog post with featured image
    $stmt = $db->prepare("
        SELECT bp.id, bp.user_id, bp.title, bp.content, bp.category, bp.featured_image, bp.created_at, bp.updated_at, u.username
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

    // Get user's existing rating (if logged in)
    $userRating = 0;
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("SELECT rating FROM blog_ratings WHERE blog_post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $_SESSION['user_id']]);
        $ratingRow = $stmt->fetch();
        $userRating = $ratingRow ? $ratingRow['rating'] : 0;
    }

    // Get average rating and total ratings (combined user and public ratings)
    $stmt = $db->prepare("
        SELECT 
            (SELECT AVG(rating) FROM blog_ratings WHERE blog_post_id = ?) as user_avg,
            (SELECT COUNT(*) FROM blog_ratings WHERE blog_post_id = ?) as user_count,
            (SELECT AVG(rating) FROM blog_ratings_public WHERE blog_post_id = ?) as public_avg,
            (SELECT COUNT(*) FROM blog_ratings_public WHERE blog_post_id = ?) as public_count
    ");
    $stmt->execute([$postId, $postId, $postId, $postId]);
    $ratingData = $stmt->fetch();
    
    $totalRatings = ($ratingData['user_count'] ?? 0) + ($ratingData['public_count'] ?? 0);
    $avgRating = 0;
    
    if ($totalRatings > 0) {
        $userSum = ($ratingData['user_avg'] ?? 0) * ($ratingData['user_count'] ?? 0);
        $publicSum = ($ratingData['public_avg'] ?? 0) * ($ratingData['public_count'] ?? 0);
        $avgRating = round(($userSum + $publicSum) / $totalRatings, 1);
    }
    
    // Get comments
    $stmt = $db->prepare("
        SELECT c.id, c.comment, c.created_at, c.user_id, u.username 
        FROM blog_comments c 
        JOIN user u ON c.user_id = u.id 
        WHERE c.blog_post_id = ? 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$postId]);
    $comments = $stmt->fetchAll();

} catch (Exception $e) {
    header('Location: index.php');
    exit();
}

$isOwner = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$post['user_id'];

function formatDate($date) {
    return date('F j, Y \a\t g:i A', strtotime($date));
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', $time);
}

function getAvatarColor($username) {
    $colors = ['#ff7a00', '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
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
    <title><?php echo htmlspecialchars($post['title']); ?> - SoulBalance</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='45' fill='%23ff7a00'/><text x='50' y='60' font-size='50' font-weight='bold' text-anchor='middle' fill='white' font-family='serif'>S</text></svg>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- ENHANCED STYLES FOR RATINGS AND COMMENTS -->
    
</head>
<body>
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

    <!-- Page Hero Section -->
    <section class="page-hero">
        <div class="page-hero-overlay"></div>
        <img src="assets/images/about-hero.jpg" alt="Blog background" class="page-hero-image">
        
        <div class="page-hero-content">
            <div class="container">
                <div class="page-hero-title">
                    <h1>
                        <span class="title-bold">Blog </span>
                        <span class="title-italic">details</span>
                    </h1>
                </div>
                
                <div class="page-hero-bottom">
                    <div></div>
                    <div class="page-breadcrumb">
                        <a href="index.php">Home</a>
                        <span>&gt;&gt;</span>
                        <span>Blog details</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <article class="blog-post fade-in">
                <!-- Enhanced Header Box -->
                <div class="blog-post-header-box">
                    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
                    
                    <!-- Meta Information Row -->
                    <div class="blog-post-meta-row">
                        <!-- Author -->
                        <div class="blog-post-author-inline">
                            <div class="author-avatar" style="background-color: <?php echo getAvatarColor($post['username']); ?>;">
                                <?php echo strtoupper(substr($post['username'], 0, 1)); ?>
                            </div>
                            <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                        </div>
                        
                        <!-- Rating Display -->
                        <div class="blog-post-rating-inline">
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo $i <= $avgRating ? 'filled' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text">
                                <?php echo $avgRating > 0 ? $avgRating : 'No ratings'; ?>
                                <?php if ($totalRatings > 0): ?>
                                    (<?php echo $totalRatings; ?>)
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <!-- Date -->
                        <div class="blog-post-date-inline">
                            <?php echo formatDate($post['created_at']); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Content with Image -->
                <div class="blog-content-with-image">
                    <?php 
                    $content = $post['content'];
                    $allowedTags = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><ul><ol><li><a><blockquote><code><pre><div>';
                    $cleanContent = strip_tags($content, $allowedTags);
                    
                    preg_match('/<p[^>]*>.*?<\/p>/s', $cleanContent, $firstParagraph);
                    $firstPara = !empty($firstParagraph[0]) ? $firstParagraph[0] : '';
                    $remainingContent = $firstPara ? str_replace($firstPara, '', $cleanContent) : $cleanContent;
                    ?>
                    
                    <?php if ($firstPara): ?>
                        <div class="blog-content-first-paragraph">
                            <?php echo $firstPara; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($post['featured_image']) && file_exists($post['featured_image'])): ?>
                        <div class="blog-content-image">
                            <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                        </div>
                    <?php endif; ?>
                    
                    <div class="blog-content-remaining blog-content">
                        <?php echo $remainingContent; ?>
                    </div>
                </div>
                
                <?php if ($isOwner): ?>
                    <div class="blog-actions">
                        <a href="edit_blog.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary">✏️ Edit Post</a>
                        <a href="delete_blog.php?id=<?php echo $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this post?');">🗑️ Delete Post</a>
                    </div>
                <?php endif; ?>
            </article>

            <!-- ENHANCED RATING SECTION -->
            <section class="rating-section fade-in">
                <h3>Rate this Post</h3>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Logged-in User Rating -->
                    <div class="rating-input">
                        <p>How would you rate this article? Click on a star below:</p>
                        <div class="rating-stars-input" id="ratingStars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star-input <?php echo $i <= $userRating ? 'selected' : ''; ?>" data-rating="<?php echo $i; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <?php if ($userRating > 0): ?>
                            <p class="user-rating-text">Your rating: <?php echo $userRating; ?> stars</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Public Rating (Not Logged In) -->
                    <div class="rating-public">
                        <p>Rate this article anonymously (one rating per visitor):</p>
                        <div class="rating-stars-public" id="ratingStarsPublic">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star-public" data-rating="<?php echo $i; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <div class="rating-message" id="ratingMessage" style="display: none;"></div>
                    </div>
                <?php endif; ?>
            </section>

            <!-- ENHANCED COMMENTS SECTION -->
            <section class="comments-section fade-in">
                <h3>Comments (<?php echo count($comments); ?>)</h3>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="comment-form">
                        <h4>Share your thoughts</h4>
                        <form id="commentForm">
                            <textarea 
                                id="commentText" 
                                name="comment" 
                                placeholder="What did you think about this article? Share your insights..." 
                                required 
                                maxlength="1000"
                                rows="4"
                            ></textarea>
                            <div class="comment-form-actions">
                                <span class="char-count"><span id="charCount">0</span>/1000 characters</span>
                                <button type="submit" class="btn btn-primary">Post Comment</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="login-prompt">
                        <p style="margin-bottom: 1rem; color: #666;">Want to join the discussion?</p>
                        <a href="login.php" class="btn btn-primary">Sign in to comment</a>
                    </div>
                <?php endif; ?>

                <div class="comments-list" id="commentsList">
                    <?php if (empty($comments)): ?>
                        <p class="no-comments">No comments yet. Be the first to share your thoughts!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item" data-comment-id="<?php echo $comment['id']; ?>">
                                <div class="comment-header">
                                    <div class="comment-author">
                                        <div class="comment-avatar" style="background-color: <?php echo getAvatarColor($comment['username']); ?>;">
                                            <?php echo strtoupper(substr($comment['username'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                            <span class="comment-time"><?php echo timeAgo($comment['created_at']); ?></span>
                                        </div>
                                    </div>
                                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $comment['user_id']): ?>
                                        <button class="btn-delete-comment" data-comment-id="<?php echo $comment['id']; ?>" title="Delete comment">
                                            🗑️
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <p class="comment-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            
            <div class="back-button fade-in">
                <a href="index.php" class="btn btn-secondary">← Back to Blog</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <?php include 'includes/footer.php'; ?>
    </footer>

    <!-- FIXED JAVASCRIPT -->
   <script>
// Fixed Rating Functionality
const postId = <?php echo $postId; ?>;
const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

console.log('Post ID:', postId);
console.log('Is Logged In:', isLoggedIn);

// USER RATING (Logged In Users)
if (isLoggedIn) {
    // Public Rating System (No Login Required)
// Add this to the existing <script> section in view_blog.php

const ratingStarsPublic = document.querySelectorAll('.star-public');
const ratingMessage = document.getElementById('ratingMessage');

// Check if user has already rated (stored in localStorage)
const userRatingKey = `blog_${postId}_rating`;
const existingRating = localStorage.getItem(userRatingKey);

if (existingRating) {
    updateStarDisplay(parseInt(existingRating));
    showRatingMessage(`You rated this post ${existingRating} stars`, false);
}

ratingStarsPublic.forEach(star => {
    star.addEventListener('click', function() {
        const rating = parseInt(this.dataset.rating);
        submitPublicRating(rating);
    });

    star.addEventListener('mouseenter', function() {
        const rating = parseInt(this.dataset.rating);
        ratingStarsPublic.forEach((s, index) => {
            if (index < rating) {
                s.classList.add('hover');
            } else {
                s.classList.remove('hover');
            }
        });
    });
});

document.getElementById('ratingStarsPublic')?.addEventListener('mouseleave', function() {
    ratingStarsPublic.forEach(s => s.classList.remove('hover'));
});

function submitPublicRating(rating) {
    // Check if already rated
    if (existingRating) {
        showRatingMessage('You have already rated this post', true);
        return;
    }

    const formData = new FormData();
    formData.append('action', 'add_public_rating');
    formData.append('post_id', postId);
    formData.append('rating', rating);

    fetch('api/blog_interactions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Store rating in localStorage
            localStorage.setItem(userRatingKey, rating);
            
            // Update star display
            updateStarDisplay(rating);
            
            // Update average rating display
            document.querySelectorAll('.rating-stars .star, .rating-stars-public .star-public').forEach((star, index) => {
                if (index < Math.round(data.avg_rating)) {
                    star.classList.add('filled');
                    star.classList.add('selected');
                } else {
                    star.classList.remove('filled');
                    star.classList.remove('selected');
                }
            });

            document.querySelector('.rating-text').textContent = 
                `${data.avg_rating} (${data.total_ratings} ${data.total_ratings === 1 ? 'rating' : 'ratings'})`;

            showRatingMessage(`Thank you! You rated this post ${rating} stars`, false);
            showNotification('Rating submitted successfully!', 'success');
        } else {
            showRatingMessage(data.message, true);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showRatingMessage('Failed to submit rating', true);
    });
}

function updateStarDisplay(rating) {
    ratingStarsPublic.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('selected');
        } else {
            star.classList.remove('selected');
        }
    });
}

function showRatingMessage(message, isError) {
    ratingMessage.textContent = message;
    ratingMessage.style.display = 'block';
    ratingMessage.style.color = isError ? 'var(--danger)' : 'var(--primary)';
    
    setTimeout(() => {
        ratingMessage.style.display = 'none';
    }, 3000);
}
}

// PUBLIC RATING (Non-logged-in Users)
if (!isLoggedIn) {
    const ratingStarsPublic = document.querySelectorAll('.star-public');
    const ratingMessage = document.getElementById('ratingMessage');
    
    if (ratingStarsPublic.length > 0) {
        console.log('Public rating stars found:', ratingStarsPublic.length);
        
        // Check if user has already rated (using cookie instead of localStorage)
        const userRatingKey = `blog_${postId}_rating`;
        let existingRating = getCookie(userRatingKey);
        
        if (existingRating) {
            updatePublicStarDisplay(parseInt(existingRating));
            showRatingMessage(`You rated this post ${existingRating} stars`, false);
        }
        
        // Hover effect
        ratingStarsPublic.forEach(star => {
            star.addEventListener('mouseenter', function() {
                const rating = parseInt(this.dataset.rating);
                ratingStarsPublic.forEach((s, index) => {
                    if (index < rating) {
                        s.classList.add('hover');
                    } else {
                        s.classList.remove('hover');
                    }
                });
            });
            
            // Click to rate
            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.rating);
                console.log('Public star clicked, rating:', rating);
                submitPublicRating(rating);
            });
        });
        
        // Remove hover on mouse leave
        const ratingContainerPublic = document.getElementById('ratingStarsPublic');
        if (ratingContainerPublic) {
            ratingContainerPublic.addEventListener('mouseleave', function() {
                ratingStarsPublic.forEach(s => s.classList.remove('hover'));
            });
        }
        
        // Submit public rating
        function submitPublicRating(rating) {
            console.log('Submitting public rating:', rating);
            
            const formData = new FormData();
            formData.append('action', 'add_public_rating');
            formData.append('post_id', postId);
            formData.append('rating', rating);

            console.log('Sending request to api/blog_interactions.php');

            fetch('api/blog_interactions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed response:', data);
                    
                    if (data.success) {
                        // Store rating in cookie
                        setCookie(userRatingKey, rating, 365);
                        
                        // Update star display
                        updatePublicStarDisplay(rating);
                        
                        // Update rating display in header
                        updateRatingDisplay(data.avg_rating, data.total_ratings);
                        
                        // Show success message
                        showRatingMessage(`Thank you! You rated this post ${rating} stars`, false);
                        
                        showNotification('Rating submitted successfully!', 'success');
                    } else {
                        showRatingMessage(data.message || 'Failed to submit rating', true);
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    showRatingMessage('Server error: ' + text, true);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showRatingMessage('Failed to submit rating: ' + error.message, true);
            });
        }
        
        function updatePublicStarDisplay(rating) {
            ratingStarsPublic.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('selected');
                } else {
                    star.classList.remove('selected');
                }
            });
        }
        
        function showRatingMessage(message, isError) {
            if (ratingMessage) {
                ratingMessage.textContent = message;
                ratingMessage.style.display = 'block';
                ratingMessage.style.color = isError ? '#ef4444' : '#ff7a00';
                
                setTimeout(() => {
                    ratingMessage.style.display = 'none';
                }, 5000);
            }
        }
    }
}

// Cookie helper functions
function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
}

function getCookie(name) {
    const nameEQ = name + '=';
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Update rating display in header
function updateRatingDisplay(avgRating, totalRatings) {
    console.log('Updating rating display:', avgRating, totalRatings);
    
    // Update stars in header
    document.querySelectorAll('.blog-post-rating-inline .star').forEach((star, index) => {
        if (index < Math.round(avgRating)) {
            star.classList.add('filled');
        } else {
            star.classList.remove('filled');
        }
    });

    // Update rating text
    const ratingTextEl = document.querySelector('.blog-post-rating-inline .rating-text');
    if (ratingTextEl) {
        ratingTextEl.textContent = `${avgRating} (${totalRatings})`;
    }
}

// Comment form submission
const commentForm = document.getElementById('commentForm');
const commentText = document.getElementById('commentText');
const charCount = document.getElementById('charCount');

if (commentText) {
    commentText.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });
}

if (commentForm) {
    commentForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const comment = commentText.value.trim();
        if (!comment) {
            showNotification('Please enter a comment', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add_comment');
        formData.append('post_id', postId);
        formData.append('comment', comment);

        fetch('api/blog_interactions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                addCommentToList(data.comment);
                commentText.value = '';
                charCount.textContent = '0';
                showNotification('Comment posted successfully!', 'success');

                const noComments = document.querySelector('.no-comments');
                if (noComments) noComments.remove();

                const commentHeader = document.querySelector('.comments-section h3');
                const currentCount = parseInt(commentHeader.textContent.match(/\d+/)[0]);
                commentHeader.textContent = `Comments (${currentCount + 1})`;
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to post comment', 'error');
        });
    });
}

// Delete comment
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-delete-comment')) {
        if (!confirm('Are you sure you want to delete this comment?')) return;

        const commentId = e.target.dataset.commentId;
        const formData = new FormData();
        formData.append('action', 'delete_comment');
        formData.append('post_id', postId);
        formData.append('comment_id', commentId);

        fetch('api/blog_interactions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
                commentItem.style.opacity = '0';
                commentItem.style.transform = 'translateX(-20px)';
                setTimeout(() => commentItem.remove(), 300);

                const commentHeader = document.querySelector('.comments-section h3');
                const currentCount = parseInt(commentHeader.textContent.match(/\d+/)[0]);
                commentHeader.textContent = `Comments (${currentCount - 1})`;

                if (currentCount - 1 === 0) {
                    document.getElementById('commentsList').innerHTML = 
                        '<p class="no-comments">No comments yet. Be the first to share your thoughts!</p>';
                }

                showNotification('Comment deleted successfully!', 'success');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to delete comment', 'error');
        });
    }
});

function addCommentToList(comment) {
    const commentsList = document.getElementById('commentsList');
    const avatarColor = getRandomColor();
    
    const commentHTML = `
        <div class="comment-item" data-comment-id="${comment.id}" style="opacity: 0; transform: translateY(20px);">
            <div class="comment-header">
                <div class="comment-author">
                    <div class="comment-avatar" style="background-color: ${avatarColor};">
                        ${comment.username.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <strong>${escapeHtml(comment.username)}</strong>
                        <span class="comment-time">just now</span>
                    </div>
                </div>
                <button class="btn-delete-comment" data-comment-id="${comment.id}" title="Delete comment">
                    🗑️
                </button>
            </div>
            <p class="comment-text">${escapeHtml(comment.comment).replace(/\n/g, '<br>')}</p>
        </div>
    `;
    
    commentsList.insertAdjacentHTML('afterbegin', commentHTML);
    
    setTimeout(() => {
        const newComment = commentsList.firstElementChild;
        newComment.style.transition = 'all 0.3s ease';
        newComment.style.opacity = '1';
        newComment.style.transform = 'translateY(0)';
    }, 10);
}

function getRandomColor() {
    const colors = ['#ff7a00', '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
    return colors[Math.floor(Math.random() * colors.length)];
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('show');
    }, 10);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Intersection observer for animations
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
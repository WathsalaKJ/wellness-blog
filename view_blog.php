<?php
/**
 * View Single Blog Post - Enhanced with Comments and Ratings
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

// Enhanced owner check with type casting
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
    <meta name="description" content="<?php echo htmlspecialchars(substr(strip_tags($post['content']), 0, 160)); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($post['title']); ?> - SoulBalance">
    <meta property="og:description" content="<?php echo htmlspecialchars(substr(strip_tags($post['content']), 0, 160)); ?>">
    <title><?php echo htmlspecialchars($post['title']); ?> - SoulBalance</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='45' fill='%23ff7a00'/><text x='50' y='60' font-size='50' font-weight='bold' text-anchor='middle' fill='white' font-family='serif'>S</text></svg>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
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

    
 <!-- Page Hero Section -->
<section class="page-hero">
    <div class="page-hero-overlay"></div>
    <img src="assets/images/blog-hero-bg.jpg" alt="Blog background" class="page-hero-image" onerror="this.src='assets/images/about-hero.jpg'">
    
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
        <div class ="blog-post-container">
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
            
            <!-- Content with Image After First Paragraph -->
            <div class="blog-content-with-image">
                <?php 
                // Split content into paragraphs
                $content = $post['content'];
                $allowedTags = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><ul><ol><li><a><blockquote><code><pre><div>';
                $cleanContent = strip_tags($content, $allowedTags);
                
                // Extract first paragraph
                preg_match('/<p[^>]*>.*?<\/p>/s', $cleanContent, $firstParagraph);
                $firstPara = !empty($firstParagraph[0]) ? $firstParagraph[0] : '';
                $remainingContent = $firstPara ? str_replace($firstPara, '', $cleanContent) : $cleanContent;
                ?>
                
                <!-- First Paragraph -->
                <?php if ($firstPara): ?>
                    <div class="blog-content-first-paragraph">
                        <?php echo $firstPara; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Featured Image After First Paragraph -->
                <?php if (!empty($post['featured_image']) && file_exists($post['featured_image'])): ?>
                    <div class="blog-content-image">
                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                    </div>
                <?php endif; ?>
                
                <!-- Remaining Content -->
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
        <div>

        <!-- Rating Section -->
        <section class="rating-section fade-in">
            <h3>Rate this Post</h3>
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Logged-in User Rating -->
                <div class="rating-input">
                    <p>Click on a star to rate (1-5)</p>
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
                    
                    <p>Click on a star to rate (1-5)</p>
                    <div class="rating-stars-public" id="ratingStarsPublic">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star-public" data-rating="<?php echo $i; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <div class="rating-message" id="ratingMessage"></div>
                </div>
            <?php endif; ?>
        </section>

       
            <!-- Comments Section -->
            <section class="comments-section fade-in">
                <h3>Comments (<?php echo count($comments); ?>)</h3>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="comment-form">
                        <h4>Leave a Comment</h4>
                        <form id="commentForm">
                            <textarea 
                                id="commentText" 
                                name="comment" 
                                placeholder="Share your thoughts..." 
                                required 
                                maxlength="1000"
                                rows="4"
                            ></textarea>
                            <div class="comment-form-actions">
                                <span class="char-count"><span id="charCount">0</span>/1000</span>
                                <button type="submit" class="btn btn-primary">Post Comment</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <p class="login-prompt"><a href="login.php" class="btn btn-primary btn-sm">Login to comment</a></p>
                <?php endif; ?>

                <div class="comments-list" id="commentsList">
                    <?php if (empty($comments)): ?>
                        <p class="no-comments">No comments yet. Be the first to comment!</p>
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

    <script>
        const postId = <?php echo $postId; ?>;
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

// Rating functionality
        document.querySelectorAll('.star-input').forEach(star => {
            star.addEventListener('click', function() {
                // Public Rating functionality (for non-logged-in users)
// Public Rating functionality (for non-logged-in users) - FIXED
if (!isLoggedIn) {
    const ratingStarsPublic = document.querySelectorAll('.star-public');
    const ratingMessage = document.getElementById('ratingMessage');
    
    if (ratingStarsPublic.length > 0 && ratingMessage) {
        // Check if user has already rated (stored in localStorage)
        const userRatingKey = `blog_${postId}_rating`;
        let existingRating = localStorage.getItem(userRatingKey);
        
        if (existingRating) {
            updatePublicStarDisplay(parseInt(existingRating));
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
        
        const ratingContainer = document.getElementById('ratingStarsPublic');
        if (ratingContainer) {
            ratingContainer.addEventListener('mouseleave', function() {
                ratingStarsPublic.forEach(s => s.classList.remove('hover'));
            });
        }
        
        function submitRating(rating) {
    console.log('Submitting rating:', rating);
    console.log('Post ID:', postId);
    console.log('Is logged in:', isLoggedIn);
    
    const formData = new FormData();
    formData.append('action', 'add_rating');
    formData.append('post_id', postId);
    formData.append('rating', rating);

    fetch('api/blog_interactions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Update star selection
            document.querySelectorAll('.star-input').forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('selected');
                } else {
                    star.classList.remove('selected');
                }
            });

            // Update rating display in header
            document.querySelectorAll('.blog-post-rating-inline .star').forEach((star, index) => {
                if (index < Math.round(data.avg_rating)) {
                    star.classList.add('filled');
                } else {
                    star.classList.remove('filled');
                }
            });

            const ratingTextEl = document.querySelector('.blog-post-rating-inline .rating-text');
            if (ratingTextEl) {
                ratingTextEl.textContent = `${data.avg_rating} (${data.total_ratings})`;
            }

            // Show user rating text
            let userRatingText = document.querySelector('.user-rating-text');
            if (!userRatingText) {
                userRatingText = document.createElement('p');
                userRatingText.className = 'user-rating-text';
                document.querySelector('.rating-input').appendChild(userRatingText);
            }
            userRatingText.textContent = `Your rating: ${rating} stars`;

            showNotification('Rating submitted successfully!', 'success');
        } else {
            console.error('Rating failed:', data.message);
            showNotification(data.message || 'Failed to submit rating', 'error');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showNotification('Failed to submit rating', 'error');
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
                ratingMessage.style.color = isError ? 'var(--danger)' : 'var(--primary)';
                
                setTimeout(() => {
                    ratingMessage.style.display = 'none';
                }, 5000);
            }
        }
    }
}

            });
        });


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
                        // Add new comment to the list
                        addCommentToList(data.comment);
                        commentText.value = '';
                        charCount.textContent = '0';
                        showNotification('Comment posted successfully!', 'success');

                        // Remove "no comments" message if it exists
                        const noComments = document.querySelector('.no-comments');
                        if (noComments) noComments.remove();

                        // Update comment count
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

                        // Update comment count
                        const commentHeader = document.querySelector('.comments-section h3');
                        const currentCount = parseInt(commentHeader.textContent.match(/\d+/)[0]);
                        commentHeader.textContent = `Comments (${currentCount - 1})`;

                        // Show "no comments" if list is empty
                        if (currentCount - 1 === 0) {
                            document.getElementById('commentsList').innerHTML = 
                                '<p class="no-comments">No comments yet. Be the first to comment!</p>';
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
                            
                        </button>
                    </div>
                    <p class="comment-text">${escapeHtml(comment.comment).replace(/\n/g, '<br>')}</p>
                </div>
            `;
            
            commentsList.insertAdjacentHTML('afterbegin', commentHTML);
            
            // Animate in
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
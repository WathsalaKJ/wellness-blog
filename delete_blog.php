<?php
/**
 * Delete Blog Post - FIXED VERSION
 * Deletes post and associated image file
 */

session_start();
require_once 'config/config.php'; // Using your original filename
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'Please login to delete posts';
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($postId === 0) {
    $_SESSION['error_message'] = 'Invalid post ID';
    header('Location: dashboard.php');
    exit();
}

try {
    $db = getDB();
    
    // Get post details including image path
    $stmt = $db->prepare("SELECT id, user_id, title, featured_image FROM blogPost WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    // Check if post exists
    if (!$post) {
        $_SESSION['error_message'] = 'Post not found';
        header('Location: dashboard.php');
        exit();
    }
    
    // Enhanced authorization check with type casting and logging
    if ((int)$post['user_id'] !== (int)$userId) {
        error_log("Delete permission denied - Post user_id: " . $post['user_id'] . " (type: " . gettype($post['user_id']) . 
                  "), Session user_id: " . $userId . " (type: " . gettype($userId) . ")");
        $_SESSION['error_message'] = 'You do not have permission to delete this post';
        header('Location: dashboard.php');
        exit();
    }
    
    // Delete the image file if it exists
    if ($post['featured_image'] && file_exists($post['featured_image'])) {
        if (!unlink($post['featured_image'])) {
            error_log("Failed to delete image file: " . $post['featured_image']);
        }
    }
    
    // Delete the post from database
    $deleteStmt = $db->prepare("DELETE FROM blogPost WHERE id = ? AND user_id = ?");
    $deleteStmt->execute([$postId, $userId]);
    
    if ($deleteStmt->rowCount() > 0) {
        $_SESSION['success_message'] = 'Post deleted successfully!';
    } else {
        error_log("No rows deleted - Post ID: $postId, User ID: $userId");
        $_SESSION['error_message'] = 'Failed to delete post. Please try again.';
    }
    
} catch (Exception $e) {
    error_log("Delete Post Error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to delete post: ' . $e->getMessage();
}

// Redirect to dashboard
header('Location: dashboard.php');
exit();
?>
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
    $stmt = $db->prepare("SELECT id, user_id, featured_image FROM blogPost WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    // Check if post exists
    if (!$post) {
        $_SESSION['error_message'] = 'Post not found';
        header('Location: dashboard.php');
        exit();
    }
    
    // Check if user owns the post
    if ($post['user_id'] !== $userId) {
        $_SESSION['error_message'] = 'You do not have permission to delete this post';
        header('Location: dashboard.php');
        exit();
    }
    
    // Delete the image file if it exists
    if ($post['featured_image'] && file_exists($post['featured_image'])) {
        unlink($post['featured_image']);
    }
    
    // Delete the post from database
    $deleteStmt = $db->prepare("DELETE FROM blogPost WHERE id = ? AND user_id = ?");
    $deleteStmt->execute([$postId, $userId]);
    
    $_SESSION['success_message'] = 'Post deleted successfully!';
    
} catch (Exception $e) {
    error_log("Delete Post Error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to delete post. Please try again.';
}

// Redirect to dashboard
header('Location: dashboard.php');
exit();
?>
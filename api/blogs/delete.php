<?php
/**
 * Delete Blog Post Endpoint
 * DELETE /api/blogs/delete.php
 * 
 * Expected JSON body:
 * {
 *   "id": number
 * }
 * 
 * Requires authentication
 * Users can only delete their own posts
 */

// Include configuration and database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Allow DELETE and POST requests (some clients don't support DELETE)
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Check if user is authenticated
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required. Please login.'
        ]);
        exit();
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($input['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Post ID is required'
        ]);
        exit();
    }
    
    $postId = intval($input['id']);
    $userId = $_SESSION['user_id'];
    
    // Get database connection
    $db = getDB();
    
    // Check if post exists and belongs to the current user
    $stmt = $db->prepare("SELECT user_id, title FROM blogPost WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    if (!$post) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Blog post not found'
        ]);
        exit();
    }
    
    // Authorization check: ensure user owns this post
    if ($post['user_id'] != $userId) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'You are not authorized to delete this post'
        ]);
        exit();
    }
    
    // Delete the blog post
    $stmt = $db->prepare("DELETE FROM blogPost WHERE id = ?");
    $stmt->execute([$postId]);
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Blog post deleted successfully',
        'deleted_post' => [
            'id' => $postId,
            'title' => $post['title']
        ]
    ]);
    
} catch (PDOException $e) {
    // Log error
    error_log("Delete Post Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete blog post. Please try again.'
    ]);
} catch (Exception $e) {
    error_log("Delete Post Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting the post'
    ]);
}
?>

<?php
/**
 * Update Blog Post Endpoint
 * PUT /api/blogs/update.php
 * 
 * Expected JSON body:
 * {
 *   "id": number,
 *   "title": "string",
 *   "content": "string"
 * }
 * 
 * Requires authentication
 * Users can only update their own posts
 */

// Include configuration and database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Allow PUT and POST requests (some clients don't support PUT)
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    if (empty($input['id']) || empty($input['title']) || empty($input['content'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Post ID, title, and content are required'
        ]);
        exit();
    }
    
    $postId = intval($input['id']);
    $title = trim($input['title']);
    $content = trim($input['content']);
    $userId = $_SESSION['user_id'];
    
    // Validate title length
    if (strlen($title) > 255) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Title must be less than 255 characters'
        ]);
        exit();
    }
    
    // Validate content (minimum length)
    if (strlen($content) < 10) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Content must be at least 10 characters'
        ]);
        exit();
    }
    
    // Get database connection
    $db = getDB();
    
    // Check if post exists and belongs to the current user
    $stmt = $db->prepare("SELECT user_id FROM blogPost WHERE id = ?");
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
            'message' => 'You are not authorized to update this post'
        ]);
        exit();
    }
    
    // Update the blog post
    $stmt = $db->prepare("
        UPDATE blogPost 
        SET title = ?, content = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    $stmt->execute([$title, $content, $postId]);
    
    // Retrieve the updated post with author info
    $stmt = $db->prepare("
        SELECT 
            bp.id, 
            bp.title, 
            bp.content, 
            bp.created_at, 
            bp.updated_at,
            u.id as author_id,
            u.username as author_name
        FROM blogPost bp
        JOIN user u ON bp.user_id = u.id
        WHERE bp.id = ?
    ");
    $stmt->execute([$postId]);
    $updatedPost = $stmt->fetch();
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Blog post updated successfully',
        'post' => $updatedPost
    ]);
    
} catch (PDOException $e) {
    // Log error
    error_log("Update Post Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update blog post. Please try again.'
    ]);
} catch (Exception $e) {
    error_log("Update Post Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating the post'
    ]);
}
?>

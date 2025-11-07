<?php
/**
 * Create Blog Post Endpoint
 * POST /api/blogs/create.php
 * 
 * Expected JSON body:
 * {
 *   "title": "string",
 *   "content": "string" (markdown or HTML)
 * }
 * 
 * Requires authentication
 */

// Include configuration and database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    if (empty($input['title']) || empty($input['content'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Title and content are required'
        ]);
        exit();
    }
    
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
    
    // Insert new blog post
    $stmt = $db->prepare("
        INSERT INTO blogPost (user_id, title, content) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$userId, $title, $content]);
    
    // Get the new post's ID
    $postId = $db->lastInsertId();
    
    // Retrieve the newly created post with author info
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
    $post = $stmt->fetch();
    
    // Return success response
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Blog post created successfully',
        'post' => $post
    ]);
    
} catch (PDOException $e) {
    // Log error
    error_log("Create Post Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create blog post. Please try again.'
    ]);
} catch (Exception $e) {
    error_log("Create Post Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while creating the post'
    ]);
}
?>

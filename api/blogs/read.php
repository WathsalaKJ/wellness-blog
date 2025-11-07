<?php
/**
 * Read Blog Posts Endpoint
 * GET /api/blogs/read.php
 * 
 * Query parameters:
 * - id: Get single blog post by ID
 * - user_id: Get all posts by specific user
 * - (no params): Get all blog posts
 * 
 * No authentication required for reading
 */

// Include configuration and database
require_once '../../config/config.php';
require_once '../../config/database.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get database connection
    $db = getDB();
    
    // Check if requesting a single post by ID
    if (isset($_GET['id'])) {
        $postId = intval($_GET['id']);
        
        // Get single post with author information
        $stmt = $db->prepare("
            SELECT 
                bp.id, 
                bp.title, 
                bp.content, 
                bp.created_at, 
                bp.updated_at,
                bp.user_id,
                u.username as author_name,
                u.email as author_email
            FROM blogPost bp
            JOIN user u ON bp.user_id = u.id
            WHERE bp.id = ?
        ");
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
        
        // Return single post
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'post' => $post
        ]);
        
    } 
    // Check if requesting posts by specific user
    elseif (isset($_GET['user_id'])) {
        $userId = intval($_GET['user_id']);
        
        // Get all posts by this user
        $stmt = $db->prepare("
            SELECT 
                bp.id, 
                bp.title, 
                bp.content, 
                bp.created_at, 
                bp.updated_at,
                bp.user_id,
                u.username as author_name
            FROM blogPost bp
            JOIN user u ON bp.user_id = u.id
            WHERE bp.user_id = ?
            ORDER BY bp.created_at DESC
        ");
        $stmt->execute([$userId]);
        $posts = $stmt->fetchAll();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'count' => count($posts),
            'posts' => $posts
        ]);
        
    } 
    // Get all posts
    else {
        // Get all blog posts with author information, ordered by newest first
        $stmt = $db->query("
            SELECT 
                bp.id, 
                bp.title, 
                bp.content, 
                bp.created_at, 
                bp.updated_at,
                bp.user_id,
                u.username as author_name
            FROM blogPost bp
            JOIN user u ON bp.user_id = u.id
            ORDER BY bp.created_at DESC
        ");
        $posts = $stmt->fetchAll();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'count' => count($posts),
            'posts' => $posts
        ]);
    }
    
} catch (PDOException $e) {
    // Log error
    error_log("Read Posts Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve blog posts'
    ]);
} catch (Exception $e) {
    error_log("Read Posts Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while retrieving posts'
    ]);
}
?>

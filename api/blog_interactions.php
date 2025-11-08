<?php
/**
 * Blog Interactions Handler - Comments and Ratings
 */

session_start();
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Please login to interact']);
        exit();
    }

    $action = $_POST['action'] ?? '';
    $postId = intval($_POST['post_id'] ?? 0);
    $userId = $_SESSION['user_id'];

    if ($postId === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
        exit();
    }

    try {
        $db = getDB();

        if ($action === 'add_comment') {
            $comment = trim($_POST['comment'] ?? '');
            
            if (empty($comment)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
                exit();
            }

            if (strlen($comment) > 1000) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Comment too long (max 1000 characters)']);
                exit();
            }

            $stmt = $db->prepare("INSERT INTO blog_comments (blog_post_id, user_id, comment) VALUES (?, ?, ?)");
            $stmt->execute([$postId, $userId, $comment]);

            // Get the newly created comment with user info
            $commentId = $db->lastInsertId();
            $stmt = $db->prepare("
                SELECT c.id, c.comment, c.created_at, u.username 
                FROM blog_comments c 
                JOIN user u ON c.user_id = u.id 
                WHERE c.id = ?
            ");
            $stmt->execute([$commentId]);
            $newComment = $stmt->fetch();

            echo json_encode([
                'success' => true, 
                'message' => 'Comment added successfully',
                'comment' => $newComment
            ]);

        } elseif ($action === 'add_rating') {
            $rating = intval($_POST['rating'] ?? 0);
            
            if ($rating < 1 || $rating > 5) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
                exit();
            }

            // Insert or update rating
            $stmt = $db->prepare("
                INSERT INTO blog_ratings (blog_post_id, user_id, rating) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE rating = ?, updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$postId, $userId, $rating, $rating]);

            // Get updated average rating
            $stmt = $db->prepare("
                SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings 
                FROM blog_ratings 
                WHERE blog_post_id = ?
            ");
            $stmt->execute([$postId]);
            $ratingData = $stmt->fetch();

            echo json_encode([
                'success' => true, 
                'message' => 'Rating submitted successfully',
                'avg_rating' => round($ratingData['avg_rating'], 1),
                'total_ratings' => $ratingData['total_ratings']
            ]);

         } elseif ($action === 'add_public_rating') {
    $rating = intval($_POST['rating'] ?? 0);
    
    if ($rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
        exit();
    }

    // Use IP address as identifier for public ratings
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Check if this IP has already rated this post
    $stmt = $db->prepare("
        SELECT id FROM blog_ratings_public 
        WHERE blog_post_id = ? AND ip_address = ?
    ");
    $stmt->execute([$postId, $ipAddress]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'You have already rated this post']);
        exit();
    }

    // Insert public rating
    $stmt = $db->prepare("
        INSERT INTO blog_ratings_public (blog_post_id, ip_address, rating) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$postId, $ipAddress, $rating]);

    // Get updated average rating (combine user and public ratings)
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

    echo json_encode([
        'success' => true, 
        'message' => 'Rating submitted successfully',
        'avg_rating' => $avgRating,
        'total_ratings' => $totalRatings
    ]);


    } elseif ($action === 'delete_comment') {
            $commentId = intval($_POST['comment_id'] ?? 0);
            
            // Check if user owns the comment
            $stmt = $db->prepare("SELECT user_id FROM blog_comments WHERE id = ?");
            $stmt->execute([$commentId]);
            $comment = $stmt->fetch();
            
            if (!$comment || $comment['user_id'] !== $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            $stmt = $db->prepare("DELETE FROM blog_comments WHERE id = ? AND user_id = ?");
            $stmt->execute([$commentId, $userId]);

            echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);

        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }

    } catch (Exception $e) {
        error_log("Blog Interaction Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'An error occurred']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
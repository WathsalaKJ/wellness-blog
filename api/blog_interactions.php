<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$action = $_POST['action'] ?? '';
$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

if ($postId === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit();
}

try {
    $db = getDB();
    
    switch ($action) {
        case 'add_rating':
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Please login to rate']);
                exit();
            }
            
            $userId = $_SESSION['user_id'];
            $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
            
            if ($rating < 1 || $rating > 5) {
                echo json_encode(['success' => false, 'message' => 'Invalid rating']);
                exit();
            }
            
            // Check if user already rated
            $stmt = $db->prepare("SELECT id FROM blog_ratings WHERE blog_post_id = ? AND user_id = ?");
            $stmt->execute([$postId, $userId]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing rating
                $stmt = $db->prepare("UPDATE blog_ratings SET rating = ?, created_at = NOW() WHERE blog_post_id = ? AND user_id = ?");
                $stmt->execute([$rating, $postId, $userId]);
            } else {
                // Insert new rating
                $stmt = $db->prepare("INSERT INTO blog_ratings (blog_post_id, user_id, rating, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$postId, $userId, $rating]);
            }
            
            // Get updated average rating (combined user and public ratings)
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
                'avg_rating' => $avgRating,
                'total_ratings' => $totalRatings,
                'user_rating' => $rating
            ]);
            break;
            
        case 'add_public_rating':
            $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
            
            if ($rating < 1 || $rating > 5) {
                echo json_encode(['success' => false, 'message' => 'Invalid rating']);
                exit();
            }
            
            // Get user's IP address for tracking
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            
            // Check if this IP already rated this post
            $stmt = $db->prepare("SELECT id FROM blog_ratings_public WHERE blog_post_id = ? AND ip_address = ?");
            $stmt->execute([$postId, $ipAddress]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'You have already rated this post']);
                exit();
            }
            
            // Insert public rating
            $stmt = $db->prepare("INSERT INTO blog_ratings_public (blog_post_id, rating, ip_address, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$postId, $rating, $ipAddress]);
            
            // Get updated average rating (combined user and public ratings)
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
                'avg_rating' => $avgRating,
                'total_ratings' => $totalRatings
            ]);
            break;
            
        case 'add_comment':
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Please login to comment']);
                exit();
            }
            
            $userId = $_SESSION['user_id'];
            $comment = trim($_POST['comment'] ?? '');
            
            if (empty($comment)) {
                echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
                exit();
            }
            
            if (strlen($comment) > 1000) {
                echo json_encode(['success' => false, 'message' => 'Comment is too long']);
                exit();
            }
            
            $stmt = $db->prepare("INSERT INTO blog_comments (blog_post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$postId, $userId, $comment]);
            
            $commentId = $db->lastInsertId();
            
            // Get username
            $stmt = $db->prepare("SELECT username FROM user WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'comment' => [
                    'id' => $commentId,
                    'username' => $user['username'],
                    'comment' => $comment,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
            break;
            
        case 'delete_comment':
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Please login']);
                exit();
            }
            
            $commentId = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
            $userId = $_SESSION['user_id'];
            
            // Verify ownership
            $stmt = $db->prepare("SELECT user_id FROM blog_comments WHERE id = ? AND blog_post_id = ?");
            $stmt->execute([$commentId, $postId]);
            $comment = $stmt->fetch();
            
            if (!$comment || $comment['user_id'] != $userId) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }
            
            $stmt = $db->prepare("DELETE FROM blog_comments WHERE id = ?");
            $stmt->execute([$commentId]);
            
            echo json_encode(['success' => true]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
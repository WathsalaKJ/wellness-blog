<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

session_start();
require_once __DIR__ . '/../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');

// Function to send JSON response and exit
function sendResponse($success, $data = [], $message = '') {
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    $response = array_merge($response, $data);
    echo json_encode($response);
    exit();
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, [], 'Invalid request method');
}

$action = $_POST['action'] ?? '';
$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

if ($postId === 0) {
    sendResponse(false, [], 'Invalid post ID');
}

try {
    $db = getDB();
    
    switch ($action) {
        case 'add_rating':
            if (!isset($_SESSION['user_id'])) {
                sendResponse(false, [], 'Please login to rate');
            }
            
            $userId = intval($_SESSION['user_id']);
            $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
            
            if ($rating < 1 || $rating > 5) {
                sendResponse(false, [], 'Invalid rating value');
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
            
            // Get updated average rating
            $stmt = $db->prepare("
                SELECT 
                    COALESCE(AVG(rating), 0) as user_avg,
                    COUNT(*) as user_count
                FROM blog_ratings 
                WHERE blog_post_id = ?
            ");
            $stmt->execute([$postId]);
            $userRatings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $db->prepare("
                SELECT 
                    COALESCE(AVG(rating), 0) as public_avg,
                    COUNT(*) as public_count
                FROM blog_ratings_public 
                WHERE blog_post_id = ?
            ");
            $stmt->execute([$postId]);
            $publicRatings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $userCount = intval($userRatings['user_count']);
            $publicCount = intval($publicRatings['public_count']);
            $totalRatings = $userCount + $publicCount;
            
            $avgRating = 0;
            if ($totalRatings > 0) {
                $userSum = floatval($userRatings['user_avg']) * $userCount;
                $publicSum = floatval($publicRatings['public_avg']) * $publicCount;
                $avgRating = round(($userSum + $publicSum) / $totalRatings, 1);
            }
            
            sendResponse(true, [
                'avg_rating' => $avgRating,
                'total_ratings' => $totalRatings,
                'user_rating' => $rating
            ], 'Rating submitted successfully');
            break;
            
        case 'add_public_rating':
            $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
            
            if ($rating < 1 || $rating > 5) {
                sendResponse(false, [], 'Invalid rating value');
            }
            
            // Get user's IP address
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            
            // Check if this IP already rated
            $stmt = $db->prepare("SELECT id FROM blog_ratings_public WHERE blog_post_id = ? AND ip_address = ?");
            $stmt->execute([$postId, $ipAddress]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                sendResponse(false, [], 'You have already rated this post');
            }
            
            // Insert public rating
            $stmt = $db->prepare("INSERT INTO blog_ratings_public (blog_post_id, rating, ip_address, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$postId, $rating, $ipAddress]);
            
            // Get updated average rating
            $stmt = $db->prepare("
                SELECT 
                    COALESCE(AVG(rating), 0) as user_avg,
                    COUNT(*) as user_count
                FROM blog_ratings 
                WHERE blog_post_id = ?
            ");
            $stmt->execute([$postId]);
            $userRatings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $db->prepare("
                SELECT 
                    COALESCE(AVG(rating), 0) as public_avg,
                    COUNT(*) as public_count
                FROM blog_ratings_public 
                WHERE blog_post_id = ?
            ");
            $stmt->execute([$postId]);
            $publicRatings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $userCount = intval($userRatings['user_count']);
            $publicCount = intval($publicRatings['public_count']);
            $totalRatings = $userCount + $publicCount;
            
            $avgRating = 0;
            if ($totalRatings > 0) {
                $userSum = floatval($userRatings['user_avg']) * $userCount;
                $publicSum = floatval($publicRatings['public_avg']) * $publicCount;
                $avgRating = round(($userSum + $publicSum) / $totalRatings, 1);
            }
            
            sendResponse(true, [
                'avg_rating' => $avgRating,
                'total_ratings' => $totalRatings
            ], 'Rating submitted successfully');
            break;
            
        case 'add_comment':
            if (!isset($_SESSION['user_id'])) {
                sendResponse(false, [], 'Please login to comment');
            }
            
            $userId = intval($_SESSION['user_id']);
            $comment = trim($_POST['comment'] ?? '');
            
            if (empty($comment)) {
                sendResponse(false, [], 'Comment cannot be empty');
            }
            
            if (strlen($comment) > 1000) {
                sendResponse(false, [], 'Comment is too long (max 1000 characters)');
            }
            
            $stmt = $db->prepare("INSERT INTO blog_comments (blog_post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$postId, $userId, $comment]);
            
            $commentId = $db->lastInsertId();
            
            // Get username
            $stmt = $db->prepare("SELECT username FROM user WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendResponse(true, [
                'comment' => [
                    'id' => $commentId,
                    'username' => $user['username'],
                    'comment' => $comment,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ], 'Comment posted successfully');
            break;
            
        case 'delete_comment':
            if (!isset($_SESSION['user_id'])) {
                sendResponse(false, [], 'Please login');
            }
            
            $commentId = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
            $userId = intval($_SESSION['user_id']);
            
            if ($commentId === 0) {
                sendResponse(false, [], 'Invalid comment ID');
            }
            
            // Verify ownership
            $stmt = $db->prepare("SELECT user_id FROM blog_comments WHERE id = ? AND blog_post_id = ?");
            $stmt->execute([$commentId, $postId]);
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$comment || intval($comment['user_id']) !== $userId) {
                sendResponse(false, [], 'Unauthorized - You can only delete your own comments');
            }
            
            $stmt = $db->prepare("DELETE FROM blog_comments WHERE id = ?");
            $stmt->execute([$commentId]);
            
            sendResponse(true, [], 'Comment deleted successfully');
            break;
            
        default:
            sendResponse(false, [], 'Invalid action: ' . $action);
    }
    
} catch (PDOException $e) {
    // Log the actual error
    error_log('Database error in blog_interactions.php: ' . $e->getMessage());
    sendResponse(false, [], 'Database error occurred. Please try again later.');
} catch (Exception $e) {
    // Log the actual error
    error_log('Error in blog_interactions.php: ' . $e->getMessage());
    sendResponse(false, [], 'An error occurred. Please try again later.');
}
?>
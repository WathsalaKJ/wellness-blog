<?php
// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log("=== Blog Interactions API Called ===");

// Start output buffering
ob_start();

session_start();
error_log("Session ID: " . session_id());
error_log("Session User ID: " . ($_SESSION['user_id'] ?? 'NOT SET'));

// Load configuration
require_once __DIR__ . '/../config/database.php';

// Clear any output and set JSON header
ob_clean();
header('Content-Type: application/json');

// Log request data
error_log("POST data: " . print_r($_POST, true));
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);

function sendJSON($data) {
    error_log("Sending response: " . json_encode($data));
    echo json_encode($data);
    exit();
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method");
    sendJSON(['success' => false, 'message' => 'Invalid request method']);
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

error_log("Action: $action, Post ID: $postId");

if (empty($action)) {
    sendJSON(['success' => false, 'message' => 'No action specified']);
}

if ($postId === 0) {
    sendJSON(['success' => false, 'message' => 'Invalid post ID']);
}

try {
    $db = getDB();
    error_log("Database connection successful");
    
    switch ($action) {
        case 'add_rating':
            error_log("Processing add_rating");
            
            if (!isset($_SESSION['user_id'])) {
                error_log("User not logged in");
                sendJSON(['success' => false, 'message' => 'Please login to rate']);
            }
            
            $userId = intval($_SESSION['user_id']);
            $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
            
            error_log("User ID: $userId, Rating: $rating");
            
            if ($rating < 1 || $rating > 5) {
                error_log("Invalid rating value: $rating");
                sendJSON(['success' => false, 'message' => 'Rating must be between 1 and 5']);
            }
            
            // Check if user already rated
            $stmt = $db->prepare("SELECT id FROM blog_ratings WHERE blog_post_id = ? AND user_id = ?");
            $stmt->execute([$postId, $userId]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                error_log("Updating existing rating");
                $stmt = $db->prepare("UPDATE blog_ratings SET rating = ?, created_at = NOW() WHERE blog_post_id = ? AND user_id = ?");
                $stmt->execute([$rating, $postId, $userId]);
            } else {
                error_log("Inserting new rating");
                $stmt = $db->prepare("INSERT INTO blog_ratings (blog_post_id, user_id, rating, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$postId, $userId, $rating]);
            }
            
            error_log("Rating saved successfully");
            
            // Calculate average
            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) as user_count,
                    COALESCE(AVG(rating), 0) as user_avg
                FROM blog_ratings 
                WHERE blog_post_id = ?
            ");
            $stmt->execute([$postId]);
            $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) as public_count,
                    COALESCE(AVG(rating), 0) as public_avg
                FROM blog_ratings_public 
                WHERE blog_post_id = ?
            ");
            $stmt->execute([$postId]);
            $publicStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $userCount = intval($userStats['user_count']);
            $publicCount = intval($publicStats['public_count']);
            $userAvg = floatval($userStats['user_avg']);
            $publicAvg = floatval($publicStats['public_avg']);
            
            $totalCount = $userCount + $publicCount;
            $avgRating = 0;
            
            if ($totalCount > 0) {
                $avgRating = round((($userAvg * $userCount) + ($publicAvg * $publicCount)) / $totalCount, 1);
            }
            
            error_log("Stats - User: $userCount/$userAvg, Public: $publicCount/$publicAvg, Total: $totalCount/$avgRating");
            
            sendJSON([
                'success' => true,
                'message' => 'Rating submitted successfully',
                'avg_rating' => $avgRating,
                'total_ratings' => $totalCount,
                'user_rating' => $rating
            ]);
            break;
            
        case 'add_public_rating':
            error_log("Processing add_public_rating");
            
            $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
            
            if ($rating < 1 || $rating > 5) {
                error_log("Invalid rating value: $rating");
                sendJSON(['success' => false, 'message' => 'Rating must be between 1 and 5']);
            }
            
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            error_log("IP Address: $ipAddress, Rating: $rating");
            
            // Check if IP already rated
            $stmt = $db->prepare("SELECT id FROM blog_ratings_public WHERE blog_post_id = ? AND ip_address = ?");
            $stmt->execute([$postId, $ipAddress]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                error_log("IP already rated this post");
                sendJSON(['success' => false, 'message' => 'You have already rated this post']);
            }
            
            error_log("Inserting public rating");
            $stmt = $db->prepare("INSERT INTO blog_ratings_public (blog_post_id, rating, ip_address, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$postId, $rating, $ipAddress]);
            
            error_log("Public rating saved successfully");
            
            // Calculate average
            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) as user_count,
                    COALESCE(AVG(rating), 0) as user_avg
                FROM blog_ratings 
                WHERE blog_post_id = ?
            ");
            $stmt->execute([$postId]);
            $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) as public_count,
                    COALESCE(AVG(rating), 0) as public_avg
                FROM blog_ratings_public 
                WHERE blog_post_id = ?
            ");
            $stmt->execute([$postId]);
            $publicStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $userCount = intval($userStats['user_count']);
            $publicCount = intval($publicStats['public_count']);
            $userAvg = floatval($userStats['user_avg']);
            $publicAvg = floatval($publicStats['public_avg']);
            
            $totalCount = $userCount + $publicCount;
            $avgRating = 0;
            
            if ($totalCount > 0) {
                $avgRating = round((($userAvg * $userCount) + ($publicAvg * $publicCount)) / $totalCount, 1);
            }
            
            error_log("Stats - User: $userCount/$userAvg, Public: $publicCount/$publicAvg, Total: $totalCount/$avgRating");
            
            sendJSON([
                'success' => true,
                'message' => 'Rating submitted successfully',
                'avg_rating' => $avgRating,
                'total_ratings' => $totalCount
            ]);
            break;
            
        case 'add_comment':
            error_log("Processing add_comment");
            
            if (!isset($_SESSION['user_id'])) {
                sendJSON(['success' => false, 'message' => 'Please login to comment']);
            }
            
            $userId = intval($_SESSION['user_id']);
            $comment = trim($_POST['comment'] ?? '');
            
            if (empty($comment)) {
                sendJSON(['success' => false, 'message' => 'Comment cannot be empty']);
            }
            
            if (strlen($comment) > 1000) {
                sendJSON(['success' => false, 'message' => 'Comment too long']);
            }
            
            $stmt = $db->prepare("INSERT INTO blog_comments (blog_post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$postId, $userId, $comment]);
            
            $commentId = $db->lastInsertId();
            
            $stmt = $db->prepare("SELECT username FROM user WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendJSON([
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
                sendJSON(['success' => false, 'message' => 'Please login']);
            }
            
            $commentId = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
            $userId = intval($_SESSION['user_id']);
            
            $stmt = $db->prepare("SELECT user_id FROM blog_comments WHERE id = ? AND blog_post_id = ?");
            $stmt->execute([$commentId, $postId]);
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$comment || intval($comment['user_id']) !== $userId) {
                sendJSON(['success' => false, 'message' => 'Unauthorized']);
            }
            
            $stmt = $db->prepare("DELETE FROM blog_comments WHERE id = ?");
            $stmt->execute([$commentId]);
            
            sendJSON(['success' => true]);
            break;
            
        default:
            error_log("Unknown action: $action");
            sendJSON(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    sendJSON(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    sendJSON(['success' => false, 'message' => 'Server error occurred']);
}
?>
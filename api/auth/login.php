<?php
/**
 * User Login Endpoint
 * POST /api/auth/login.php
 * 
 * Expected JSON body:
 * {
 *   "email": "string",
 *   "password": "string"
 * }
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
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($input['email']) || empty($input['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email and password are required'
        ]);
        exit();
    }
    
    $email = trim($input['email']);
    $password = $input['password'];
    
    // Get database connection
    $db = getDB();
    
    // Find user by email
    $stmt = $db->prepare("
        SELECT id, username, email, password, role 
        FROM user 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Check if user exists and password is correct
    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit();
    }
    
    // Create session for the user
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['login_time'] = time();
    
    // Set session lifetime
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    
    // Return success response (don't send password back)
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
    
} catch (PDOException $e) {
    // Log error
    error_log("Login Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Login failed. Please try again.'
    ]);
} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during login'
    ]);
}
?>

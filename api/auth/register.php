<?php
/**
 * User Registration Endpoint
 * POST /api/auth/register.php
 * 
 * Expected JSON body:
 * {
 *   "username": "string",
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
    if (empty($input['username']) || empty($input['email']) || empty($input['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Username, email, and password are required'
        ]);
        exit();
    }
    
    $username = trim($input['username']);
    $email = trim($input['email']);
    $password = $input['password'];
    
    // Validate username (3-50 characters, alphanumeric and underscore only)
    if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Username must be 3-50 characters (letters, numbers, underscore only)'
        ]);
        exit();
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit();
    }
    
    // Validate password (minimum 6 characters)
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 6 characters'
        ]);
        exit();
    }
    
    // Get database connection
    $db = getDB();
    
    // Check if username already exists
    $stmt = $db->prepare("SELECT id FROM user WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Username already exists'
        ]);
        exit();
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM user WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Email already registered'
        ]);
        exit();
    }
    
    // Hash password using bcrypt
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert new user
    $stmt = $db->prepare("
        INSERT INTO user (username, email, password, role) 
        VALUES (?, ?, ?, 'user')
    ");
    $stmt->execute([$username, $email, $hashedPassword]);
    
    // Get the new user's ID
    $userId = $db->lastInsertId();
    
    // Return success response
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'user' => [
            'id' => $userId,
            'username' => $username,
            'email' => $email
        ]
    ]);
    
} catch (PDOException $e) {
    // Log error
    error_log("Registration Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Please try again.'
    ]);
} catch (Exception $e) {
    error_log("Registration Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during registration'
    ]);
}
?>

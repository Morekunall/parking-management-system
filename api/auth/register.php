<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['name']) || !isset($input['email']) || !isset($input['password']) || !isset($input['user_type'])) {
    sendResponse(['success' => false, 'message' => 'All fields required'], 400);
}

$name = $input['name'];
$email = $input['email'];
$password = $input['password'];
$user_type = $input['user_type'];

// Validate user type
if (!in_array($user_type, ['provider', 'customer'])) {
    sendResponse(['success' => false, 'message' => 'Invalid user type'], 400);
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(['success' => false, 'message' => 'Invalid email format'], 400);
}

// Validate password strength
if (strlen($password) < 6) {
    sendResponse(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if email already exists
    $query = "SELECT id FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        sendResponse(['success' => false, 'message' => 'Email already registered'], 409);
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $query = "INSERT INTO users (name, email, password, user_type) VALUES (:name, :email, :password, :user_type)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':user_type', $user_type);
    
    if ($stmt->execute()) {
        sendResponse([
            'success' => true,
            'message' => 'Registration successful'
        ]);
    } else {
        sendResponse(['success' => false, 'message' => 'Registration failed'], 500);
    }
    
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => 'Server error'], 500);
}
?>

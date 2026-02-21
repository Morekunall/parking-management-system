<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email']) || !isset($input['password'])) {
    sendResponse(['success' => false, 'message' => 'Email and password required'], 400);
}

$email = $input['email'];
$password = $input['password'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, name, email, password, user_type FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password'])) {
        sendResponse(['success' => false, 'message' => 'Invalid credentials'], 401);
    }
    
    // Generate JWT token
    $jwt = new JWT();
    $payload = [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'user_type' => $user['user_type'],
        'exp' => time() + (24 * 60 * 60) // 24 hours
    ];
    
    $token = $jwt->generateToken($payload);
    
    // Remove password from response
    unset($user['password']);
    
    sendResponse([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'user' => $user
    ]);
    
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => 'Server error'], 500);
}
?>

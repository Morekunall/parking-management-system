<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['token']) || !isset($input['password'])) {
    sendResponse(['success' => false, 'message' => 'Token and password are required'], 400);
}

$token = $input['token'];
$password = $input['password'];

// Validate password strength
if (strlen($password) < 6) {
    sendResponse(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Find valid token
    $query = "SELECT email, expires_at FROM password_reset_tokens 
              WHERE token = :token AND used = FALSE AND expires_at > NOW()";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    
    $reset_token = $stmt->fetch();
    
    if (!$reset_token) {
        sendResponse(['success' => false, 'message' => 'Invalid or expired reset token'], 400);
    }
    
    $email = $reset_token['email'];
    
    // Hash new password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Update user password
    $query = "UPDATE users SET password = :password WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':email', $email);
    
    if ($stmt->execute()) {
        // Mark token as used
        $query = "UPDATE password_reset_tokens SET used = TRUE WHERE token = :token";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        sendResponse([
            'success' => true,
            'message' => 'Password has been reset successfully'
        ]);
    } else {
        sendResponse(['success' => false, 'message' => 'Failed to update password'], 500);
    }
    
} catch (Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    sendResponse(['success' => false, 'message' => 'Server error'], 500);
}
?>




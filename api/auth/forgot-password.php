<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email'])) {
    sendResponse(['success' => false, 'message' => 'Email is required'], 400);
}

$email = $input['email'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(['success' => false, 'message' => 'Invalid email format'], 400);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        sendResponse(['success' => false, 'message' => 'Database connection failed'], 500);
    }
    
    // Check if user exists
    $query = "SELECT id, name, email FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch();
    
    // Always return success to prevent email enumeration attacks
    if (!$user) {
        sendResponse([
            'success' => true,
            'message' => 'If an account with that email exists, a password reset link has been sent.'
        ]);
    }
    
    // Generate secure random token
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', time() + (60 * 60)); // 1 hour from now
    
    // Check if password_reset_tokens table exists, if not create it
    try {
        $checkTable = $db->query("SHOW TABLES LIKE 'password_reset_tokens'");
        if ($checkTable->rowCount() == 0) {
            // Create the table if it doesn't exist
            $createTable = "CREATE TABLE password_reset_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                token VARCHAR(255) NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                used BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->exec($createTable);
        }
    } catch (Exception $tableError) {
        error_log("Table creation error: " . $tableError->getMessage());
    }
    
    // Invalidate any existing tokens for this email
    $query = "UPDATE password_reset_tokens SET used = TRUE WHERE email = :email AND used = FALSE";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    // Insert new token
    $query = "INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (:email, :token, :expires_at)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':expires_at', $expires_at);
    
    if ($stmt->execute()) {
        // In a real application, you would send an email here
        // For this demo, we'll return the token in the response
        // In production, remove the token from the response and send it via email
        
        $reset_link = "http://localhost/Traffic management/reset-password.html?token=" . $token;
        
        // Simulate email sending (in production, use PHPMailer or similar)
        error_log("Password reset email would be sent to: " . $email);
        error_log("Reset link: " . $reset_link);
        
        sendResponse([
            'success' => true,
            'message' => 'If an account with that email exists, a password reset link has been sent.',
            'reset_link' => $reset_link, // Remove this in production
            'token' => $token, // Remove this in production
            'debug' => 'Token generated successfully'
        ]);
    } else {
        sendResponse(['success' => false, 'message' => 'Failed to generate reset token'], 500);
    }
    
} catch (Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    sendResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}
?>

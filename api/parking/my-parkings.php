<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$auth = validateAuth();

if ($auth['user_type'] !== 'provider') {
    sendResponse(['success' => false, 'message' => 'Access denied'], 403);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM parking_spaces WHERE provider_id = :provider_id ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':provider_id', $auth['user_id']);
    $stmt->execute();
    
    $parkings = $stmt->fetchAll();
    
    sendResponse([
        'success' => true,
        'data' => $parkings
    ]);
    
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => 'Server error'], 500);
}
?>

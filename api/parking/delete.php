<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$auth = validateAuth();

if ($auth['user_type'] !== 'provider') {
    sendResponse(['success' => false, 'message' => 'Access denied'], 403);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['parking_id'])) {
    sendResponse(['success' => false, 'message' => 'Parking ID required'], 400);
}

$parking_id = (int)$input['parking_id'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if parking space exists and belongs to the provider
    $query = "SELECT id FROM parking_spaces WHERE id = :parking_id AND provider_id = :provider_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':parking_id', $parking_id);
    $stmt->bindParam(':provider_id', $auth['user_id']);
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        sendResponse(['success' => false, 'message' => 'Parking space not found or access denied'], 404);
    }
    
    // Check for active bookings
    $query = "SELECT COUNT(*) as count FROM bookings 
              WHERE parking_id = :parking_id 
              AND status IN ('pending', 'confirmed')";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':parking_id', $parking_id);
    $stmt->execute();
    
    $activeBookings = $stmt->fetch();
    
    if ($activeBookings['count'] > 0) {
        sendResponse(['success' => false, 'message' => 'Cannot delete parking space with active bookings'], 409);
    }
    
    // Delete parking space
    $query = "DELETE FROM parking_spaces WHERE id = :parking_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':parking_id', $parking_id);
    
    if ($stmt->execute()) {
        sendResponse([
            'success' => true,
            'message' => 'Parking space deleted successfully'
        ]);
    } else {
        sendResponse(['success' => false, 'message' => 'Failed to delete parking space'], 500);
    }
    
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => 'Server error'], 500);
}
?>

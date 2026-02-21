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
    
    $query = "SELECT b.*, ps.name as parking_name, ps.location, u.name as customer_name 
              FROM bookings b 
              JOIN parking_spaces ps ON b.parking_id = ps.id 
              JOIN users u ON b.customer_id = u.id 
              WHERE ps.provider_id = :provider_id 
              ORDER BY b.booking_date DESC, b.start_time DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':provider_id', $auth['user_id']);
    $stmt->execute();
    
    $bookings = $stmt->fetchAll();
    
    sendResponse([
        'success' => true,
        'data' => $bookings
    ]);
    
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => 'Server error'], 500);
}
?>

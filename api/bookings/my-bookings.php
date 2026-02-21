<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$auth = validateAuth();

if ($auth['user_type'] !== 'customer') {
    sendResponse(['success' => false, 'message' => 'Access denied'], 403);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT b.*, ps.name as parking_name, ps.location, ps.price_per_hour 
              FROM bookings b 
              JOIN parking_spaces ps ON b.parking_id = ps.id 
              WHERE b.customer_id = :customer_id 
              ORDER BY b.booking_date DESC, b.start_time DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':customer_id', $auth['user_id']);
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

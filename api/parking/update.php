<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$auth = validateAuth();

if ($auth['user_type'] !== 'provider') {
    sendResponse(['success' => false, 'message' => 'Access denied'], 403);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['parking_id']) || !isset($input['name']) || !isset($input['location']) || 
    !isset($input['available_slots']) || !isset($input['price_per_hour']) || !isset($input['available_date']) || !isset($input['available_time'])) {
    sendResponse(['success' => false, 'message' => 'All required fields must be provided'], 400);
}

$parking_id = (int)$input['parking_id'];
$provider_id = $auth['user_id'];
$name = $input['name'];
$location = $input['location'];
$available_slots = (int)$input['available_slots'];
$price_per_hour = (float)$input['price_per_hour'];
$available_date = $input['available_date'];
$available_time = $input['available_time'];
$image_url = isset($input['image_url']) ? $input['image_url'] : null;

// Validate data
if ($available_slots <= 0) {
    sendResponse(['success' => false, 'message' => 'Available slots must be greater than 0'], 400);
}

if ($price_per_hour < 0) {
    sendResponse(['success' => false, 'message' => 'Price per hour cannot be negative'], 400);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if parking space exists and belongs to the provider
    $query = "SELECT id FROM parking_spaces WHERE id = :parking_id AND provider_id = :provider_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':parking_id', $parking_id);
    $stmt->bindParam(':provider_id', $provider_id);
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        sendResponse(['success' => false, 'message' => 'Parking space not found or access denied'], 404);
    }
    
    // Update parking space
    $query = "UPDATE parking_spaces SET 
              name = :name, 
              location = :location, 
              available_slots = :available_slots, 
              price_per_hour = :price_per_hour, 
              available_date = :available_date, 
              available_time = :available_time, 
              image_url = :image_url 
              WHERE id = :parking_id AND provider_id = :provider_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':parking_id', $parking_id);
    $stmt->bindParam(':provider_id', $provider_id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':available_slots', $available_slots);
    $stmt->bindParam(':price_per_hour', $price_per_hour);
    $stmt->bindParam(':available_date', $available_date);
    $stmt->bindParam(':available_time', $available_time);
    $stmt->bindParam(':image_url', $image_url);
    
    if ($stmt->execute()) {
        sendResponse([
            'success' => true,
            'message' => 'Parking space updated successfully'
        ]);
    } else {
        sendResponse(['success' => false, 'message' => 'Failed to update parking space'], 500);
    }
    
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => 'Server error'], 500);
}
?>

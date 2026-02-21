<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$auth = validateAuth();

if ($auth['user_type'] !== 'provider') {
    sendResponse(['success' => false, 'message' => 'Access denied'], 403);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['name']) || !isset($input['location']) || !isset($input['available_slots']) || 
    !isset($input['price_per_hour']) || !isset($input['available_date']) || !isset($input['available_time'])) {
    sendResponse(['success' => false, 'message' => 'All required fields must be provided'], 400);
}

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
    
    $query = "INSERT INTO parking_spaces (provider_id, name, location, available_slots, price_per_hour, available_date, available_time, image_url) 
              VALUES (:provider_id, :name, :location, :available_slots, :price_per_hour, :available_date, :available_time, :image_url)";
    
    $stmt = $db->prepare($query);
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
            'message' => 'Parking space added successfully',
            'parking_id' => $db->lastInsertId()
        ]);
    } else {
        sendResponse(['success' => false, 'message' => 'Failed to add parking space'], 500);
    }
    
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => 'Server error'], 500);
}
?>

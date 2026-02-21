<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$auth = validateAuth();

if ($auth['user_type'] !== 'customer') {
    sendResponse(['success' => false, 'message' => 'Access denied'], 403);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['parking_id']) || !isset($input['booking_date']) || 
    !isset($input['start_time']) || !isset($input['end_time'])) {
    sendResponse(['success' => false, 'message' => 'All required fields must be provided'], 400);
}

$customer_id = $auth['user_id'];
$parking_id = (int)$input['parking_id'];
$booking_date = $input['booking_date'];
$start_time = $input['start_time'];
$end_time = $input['end_time'];

// Validate time
if ($start_time >= $end_time) {
    sendResponse(['success' => false, 'message' => 'End time must be after start time'], 400);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get parking space details
    $query = "SELECT * FROM parking_spaces WHERE id = :parking_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':parking_id', $parking_id);
    $stmt->execute();
    
    $parking = $stmt->fetch();
    
    if (!$parking) {
        sendResponse(['success' => false, 'message' => 'Parking space not found'], 404);
    }
    
    // Calculate total cost
    $start = new DateTime($start_time);
    $end = new DateTime($end_time);
    $hours = $end->diff($start)->h + ($end->diff($start)->i / 60);
    $total_cost = $hours * $parking['price_per_hour'];
    
    // Count existing bookings for this parking space on this date
    $query = "SELECT COUNT(*) as count FROM bookings 
              WHERE parking_id = :parking_id 
              AND booking_date = :booking_date 
              AND status IN ('pending', 'confirmed')";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':parking_id', $parking_id);
    $stmt->bindParam(':booking_date', $booking_date);
    $stmt->execute();
    
    $existing_bookings = $stmt->fetch();
    
    // Check if there are available slots
    if ($existing_bookings['count'] >= $parking['available_slots']) {
        sendResponse(['success' => false, 'message' => 'No available slots for this date'], 409);
    }
    
    // Check for overlapping bookings
    $query = "SELECT COUNT(*) as count FROM bookings 
              WHERE parking_id = :parking_id 
              AND booking_date = :booking_date 
              AND status IN ('pending', 'confirmed')
              AND ((start_time <= :start_time AND end_time > :start_time) 
                   OR (start_time < :end_time AND end_time >= :end_time)
                   OR (start_time >= :start_time AND end_time <= :end_time))";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':parking_id', $parking_id);
    $stmt->bindParam(':booking_date', $booking_date);
    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->execute();
    
    $overlap = $stmt->fetch();
    
    if ($overlap['count'] > 0) {
        sendResponse(['success' => false, 'message' => 'Time slot is already booked'], 409);
    }
    
    // Create booking
    $query = "INSERT INTO bookings (customer_id, parking_id, booking_date, start_time, end_time, total_cost) 
              VALUES (:customer_id, :parking_id, :booking_date, :start_time, :end_time, :total_cost)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':customer_id', $customer_id);
    $stmt->bindParam(':parking_id', $parking_id);
    $stmt->bindParam(':booking_date', $booking_date);
    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->bindParam(':total_cost', $total_cost);
    
    if ($stmt->execute()) {
        $booking_id = $db->lastInsertId();
        
        // Calculate remaining slots for this parking space on this date
        $query = "SELECT COUNT(*) as booked_slots FROM bookings 
                  WHERE parking_id = :parking_id 
                  AND booking_date = :booking_date 
                  AND status IN ('pending', 'confirmed')";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':parking_id', $parking_id);
        $stmt->bindParam(':booking_date', $booking_date);
        $stmt->execute();
        
        $booked_slots = $stmt->fetch();
        $remaining_slots = $parking['available_slots'] - $booked_slots['booked_slots'];
        
        sendResponse([
            'success' => true,
            'message' => 'Booking created successfully',
            'booking_id' => $booking_id,
            'total_cost' => $total_cost,
            'remaining_slots' => $remaining_slots,
            'total_slots' => $parking['available_slots']
        ]);
    } else {
        sendResponse(['success' => false, 'message' => 'Failed to create booking'], 500);
    }
    
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => 'Server error'], 500);
}
?>

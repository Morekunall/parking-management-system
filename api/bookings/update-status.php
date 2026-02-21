<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$auth = validateAuth();

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['booking_id']) || !isset($input['status'])) {
    sendResponse(['success' => false, 'message' => 'Booking ID and status required'], 400);
}

$booking_id = (int)$input['booking_id'];
$status = $input['status'];

// Validate status
if (!in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
    sendResponse(['success' => false, 'message' => 'Invalid status'], 400);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if booking exists and user has permission
    $query = "SELECT b.*, ps.provider_id, ps.available_slots 
              FROM bookings b 
              JOIN parking_spaces ps ON b.parking_id = ps.id 
              WHERE b.id = :booking_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':booking_id', $booking_id);
    $stmt->execute();
    
    $booking = $stmt->fetch();
    
    if (!$booking) {
        sendResponse(['success' => false, 'message' => 'Booking not found'], 404);
    }
    
    // Check permissions
    $canUpdate = false;
    if ($auth['user_type'] === 'provider' && $booking['provider_id'] == $auth['user_id']) {
        $canUpdate = true;
    } elseif ($auth['user_type'] === 'customer' && $booking['customer_id'] == $auth['user_id']) {
        // Customers can only cancel their own bookings
        if ($status === 'cancelled') {
            $canUpdate = true;
        }
    }
    
    if (!$canUpdate) {
        sendResponse(['success' => false, 'message' => 'Access denied'], 403);
    }
    
    // Update booking status
    $query = "UPDATE bookings SET status = :status WHERE id = :booking_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':booking_id', $booking_id);
    
    if ($stmt->execute()) {
        // Calculate remaining slots for this parking space on this date
        $query = "SELECT COUNT(*) as booked_slots FROM bookings 
                  WHERE parking_id = :parking_id 
                  AND booking_date = :booking_date 
                  AND status IN ('pending', 'confirmed')";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':parking_id', $booking['parking_id']);
        $stmt->bindParam(':booking_date', $booking['booking_date']);
        $stmt->execute();
        
        $booked_slots = $stmt->fetch();
        $remaining_slots = $booking['available_slots'] - $booked_slots['booked_slots'];
        
        sendResponse([
            'success' => true,
            'message' => 'Booking status updated successfully',
            'remaining_slots' => $remaining_slots,
            'total_slots' => $booking['available_slots']
        ]);
    } else {
        sendResponse(['success' => false, 'message' => 'Failed to update booking status'], 500);
    }
    
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => 'Server error'], 500);
}
?>

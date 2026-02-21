<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

$location = isset($input['location']) ? $input['location'] : '';
$date = isset($input['date']) ? $input['date'] : '';
$time = isset($input['time']) ? $input['time'] : '';
$max_price = isset($input['max_price']) ? (float)$input['max_price'] : null;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT ps.*, u.name as provider_name,
              COALESCE((
                  SELECT COUNT(*) 
                  FROM bookings b 
                  WHERE b.parking_id = ps.id 
                  AND b.booking_date = COALESCE(:date, ps.available_date)
                  AND b.status IN ('pending', 'confirmed')
              ), 0) as booked_slots
              FROM parking_spaces ps 
              JOIN users u ON ps.provider_id = u.id 
              WHERE 1=1";
    
    $params = [];
    
    if (!empty($location)) {
        $query .= " AND ps.location LIKE :location";
        $params[':location'] = "%$location%";
    }
    
    if (!empty($date)) {
        $query .= " AND ps.available_date = :date";
        $params[':date'] = $date;
    }
    
    if (!empty($time)) {
        $query .= " AND ps.available_time <= :time";
        $params[':time'] = $time;
    }
    
    if ($max_price !== null) {
        $query .= " AND ps.price_per_hour <= :max_price";
        $params[':max_price'] = $max_price;
    }
    
    $query .= " ORDER BY ps.price_per_hour ASC";
    
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    $parkings = $stmt->fetchAll();
    
    // Calculate remaining slots for each parking space
    foreach ($parkings as &$parking) {
        $parking['remaining_slots'] = $parking['available_slots'] - $parking['booked_slots'];
        unset($parking['booked_slots']); // Remove booked_slots from response
    }
    
    sendResponse([
        'success' => true,
        'data' => $parkings
    ]);
    
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => 'Server error'], 500);
}
?>

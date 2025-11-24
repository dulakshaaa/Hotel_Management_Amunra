<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// require user to be logged in
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to book']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$room_name = trim($_POST['room_name'] ?? '');
$room_id = (int)($_POST['room_id'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$checkin = $_POST['checkin'] ?? '';
$checkout = $_POST['checkout'] ?? '';
$checkin_time = $_POST['checkin_time'] ?? '14:00';
$checkout_time = $_POST['checkout_time'] ?? '11:00';
$guests = (int)($_POST['guests'] ?? 1);

// validation
if (!$room_name || !$checkin || !$checkout || !$room_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (strtotime($checkout) <= strtotime($checkin)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Checkout date must be after check-in date']);
    exit;
}

if ($guests < 1 || $guests > 4) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid number of guests']);
    exit;
}

try {
    // Check room availability
    $checkAvail = $conn->prepare("SELECT available_rooms FROM rooms WHERE id = ? LIMIT 1");
    if (!$checkAvail) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $checkAvail->bind_param('i', $room_id);
    if (!$checkAvail->execute()) {
        throw new Exception("Execute failed: " . $checkAvail->error);
    }
    
    $availResult = $checkAvail->get_result();
    $availRoom = $availResult->fetch_assoc();
    $checkAvail->close();

    if (!$availRoom || $availRoom['available_rooms'] < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Room is no longer available']);
        exit;
    }

    // Insert reservation
    $stmt = $conn->prepare("INSERT INTO reservations (user_id, room_name, price, checkin, checkout, checkin_time, checkout_time, guests) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param('isdssssi', $user_id, $room_name, $price, $checkin, $checkout, $checkin_time, $checkout_time, $guests);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $reservation_id = $conn->insert_id;
    $stmt->close();

    // Decrement available rooms
    $update = $conn->prepare("UPDATE rooms SET available_rooms = available_rooms - 1 WHERE id = ? AND available_rooms > 0");
    if (!$update) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $update->bind_param('i', $room_id);
    if (!$update->execute()) {
        throw new Exception("Execute failed: " . $update->error);
    }
    $update->close();

    http_response_code(200);
    echo json_encode(['success' => true, 'reservation_id' => $reservation_id]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    exit;
}
?>
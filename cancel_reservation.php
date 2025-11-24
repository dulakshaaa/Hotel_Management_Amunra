<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$reservation_id = (int)($_POST['reservation_id'] ?? 0);

if (!$reservation_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid reservation ID']);
    exit;
}

try {
    // Get room_name from reservation to find room id
    $getRoom = $conn->prepare("SELECT room_name FROM reservations WHERE id = ? AND user_id = ? LIMIT 1");
    $getRoom->bind_param('ii', $reservation_id, $user_id);
    $getRoom->execute();
    $roomResult = $getRoom->get_result();
    $reservation = $roomResult->fetch_assoc();
    $getRoom->close();

    if (!$reservation) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // Delete reservation
    $delete = $conn->prepare("DELETE FROM reservations WHERE id = ? AND user_id = ?");
    $delete->bind_param('ii', $reservation_id, $user_id);
    $delete->execute();
    $delete->close();

    // Increment available rooms
    $increment = $conn->prepare("UPDATE rooms SET available_rooms = available_rooms + 1 WHERE name = ?");
    $increment->bind_param('s', $reservation['room_name']);
    $increment->execute();
    $increment->close();

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Reservation cancelled']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

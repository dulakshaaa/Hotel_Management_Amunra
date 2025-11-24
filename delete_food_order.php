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
$order_id = (int)($_POST['order_id'] ?? 0);

if (!$order_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    // Verify ownership
    $verify = $conn->prepare("
        SELECT o.id FROM orders o
        JOIN reservations r ON o.reservation_id = r.id
        WHERE o.id = ? AND r.user_id = ?
    ");
    $verify->bind_param('ii', $order_id, $user_id);
    $verify->execute();
    if (!$verify->get_result()->fetch_assoc()) {
        $verify->close();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    $verify->close();

    // Delete order (cascades to order_items)
    $delete = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $delete->bind_param('i', $order_id);
    $delete->execute();
    $delete->close();

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Order cancelled']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

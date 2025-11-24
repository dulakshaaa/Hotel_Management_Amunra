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
$menu_item_id = (int)($_POST['menu_item_id'] ?? 0);

if (!$order_id || !$menu_item_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    // Verify ownership
    $verify = $conn->prepare("
        SELECT o.id, oi.subtotal FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN reservations r ON o.reservation_id = r.id
        WHERE o.id = ? AND oi.menu_item_id = ? AND r.user_id = ?
    ");

    if (!$verify) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $verify->bind_param('iii', $order_id, $menu_item_id, $user_id);
    if (!$verify->execute()) {
        throw new Exception("Execute failed: " . $verify->error);
    }

    $result = $verify->get_result();
    $orderItem = $result->fetch_assoc();
    $verify->close();

    if (!$orderItem) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $itemSubtotal = (float)$orderItem['subtotal'];

    // Delete order item
    $delete = $conn->prepare("DELETE FROM order_items WHERE order_id = ? AND menu_item_id = ?");
    if (!$delete) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $delete->bind_param('ii', $order_id, $menu_item_id);
    if (!$delete->execute()) {
        throw new Exception("Execute failed: " . $delete->error);
    }
    $delete->close();

    // Update order total amount
    $update = $conn->prepare("UPDATE orders SET total_amount = total_amount - ? WHERE id = ?");
    if (!$update) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $update->bind_param('di', $itemSubtotal, $order_id);
    if (!$update->execute()) {
        throw new Exception("Execute failed: " . $update->error);
    }
    $update->close();

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Item removed successfully']);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}

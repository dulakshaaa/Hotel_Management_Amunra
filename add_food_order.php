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
$items = json_decode($_POST['items'] ?? '[]', true);

if (!$reservation_id || empty($items)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $total_amount = 0;
    foreach ($items as $menuItemId => $itemData) {
        $quantity = (int)$itemData['quantity'];
        $price = floatval($itemData['price']);
        $subtotal = $price * $quantity;
        $total_amount += $subtotal;
    }

    // Insert order
    $orderStmt = $conn->prepare("INSERT INTO orders (reservation_id, user_id, total_amount) VALUES (?, ?, ?)");
    if (!$orderStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $orderStmt->bind_param('iid', $reservation_id, $user_id, $total_amount);
    if (!$orderStmt->execute()) {
        throw new Exception("Execute failed: " . $orderStmt->error);
    }
    $order_id = $conn->insert_id;
    $orderStmt->close();

    // Insert order items
    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
    if (!$itemStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    foreach ($items as $menuItemId => $itemData) {
        $quantity = (int)$itemData['quantity'];
        $price = floatval($itemData['price']);
        $subtotal = $price * $quantity;
        $menuItemId = (int)$menuItemId;

        $itemStmt->bind_param('iiidd', $order_id, $menuItemId, $quantity, $price, $subtotal);
        if (!$itemStmt->execute()) {
            throw new Exception("Execute failed: " . $itemStmt->error);
        }
    }
    $itemStmt->close();

    http_response_code(200);
    echo json_encode(['success' => true, 'order_id' => $order_id, 'total_amount' => $total_amount]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}
?>

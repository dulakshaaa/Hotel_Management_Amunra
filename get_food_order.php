<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$order_id = (int)($_GET['order_id'] ?? 0);

if (!$order_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

// Verify ownership and get order with items and images
$query = $conn->prepare("
    SELECT o.id, o.total_amount, m.id as menu_item_id, m.name, m.image_url, oi.quantity, oi.price, oi.subtotal
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN menu m ON oi.menu_item_id = m.id
    JOIN reservations r ON o.reservation_id = r.id
    WHERE o.id = ? AND r.user_id = ?
    ORDER BY oi.created_at ASC
");
$query->bind_param('ii', $order_id, $user_id);
$query->execute();
$result = $query->get_result();

$items = [];
$total_amount = 0;

while ($row = $result->fetch_assoc()) {
    $total_amount = $row['total_amount'];
    $items[] = [
        'menu_item_id' => (int)$row['menu_item_id'],
        'name' => $row['name'],
        'image_url' => $row['image_url'],
        'quantity' => (int)$row['quantity'],
        'price' => (float)$row['price'],
        'subtotal' => (float)$row['subtotal']
    ];
}
$query->close();

if (!empty($items)) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'items' => $items,
        'total_amount' => $total_amount
    ]);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'No order found']);
}
?>

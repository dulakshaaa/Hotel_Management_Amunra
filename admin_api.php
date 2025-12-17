<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

// Admin guard: adjust as needed (user_id == 1)
if (empty($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {

        // --------------------
        // ROOMS
        // --------------------
        case 'list_rooms':
            $res = $conn->query("SELECT id, name, category, price, description, image_url, total_rooms, available_rooms, created_at FROM rooms ORDER BY category, name");
            $rows = $res->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'rooms' => $rows]);
            break;

        case 'save_room':
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $image_url = trim($_POST['image_url'] ?? '');
            $total_rooms = max(1, (int)($_POST['total_rooms'] ?? 1));
            $available_rooms = (int)($_POST['available_rooms'] ?? $total_rooms);

            if ($name === '') throw new Exception('Name required');

            if ($id) {
                $stmt = $conn->prepare("UPDATE rooms SET name=?, category=?, price=?, description=?, image_url=?, total_rooms=?, available_rooms=? WHERE id=?");
                $stmt->bind_param('ssdssiii', $name, $category, $price, $description, $image_url, $total_rooms, $available_rooms, $id);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $conn->prepare("INSERT INTO rooms (name, category, price, description, image_url, features, total_rooms, available_rooms) VALUES (?, ?, ?, ?, ?, '[]', ?, ?)");
                $stmt->bind_param('ssdssii', $name, $category, $price, $description, $image_url, $total_rooms, $available_rooms);
                $stmt->execute();
                $id = $conn->insert_id;
                $stmt->close();
            }
            echo json_encode(['success' => true, 'room_id' => $id]);
            break;

        case 'delete_room':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('Invalid id');
            $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => true]);
            break;

        // --------------------
        // MENU
        // --------------------
        case 'list_menu':
            $res = $conn->query("SELECT id, name, description, price, category, image_url, available, created_at FROM menu ORDER BY category, name");
            $rows = $res->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'menu' => $rows]);
            break;

        case 'save_menu':
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $category = trim($_POST['category'] ?? '');
            $image_url = trim($_POST['image_url'] ?? '');
            $available = !empty($_POST['available']) ? 1 : 0;

            if ($name === '') throw new Exception('Name required');

            if ($id) {
                $stmt = $conn->prepare("UPDATE menu SET name=?, description=?, price=?, category=?, image_url=?, available=? WHERE id=?");
                $stmt->bind_param('ssdssii', $name, $description, $price, $category, $image_url, $available, $id);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $conn->prepare("INSERT INTO menu (name, description, price, category, image_url, available) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('ssdssi', $name, $description, $price, $category, $image_url, $available);
                $stmt->execute();
                $id = $conn->insert_id;
                $stmt->close();
            }
            echo json_encode(['success' => true, 'menu_id' => $id]);
            break;

        case 'delete_menu':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('Invalid id');
            $stmt = $conn->prepare("DELETE FROM menu WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => true]);
            break;

        // --------------------
        // BOOKINGS
        // --------------------
        case 'list_bookings':
            $stmt = $conn->prepare("
                SELECT r.*, u.username, COALESCE(o.id,0) as order_id
                FROM reservations r
                JOIN users u ON r.user_id = u.id
                LEFT JOIN orders o ON r.id = o.reservation_id
                ORDER BY r.created_at DESC
            ");
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'bookings' => $rows]);
            break;

        case 'cancel_booking':
            $reservation_id = (int)($_POST['reservation_id'] ?? 0);
            if (!$reservation_id) throw new Exception('Invalid reservation id');

            // get room_name to increment available rooms
            $stmt = $conn->prepare("SELECT room_name FROM reservations WHERE id = ? LIMIT 1");
            $stmt->bind_param('i', $reservation_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$row) throw new Exception('Reservation not found');

            $conn->begin_transaction();
            $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
            $stmt->bind_param('i', $reservation_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE rooms SET available_rooms = LEAST(total_rooms, available_rooms + 1) WHERE name = ?");
            $stmt->bind_param('s', $row['room_name']);
            $stmt->execute();
            $stmt->close();
            $conn->commit();
            echo json_encode(['success' => true]);
            break;

        // --------------------
        // ORDERS
        // --------------------
        case 'list_orders':
            $stmt = $conn->prepare("
                SELECT o.*, u.username, r.room_name
                FROM orders o
                JOIN users u ON o.user_id = u.id
                JOIN reservations r ON o.reservation_id = r.id
                ORDER BY o.created_at DESC
            ");
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'orders' => $rows]);
            break;

        case 'update_order_status':
            $order_id = (int)($_POST['order_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            if (!in_array($status, ['pending','confirmed','completed','cancelled'])) throw new Exception('Invalid status');
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->bind_param('si', $status, $order_id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => true]);
            break;

        // --------------------
        // USERS
        // --------------------
        case 'list_users':
            $res = $conn->query("SELECT id, username, email, fullname, contact_number, nic, created_at FROM users ORDER BY created_at DESC");
            $rows = $res->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'users' => $rows]);
            break;

        // --------------------
        // REVIEWS
        // --------------------
        case 'list_reviews':
            $res = $conn->query("SELECT rv.*, u.username FROM reviews rv JOIN users u ON rv.user_id = u.id ORDER BY rv.created_at DESC");
            $rows = $res->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'reviews' => $rows]);
            break;

        case 'delete_review':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('Invalid id');
            $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

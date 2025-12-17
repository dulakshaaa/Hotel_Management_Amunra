<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php?redirect=bookings');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Fetch menu items for food modal
$menu_query = $conn->query("SELECT id, name, description, price, category, image_url FROM menu WHERE available = TRUE ORDER BY category, name");
$menu_items = [];
if ($menu_query) {
    while ($item = $menu_query->fetch_assoc()) {
        $menu_items[] = $item;
    }
}

// Fetch bookings with food orders
$bookings_query = $conn->prepare("
    SELECT 
        r.id, r.room_name, r.price, r.checkin, r.checkout, 
        r.checkin_time, r.checkout_time, r.guests, r.created_at,
        COALESCE(SUM(oi.subtotal), 0) as food_total,
        COALESCE(o.id, 0) as order_id,
        DATEDIFF(r.checkout, r.checkin) as nights
    FROM reservations r
    LEFT JOIN orders o ON r.id = o.reservation_id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE r.user_id = ?
    GROUP BY r.id
    ORDER BY r.checkin DESC
");
$bookings_query->bind_param('i', $user_id);
$bookings_query->execute();
$result = $bookings_query->get_result();
$bookings = [];
while ($booking = $result->fetch_assoc()) {
    $booking['room_subtotal'] = $booking['price'] * max(1, $booking['nights']);
    $booking['total'] = $booking['room_subtotal'] + $booking['food_total'];
    $bookings[] = $booking;
}
$bookings_query->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings — AMUNRA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #c19a53;
            --secondary-color: #f5f5dc;
            --accent-color: #8b7355;
            --text-color: #333;
            --light-text: #fff;
            --dark-bg: #1a1a1a;
            --transition: all 0.3s ease;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: #fff;
            padding-top: 80px;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background-color: rgba(26, 26, 26, 0.9);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--light-text);
        }

        .logo span {
            color: var(--primary-color);
        }

        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
        }

        .nav-links a {
            color: var(--light-text);
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .page-title {
            text-align: center;
            margin: 50px 0 40px;
        }

        .page-title h1 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .page-title p {
            color: #666;
            font-size: 1.1rem;
        }

        .bookings-container {
            margin: 40px 0;
        }

        .no-bookings {
            text-align: center;
            padding: 60px 20px;
            background: var(--secondary-color);
            border-radius: var(--border-radius);
        }

        .no-bookings i {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .no-bookings h2 {
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .no-bookings p {
            color: #666;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--primary-color);
            color: var(--light-text);
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }

        .bookings-table thead {
            background-color: var(--primary-color);
            color: var(--light-text);
        }

        .bookings-table th {
            padding: 18px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .bookings-table td {
            padding: 16px 18px;
            border-bottom: 1px solid #eee;
        }

        .bookings-table tbody tr:hover {
            background-color: #f9f9f9;
        }

        .bookings-table tbody tr:last-child td {
            border-bottom: none;
        }

        .room-name {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.05rem;
        }

        .booking-id {
            color: #999;
            font-size: 0.85rem;
        }

        .date-time {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .date-time .date {
            font-weight: 500;
            color: var(--text-color);
        }

        .date-time .time {
            font-size: 0.85rem;
            color: #666;
        }

        .price {
            font-weight: 600;
            color: var(--accent-color);
            font-size: 1.1rem;
        }

        .booking-date {
            font-size: 0.9rem;
            color: #666;
        }

        .guests-badge {
            display: inline-block;
            background-color: var(--secondary-color);
            color: var(--text-color);
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        footer {
            background-color: var(--dark-bg);
            color: var(--light-text);
            padding: 40px 0 20px;
            margin-top: 60px;
            text-align: center;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 30px;
            padding: 10px 20px;
            background: var(--secondary-color);
            color: var(--primary-color);
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .back-link:hover {
            background: var(--primary-color);
            color: var(--light-text);
        }

        .booking-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .booking-actions button {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
        }

        .btn-order {
            background: #c19a53;
            color: #fff;
        }

        .btn-order:hover {
            background: #8b7355;
        }

        .btn-view-food {
            background: #2196F3;
            color: #fff;
        }

        .btn-view-food:hover {
            background: #1976D2;
        }

        .btn-cancel-food {
            background: #ff9800;
            color: #fff;
        }

        .btn-cancel-food:hover {
            background: #e68900;
        }

        .subtotal-section {
            background: #f5f5dc;
            padding: 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        .subtotal-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .subtotal-row.total {
            border-top: 2px solid #c19a53;
            padding-top: 8px;
            font-weight: 600;
            color: #8b7355;
            font-size: 1rem;
        }

        .menu-item-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            padding: 0;
            text-align: center;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .menu-item-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            transform: translateY(-5px);
        }

        .menu-item-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .menu-item-info {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .menu-item-name {
            color: #c19a53;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .menu-item-desc {
            color: #666;
            font-size: 0.8rem;
            margin-bottom: 10px;
            flex: 1;
        }

        .menu-item-price {
            color: #8b7355;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .add-to-order {
            width: 100%;
            padding: 8px;
            background: #c19a53;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .add-to-order:hover {
            background: #8b7355;
        }

        .view-order-modal {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 2002;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .order-modal-content {
            background: #fff;
            width: 100%;
            max-width: 700px;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            max-height: 85vh;
            overflow-y: auto;
            position: relative;
        }

        .order-modal-content .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .order-modal-content .close-btn:hover {
            background: #f5f5dc;
            color: #c19a53;
        }

        .order-header {
            margin-bottom: 25px;
        }

        .order-header h2 {
            color: #c19a53;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .order-header p {
            color: #666;
            font-size: 0.95rem;
        }

        .order-items-display {
            display: grid;
            gap: 12px;
            margin-bottom: 25px;
        }

        .order-item-card {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #eee;
            transition: all 0.3s;
            position: relative;
        }

        .order-item-card:hover {
            background: #f5f5dc;
            box-shadow: 0 4px 12px rgba(193, 154, 83, 0.15);
        }

        .order-item-image {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .order-item-info {
            flex: 1;
        }

        .order-item-name {
            font-weight: 600;
            color: #c19a53;
            font-size: 1.05rem;
            margin-bottom: 5px;
        }

        .order-item-qty {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .order-item-price {
            color: #8b7355;
            font-weight: 600;
            font-size: 1rem;
        }

        .order-item-delete {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 32px;
            height: 32px;
            background: #ff6b6b;
            color: #fff;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-weight: bold;
            line-height: 1;
            padding: 0;
        }

        .order-item-delete:hover {
            background: #c92a2a;
            transform: scale(1.1);
        }

        .order-summary {
            background: linear-gradient(135deg, #f5f5dc 0%, #fafaf8 100%);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 2px solid #c19a53;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .summary-row:last-child {
            margin-bottom: 0;
            border-top: 2px solid #c19a53;
            padding-top: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            color: #8b7355;
        }

        .summary-row .label {
            color: #333;
        }

        .summary-row .value {
            color: #8b7355;
            font-weight: 600;
        }

        .order-modal-actions {
            display: flex;
            gap: 10px;
        }

        .close-order-btn {
            flex: 1;
            padding: 12px;
            background: #c19a53;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .close-order-btn:hover {
            background: #8b7355;
            transform: translateY(-2px);
        }

        .empty-order {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-order i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #ddd;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <a href="home.php" class="logo"><span>AMUNRA</span></a>
                <ul class="nav-links">
                    <li><a href="home.php#hero">Home</a></li>
                    <li><a href="home.php#rooms">Rooms</a></li>
                    <li><a href="bookings.php" style="color: var(--primary-color); font-weight: 600;">My Bookings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="page-title">
            <h1>My Bookings</h1>
            <p>View and manage your reservations at AMUNRA</p>
        </div>

        <a href="home.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>

        <div class="bookings-container">
            <?php if (empty($bookings)): ?>
                <div class="no-bookings">
                    <i class="fas fa-calendar-alt"></i>
                    <h2>No Bookings Yet</h2>
                    <p>You haven't made any reservations yet. Explore our luxurious rooms and book your stay today!</p>
                    <a href="home.php#rooms" class="btn">Browse Rooms</a>
                </div>
            <?php else: ?>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Check-in / Check-out</th>
                            <th>Guests</th>
                            <th>Subtotal</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>
                                    <div class="room-name"><?php echo htmlspecialchars($booking['room_name']); ?></div>
                                    <div class="booking-id">ID: #<?php echo $booking['id']; ?></div>
                                </td>
                                <td>
                                    <div class="date-time">
                                        <span class="date"><strong>In:</strong> <?php echo date('M d, Y', strtotime($booking['checkin'])); ?> @ <?php echo date('h:i A', strtotime($booking['checkin_time'])); ?></span>
                                        <span class="date"><strong>Out:</strong> <?php echo date('M d, Y', strtotime($booking['checkout'])); ?> @ <?php echo date('h:i A', strtotime($booking['checkout_time'])); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="guests-badge">
                                        <i class="fas fa-users"></i> <?php echo $booking['guests']; ?> <?php echo $booking['guests'] === 1 ? 'Guest' : 'Guests'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="subtotal-section">
                                        <div class="subtotal-row">
                                            <span>Room (LKR <?php echo number_format($booking['price'], 2); ?> × <?php echo $booking['nights']; ?> nights)</span>
                                            <span>LKR <?php echo number_format($booking['room_subtotal'], 2); ?></span>
                                        </div>
                                        <?php if ($booking['food_total'] > 0): ?>
                                            <div class="subtotal-row">
                                                <span>Food & Beverages</span>
                                                <span>LKR <?php echo number_format($booking['food_total'], 2); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="subtotal-row total">
                                            <span>Total Due:</span>
                                            <span>LKR <?php echo number_format($booking['total'], 2); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="booking-actions">
                                        <button class="btn-order" onclick="openFoodModal(<?php echo $booking['id']; ?>)">
                                            <i class="fas fa-utensils"></i> Order Food
                                        </button>
                                        <?php if ($booking['order_id'] > 0): ?>
                                            <button class="btn-view-food" onclick="viewFoodOrder(<?php echo $booking['order_id']; ?>)">
                                                <i class="fas fa-eye"></i> View Order
                                            </button>
                                        <?php endif; ?>
                                        <a href="receipts.php?id=<?php echo $booking['id']; ?>" class="btn-order" style="text-decoration: none; padding: 8px 12px; background: #2196F3;">
                                            <i class="fas fa-file-invoice"></i> Invoice
                                        </a>
                                        <button class="btn-cancel" onclick="if(confirm('Cancel this reservation?')) cancelReservation(<?php echo $booking['id']; ?>)" style="background: #ff6b6b;">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <!-- Food Menu Modal -->
    <div id="food-modal" style="display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 2001; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #fff; width: 100%; max-width: 800px; border-radius: 12px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto;">
            
            <button id="close-food-modal" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                <i class="fas fa-times"></i>
            </button>

            <h2 style="color: #c19a53; margin-bottom: 10px; font-size: 1.8rem;">Order Food & Beverages</h2>
            <p style="color: #666; margin-bottom: 25px;">Add items to your reservation</p>

            <div id="menu-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px;">
                <?php foreach ($menu_items as $item): ?>
                    <div class="menu-item-card menu-item" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-price="<?php echo $item['price']; ?>">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-item-image">
                        <div class="menu-item-info">
                            <div class="menu-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="menu-item-desc"><?php echo htmlspecialchars($item['description'] ?? ''); ?></div>
                            <div class="menu-item-price">LKR <?php echo number_format($item['price'], 2); ?></div>
                            <button type="button" class="add-to-order">+ Add</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="background: #f5f5dc; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="color: #c19a53; margin-bottom: 15px;">Your Order</h3>
                <div id="order-items-list" style="margin-bottom: 15px; min-height: 50px;"></div>
                <div style="border-top: 2px solid #c19a53; padding-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="color: #333; margin: 0;">Order Total:</h3>
                    <p style="color: #8b7355; font-weight: 600; font-size: 1.3rem; margin: 0;">LKR <span id="order-total">0.00</span></p>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" id="confirm-food-order" style="flex: 1; padding: 14px; background: #c19a53; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem; text-transform: uppercase; transition: all 0.3s;">
                    Add to Reservation
                </button>
                <button type="button" id="close-food-btn" style="flex: 1; padding: 14px; background: #f5f5dc; color: #c19a53; border: 2px solid #c19a53; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem; text-transform: uppercase; transition: all 0.3s;">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- View Food Order Modal (Professional) -->
    <div id="view-order-modal" class="view-order-modal">
        <div class="order-modal-content">
            <button class="close-btn" onclick="document.getElementById('view-order-modal').style.display = 'none';">
                <i class="fas fa-times"></i>
            </button>

            <div class="order-header">
                <h2><i class="fas fa-receipt"></i> My Food Order</h2>
                <p>Manage your food and beverage selections</p>
            </div>

            <div class="order-items-display" id="order-items-display"></div>

            <div class="order-summary">
                <div class="summary-row">
                    <span class="label">Subtotal:</span>
                    <span class="value">$<span id="display-subtotal">0.00</span></span>
                </div>
                <div class="summary-row">
                    <span class="label">Total:</span>
                    <span class="value">$<span id="display-order-total">0.00</span></span>
                </div>
            </div>

            <div class="order-modal-actions">
                <button class="close-order-btn" onclick="document.getElementById('view-order-modal').style.display = 'none';">
                    <i class="fas fa-check"></i> Done
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> AMUNRA. All rights reserved.</p>
        </div>
    </footer>

    <script>
        let currentReservationId = null;
        let foodOrder = {};

        const foodModal = document.getElementById('food-modal');
        const closeFoodModal = document.getElementById('close-food-modal');
        const closeFoodBtn = document.getElementById('close-food-btn');
        const addToOrderBtns = document.querySelectorAll('.add-to-order');
        const confirmFoodOrder = document.getElementById('confirm-food-order');

        // Open food modal
        function openFoodModal(reservationId) {
            currentReservationId = reservationId;
            foodOrder = {};
            document.getElementById('order-items-list').innerHTML = '';
            document.getElementById('order-total').textContent = '0.00';
            foodModal.style.display = 'flex';
        }

        // Close food modal
        closeFoodModal.addEventListener('click', () => {
            foodModal.style.display = 'none';
        });
        
        closeFoodBtn.addEventListener('click', () => {
            foodModal.style.display = 'none';
        });
        
        window.addEventListener('click', (e) => {
            if (e.target === foodModal) {
                foodModal.style.display = 'none';
            }
        });

        // Add item to order
        addToOrderBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const item = btn.closest('.menu-item');
                const itemId = item.getAttribute('data-id');
                const itemName = item.getAttribute('data-name');
                const itemPrice = parseFloat(item.getAttribute('data-price'));

                if (!foodOrder[itemId]) {
                    foodOrder[itemId] = { name: itemName, price: itemPrice, quantity: 0 };
                }
                foodOrder[itemId].quantity++;
                updateOrderDisplay();
            });
        });

        function updateOrderDisplay() {
            const list = document.getElementById('order-items-list');
            list.innerHTML = '';
            let total = 0;

            if (Object.keys(foodOrder).length === 0) {
                list.innerHTML = '<p style="color: #999; text-align: center; padding: 20px;">No items in order</p>';
                return;
            }

            for (const itemId in foodOrder) {
                const item = foodOrder[itemId];
                const subtotal = item.price * item.quantity;
                total += subtotal;

                const orderItem = document.createElement('div');
                orderItem.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #ddd;';
                orderItem.innerHTML = `
                    <div style="flex: 1;">
                        <p style="margin: 0; color: #333; font-weight: 500;">${item.name}</p>
                        <p style="margin: 5px 0 0 0; color: #666; font-size: 0.85rem;">LKR ${item.price.toFixed(2)} x ${item.quantity}</p>
                    </div>
                    <div style="text-align: right;">
                        <p style="margin: 0; color: #8b7355; font-weight: 600;">LKR ${subtotal.toFixed(2)}</p>
                        <button type="button" class="remove-item" data-id="${itemId}" style="background: #ff6b6b; color: #fff; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; margin-top: 5px;">Remove</button>
                    </div>
                `;
                list.appendChild(orderItem);
            }

            document.getElementById('order-total').textContent = total.toFixed(2);

            // Remove buttons
            document.querySelectorAll('.remove-item').forEach(btn => {
                btn.addEventListener('click', () => {
                    delete foodOrder[btn.getAttribute('data-id')];
                    updateOrderDisplay();
                });
            });
        }

        // Confirm food order
        confirmFoodOrder.addEventListener('click', async () => {
            if (Object.keys(foodOrder).length === 0) {
                alert('Please add items to your order');
                return;
            }

            if (!currentReservationId) {
                alert('Error: Reservation ID not found');
                return;
            }

            const formData = new FormData();
            formData.append('reservation_id', currentReservationId);
            formData.append('items', JSON.stringify(foodOrder));

            try {
                const res = await fetch('add_food_order.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                const data = await res.json();

                if (data.success) {
                    alert('✓ Food order added! Total: LKR ' + data.total_amount.toFixed(2));
                    foodModal.style.display = 'none';
                    location.reload();
                } else {
                    alert('✕ Error: ' + (data.message || 'Unable to add food order'));
                }
            } catch (err) {
                alert('✕ Network error. Please try again.');
                console.error(err);
            }
        });

        // View food order with professional layout
        function viewFoodOrder(orderId) {
            fetch('get_food_order.php?order_id=' + orderId, {
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const itemsDisplay = document.getElementById('order-items-display');
                    itemsDisplay.innerHTML = '';
                    
                    if (data.items.length === 0) {
                        itemsDisplay.innerHTML = `
                            <div class="empty-order">
                                <i class="fas fa-inbox"></i>
                                <p>No items in this order</p>
                            </div>
                        `;
                    } else {
                        data.items.forEach(item => {
                            const itemDiv = document.createElement('div');
                            itemDiv.className = 'order-item-card';
                            itemDiv.innerHTML = `
                                <img src="${item.image_url}" alt="${item.name}" class="order-item-image" onerror="this.src='https://via.placeholder.com/100'">
                                <div class="order-item-info">
                                    <div class="order-item-name">${item.name}</div>
                                    <div class="order-item-qty"><strong>Qty:</strong> ${item.quantity}</div>
                                    <div class="order-item-price">LKR ${item.price.toFixed(2)} each = <strong>LKR ${item.subtotal.toFixed(2)}</strong></div>
                                </div>
                                <button class="order-item-delete" onclick="deleteOrderItem(${data.order_id}, ${item.menu_item_id}, event)" title="Remove this item">
                                    ×
                                </button>
                            `;
                            itemsDisplay.appendChild(itemDiv);
                        });
                    }
                    
                    document.getElementById('display-subtotal').textContent = data.total_amount.toFixed(2);
                    document.getElementById('display-order-total').textContent = data.total_amount.toFixed(2);
                    document.getElementById('view-order-modal').style.display = 'flex';
                } else {
                    alert('Error: ' + (data.message || 'Unable to load order'));
                }
            })
            .catch(err => {
                alert('Network error. Please try again.');
                console.error(err);
            });
        }

        // Delete individual order item
        function deleteOrderItem(orderId, menuItemId, event) {
            event.preventDefault();
            
            if (!confirm('Remove this item from your order?')) {
                return;
            }

            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('menu_item_id', menuItemId);

            fetch('delete_order_item.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    viewFoodOrder(orderId);
                    location.reload();
                } else {
                    alert('✕ Error: ' + (data.message || 'Unable to delete item'));
                }
            })
            .catch(err => {
                alert('Network error. Please try again.');
                console.error(err);
            });
        }

        // Cancel reservation
        function cancelReservation(reservationId) {
            const formData = new FormData();
            formData.append('reservation_id', reservationId);

            fetch('cancel_reservation.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✓ Reservation cancelled successfully');
                    location.reload();
                } else {
                    alert('✕ Error: ' + data.message);
                }
            });
        }
    </script>
</body>
</html>

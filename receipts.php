<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$reservation_id = (int)($_GET['id'] ?? 0);

if (!$reservation_id) {
    header('Location: bookings.php');
    exit;
}

// Fetch reservation details
$stmt = $conn->prepare("
    SELECT r.*, u.username, u.email,
           COALESCE(SUM(oi.subtotal), 0) as food_total
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN orders o ON r.id = o.reservation_id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE r.id = ? AND r.user_id = ?
    GROUP BY r.id
");
$stmt->bind_param('ii', $reservation_id, $user_id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reservation) {
    header('Location: bookings.php');
    exit;
}

$nights = (int) date_diff(date_create($reservation['checkin']), date_create($reservation['checkout']))->format('%d');
$room_total = $reservation['price'] * $nights;
$food_total = (float)$reservation['food_total'];
$subtotal = $room_total + $food_total;
$tax = $subtotal * 0.1;
$grand_total = $subtotal + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $reservation_id; ?> — AMUNRA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; padding: 40px 20px; }
        .receipt { max-width: 800px; margin: 0 auto; background: #fff; padding: 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border-radius: 8px; }
        .receipt-header { text-align: center; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 2px solid #c19a53; }
        .receipt-logo { font-size: 2rem; font-weight: 700; margin-bottom: 10px; }
        .receipt-logo span { color: #c19a53; }
        .invoice-title { font-size: 1.5rem; color: #c19a53; margin: 20px 0; }
        .invoice-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; color: #666; font-size: 0.9rem; }
        .invoice-section { margin-bottom: 30px; }
        .invoice-section h4 { color: #c19a53; margin-bottom: 10px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f5f5dc; padding: 12px; text-align: left; font-weight: 600; color: #333; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .total-section { text-align: right; margin-top: 30px; border-top: 2px solid #c19a53; padding-top: 20px; }
        .total-row { display: flex; justify-content: flex-end; gap: 50px; margin-bottom: 10px; }
        .total-row.grand { font-size: 1.2rem; font-weight: 700; color: #c19a53; }
        .print-btn { margin-top: 30px; text-align: center; }
        .print-btn button { padding: 12px 30px; background: #c19a53; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
        @media print { body { padding: 0; background: none; } .print-btn { display: none; } }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="receipt-header">
            <div class="receipt-logo"><span>AMUNRA</span></div>
            <p style="color: #999;">Luxury Egyptian Resort</p>
        </div>

        <div class="invoice-title">INVOICE / RECEIPT</div>

        <div class="invoice-meta">
            <div>
                <strong>Invoice #:</strong> <?php echo str_pad($reservation_id, 6, '0', STR_PAD_LEFT); ?><br>
                <strong>Date:</strong> <?php echo date('M d, Y'); ?><br>
                <strong>Due Date:</strong> <?php echo date('M d, Y', strtotime('+7 days')); ?>
            </div>
            <div>
                <strong>Guest:</strong> <?php echo htmlspecialchars($reservation['username']); ?><br>
                <strong>Email:</strong> <?php echo htmlspecialchars($reservation['email']); ?><br>
                <strong>Status:</strong> <span style="color: #2e7d32; font-weight: 600;">Confirmed</span>
            </div>
        </div>

        <div class="invoice-section">
            <h4>Booking Details</h4>
            <table>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
                <tr>
                    <td><?php echo htmlspecialchars($reservation['room_name']); ?> (<?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?>)</td>
                    <td style="text-align: right;">$<?php echo number_format($room_total, 2); ?></td>
                </tr>
                <?php if ($food_total > 0): ?>
                <tr>
                    <td>Food & Beverages</td>
                    <td style="text-align: right;">$<?php echo number_format($food_total, 2); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="invoice-section">
            <h4>Guest Information</h4>
            <p><strong>Check-in:</strong> <?php echo date('M d, Y', strtotime($reservation['checkin'])); ?> @ <?php echo date('h:i A', strtotime($reservation['checkin_time'])); ?></p>
            <p><strong>Check-out:</strong> <?php echo date('M d, Y', strtotime($reservation['checkout'])); ?> @ <?php echo date('h:i A', strtotime($reservation['checkout_time'])); ?></p>
            <p><strong>Guests:</strong> <?php echo $reservation['guests']; ?></p>
        </div>

        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>$<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="total-row">
                <span>Tax (10%):</span>
                <span>$<?php echo number_format($tax, 2); ?></span>
            </div>
            <div class="total-row grand">
                <span>Grand Total:</span>
                <span>$<?php echo number_format($grand_total, 2); ?></span>
            </div>
        </div>

        <div class="print-btn">
            <button onclick="window.print()"><i class="fas fa-print"></i> Print Invoice</button>
        </div>
    </div>
</body>
</html>

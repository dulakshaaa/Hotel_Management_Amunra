<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Create reviews table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    reservation_id INT NOT NULL,
    rating INT NOT NULL CHECK(rating >= 1 AND rating <= 5),
    title VARCHAR(200) NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (reservation_id)
)");

// Fetch user's completed bookings
$bookingsStmt = $conn->prepare("
    SELECT r.id, r.room_name, r.checkin, r.checkout,
           COUNT(rv.id) as has_review
    FROM reservations r
    LEFT JOIN reviews rv ON r.id = rv.reservation_id
    WHERE r.user_id = ? AND r.checkout < CURDATE()
    GROUP BY r.id
    ORDER BY r.checkout DESC
");
$bookingsStmt->bind_param('i', $user_id);
$bookingsStmt->execute();
$bookings = $bookingsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$bookingsStmt->close();

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = (int)$_POST['reservation_id'];
    $room_id = (int)$_POST['room_id'];
    $rating = (int)$_POST['rating'];
    $title = trim($_POST['title']);
    $comment = trim($_POST['comment']);

    if ($rating >= 1 && $rating <= 5 && !empty($title) && !empty($comment)) {
        $insertReview = $conn->prepare(
            "INSERT INTO reviews (user_id, room_id, reservation_id, rating, title, comment) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $insertReview->bind_param('iiiiiss', $user_id, $room_id, $reservation_id, $rating, $title, $comment);
        $insertReview->execute();
        $insertReview->close();
        $message = '✓ Review submitted successfully!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews — AMUNRA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #c19a53;
            --secondary-color: #f5f5dc;
            --accent-color: #8b7355;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding-top: 80px;
        }

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 0 auto;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(26, 26, 26, 0.95);
            padding: 20px 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .page-title {
            text-align: center;
            margin: 50px 0 40px;
        }

        .page-title h1 {
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        .bookings-grid {
            display: grid;
            gap: 20px;
            margin-bottom: 60px;
        }

        .booking-card {
            background: #fff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .booking-header h3 {
            color: var(--primary-color);
        }

        .review-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .review-status.completed {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .review-status.pending {
            background: #fff3e0;
            color: #e65100;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            transition: all 0.3s;
        }

        input:focus, textarea:focus, select:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .rating-input {
            display: flex;
            gap: 10px;
        }

        .star {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: all 0.2s;
        }

        .star:hover,
        .star.active {
            color: #ffc107;
            transform: scale(1.2);
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn:hover {
            background: var(--accent-color);
        }

        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <a href="home.php" style="font-size: 1.8rem; font-weight: 700; color: #fff; text-decoration: none;"><span style="color: var(--primary-color);">AMUNRA</span></a>
            <nav style="display: flex; gap: 30px;">
                <a href="home.php" style="color: #fff; text-decoration: none;">Home</a>
                <a href="bookings.php" style="color: #fff; text-decoration: none;">Bookings</a>
                <a href="logout.php" style="color: #fff; text-decoration: none;">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="page-title">
            <h1><i class="fas fa-star"></i> Leave a Review</h1>
            <p>Share your experience with us</p>
        </div>

        <?php if (isset($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="bookings-grid">
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div>
                            <h3><?php echo htmlspecialchars($booking['room_name']); ?></h3>
                            <p style="color: #666; font-size: 0.9rem;">
                                <?php echo date('M d, Y', strtotime($booking['checkin'])); ?> - 
                                <?php echo date('M d, Y', strtotime($booking['checkout'])); ?>
                            </p>
                        </div>
                        <span class="review-status <?php echo $booking['has_review'] ? 'completed' : 'pending'; ?>">
                            <?php echo $booking['has_review'] ? '✓ Reviewed' : 'Pending Review'; ?>
                        </span>
                    </div>

                    <?php if (!$booking['has_review']): ?>
                        <form method="POST">
                            <input type="hidden" name="reservation_id" value="<?php echo $booking['id']; ?>">

                            <div class="form-group">
                                <label>Rating (1-5 stars)</label>
                                <div class="rating-input" id="rating-<?php echo $booking['id']; ?>">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star" data-value="<?php echo $i; ?>" onclick="setRating(<?php echo $booking['id']; ?>, <?php echo $i; ?>)">★</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="rating-value-<?php echo $booking['id']; ?>" value="0" required>
                            </div>

                            <div class="form-group">
                                <label>Review Title</label>
                                <input type="text" name="title" placeholder="e.g., Amazing experience!" required>
                            </div>

                            <div class="form-group">
                                <label>Your Review</label>
                                <textarea name="comment" rows="5" placeholder="Share your experience..." required></textarea>
                            </div>

                            <button type="submit" class="btn">Submit Review</button>
                        </form>
                    <?php else: ?>
                        <p style="color: #2e7d32; font-weight: 600;">Thank you for your review!</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (empty($bookings)): ?>
                <div class="booking-card" style="text-align: center; padding: 60px 30px;">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: #ddd; margin-bottom: 20px;"></i>
                    <p style="color: #999;">No completed bookings to review yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function setRating(bookingId, value) {
            document.getElementById('rating-value-' + bookingId).value = value;
            const stars = document.querySelectorAll('#rating-' + bookingId + ' .star');
            stars.forEach((star, index) => {
                if (index < value) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>

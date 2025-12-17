<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$message = '';
$error = '';

// Fetch user details
$userStmt = $conn->prepare("SELECT username, email, fullname, contact_number, nic FROM users WHERE id = ?");
$userStmt->bind_param('i', $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $nic = trim($_POST['nic'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } else if (empty($fullname) || empty($contact_number) || empty($nic)) {
        $error = 'All fields are required';
    } else {
        $update = $conn->prepare("UPDATE users SET email = ?, fullname = ?, contact_number = ?, nic = ? WHERE id = ?");
        $update->bind_param('ssssi', $email, $fullname, $contact_number, $nic, $user_id);
        $update->execute();
        $update->close();

        if (!empty($password) && strlen($password) >= 6) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $passUpdate = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $passUpdate->bind_param('si', $hash, $user_id);
            $passUpdate->execute();
            $passUpdate->close();
        }

        $message = '✓ Profile updated successfully';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — AMUNRA</title>
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
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
            background: rgba(26, 26, 26, 0.95);
            padding: 20px 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--light-text);
        }

        .logo span {
            color: var(--primary-color);
        }

        .nav-links a {
            color: var(--light-text);
            text-decoration: none;
            margin-left: 30px;
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .page-title {
            text-align: center;
            margin: 50px 0 40px;
            color: var(--primary-color);
        }

        .page-title h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .profile-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 60px;
        }

        .profile-sidebar {
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--light-text);
            font-size: 2rem;
            margin: 0 auto 20px;
        }

        .profile-sidebar h3 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .profile-sidebar p {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 10px 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 12px 15px;
            background: var(--secondary-color);
            color: var(--primary-color);
            border-radius: 6px;
            text-decoration: none;
            transition: var(--transition);
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: var(--primary-color);
            color: var(--light-text);
        }

        .profile-content {
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }

        input, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            transition: var(--transition);
        }

        input:focus, textarea:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: var(--primary-color);
            color: var(--light-text);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn:hover {
            background: var(--accent-color);
        }

        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }

        .message.error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }

        footer {
            background: var(--dark-bg);
            color: var(--light-text);
            text-align: center;
            padding: 40px 0;
            margin-top: 60px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="home.php" class="logo"><span>AMUNRA</span></a>
                <nav class="nav-links">
                    <a href="home.php">Home</a>
                    <a href="bookings.php">Bookings</a>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <div class="page-title">
            <h1><i class="fas fa-user-circle"></i> My Profile</h1>
        </div>

        <div class="profile-container">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <?php if (!empty($user['fullname'])): ?>
                    <p style="font-size: 0.85rem; color: #999;"><?php echo htmlspecialchars($user['fullname']); ?></p>
                <?php endif; ?>
                
                <ul class="sidebar-menu">
                    <li><a href="#personal" class="menu-link active">Personal Info</a></li>
                    <li><a href="#security" class="menu-link">Change Password</a></li>
                    <li><a href="#bookings" class="menu-link">My Bookings</a></li>
                    <li><a href="#preferences" class="menu-link">Preferences</a></li>
                </ul>
            </div>

            <!-- Content -->
            <div class="profile-content">
                <?php if ($message): ?>
                    <div class="message success">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="message error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Personal Info Section -->
                <div id="personal" class="content-section active">
                    <h2 style="color: var(--primary-color); margin-bottom: 25px;">Personal Information</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" readonly style="background: #f5f5f5; cursor: not-allowed;">
                        </div>

                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="tel" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>NIC (National ID Card)</label>
                            <input type="text" name="nic" value="<?php echo htmlspecialchars($user['nic'] ?? ''); ?>" required>
                        </div>

                        <button type="submit" class="btn">Update Profile</button>
                    </form>
                </div>

                <!-- Security Section -->
                <div id="security" class="content-section">
                    <h2 style="color: var(--primary-color); margin-bottom: 25px;">Change Password</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label>New Password (leave blank to keep current)</label>
                            <input type="password" name="password" placeholder="Minimum 6 characters">
                        </div>

                        <button type="submit" class="btn">Update Password</button>
                    </form>
                </div>

                <!-- Bookings Section -->
                <div id="bookings" class="content-section">
                    <h2 style="color: var(--primary-color); margin-bottom: 25px;">Your Bookings</h2>
                    <p><a href="bookings.php" style="color: var(--primary-color);">View all your bookings and manage orders</a></p>
                </div>

                <!-- Preferences Section -->
                <div id="preferences" class="content-section">
                    <h2 style="color: var(--primary-color); margin-bottom: 25px;">Preferences</h2>
                    <div class="form-group">
                        <label style="display: flex; align-items: center;">
                            <input type="checkbox" checked style="width: auto; margin-right: 10px;">
                            Receive email notifications for bookings
                        </label>
                    </div>
                    <div class="form-group">
                        <label style="display: flex; align-items: center;">
                            <input type="checkbox" checked style="width: auto; margin-right: 10px;">
                            Receive promotional offers
                        </label>
                    </div>
                    <button class="btn">Save Preferences</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> AMUNRA. All rights reserved.</p>
    </footer>

    <script>
        // Sidebar menu navigation
        document.querySelectorAll('.menu-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Remove active class from all links and sections
                document.querySelectorAll('.menu-link').forEach(l => l.classList.remove('active'));
                document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
                
                // Add active class to clicked link and corresponding section
                link.classList.add('active');
                const target = link.getAttribute('href').substring(1);
                document.getElementById(target).classList.add('active');
            });
        });
    </script>
</body>
</html>

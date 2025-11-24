<?php
require_once __DIR__ . '/config.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
        if (!$stmt) {
            $error = 'Database error. Please try again.';
        } else {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = $user['username'];
                $success = true;
                header('Location: home.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login — AMUNRA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at center, #ffffffff 0%, #fffcf1ff 100%);
            color: #333;
            padding: 40px 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 420px;
            width: 100%;
        }
        .card {
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            padding: 40px;
            background: #fff;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-section h1 {
            color: #c19a53;
            font-size: 2rem;
            margin: 0;
        }
        h2 {
            color: #c19a53;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }
        .error-box {
            background: #ffe6e6;
            border: 1px solid #f5a5a5;
            color: #b91c1c;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .error-box i {
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        input:focus {
            border-color: #c19a53;
            outline: none;
            box-shadow: 0 0 0 3px rgba(193, 154, 83, 0.1);
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #c19a53;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn:hover {
            background: #8b7355;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(193, 154, 83, 0.3);
        }
        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #666;
        }
        .footer-text a {
            color: #c19a53;
            text-decoration: none;
            font-weight: 500;
        }
        .footer-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo-section">
                <h1><span style="color: #c19a53;">AMUNRA</span></h1>
            </div>
            <h2>Welcome Back</h2>
            <p class="subtitle">Login to your account</p>

            <?php if (!empty($error)): ?>
                <div class="error-box">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php" novalidate>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn">Login</button>
            </form>

            <div class="footer-text">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
            <div class="footer-text" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                
            </div>
        </div>
    </div>
</body>
</html>

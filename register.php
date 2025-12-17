<?php
require_once __DIR__ . '/config.php';



$errors = [];
$old = ['username' => '', 'email' => '', 'fullname' => '', 'contact_number' => '', 'nic' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $nic = trim($_POST['nic'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    $old['username'] = $username;
    $old['email'] = $email;
    $old['fullname'] = $fullname;
    $old['contact_number'] = $contact_number;
    $old['nic'] = $nic;

    if ($username === '') $errors[] = 'Username is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($fullname === '') $errors[] = 'Full name is required.';
    if ($contact_number === '') $errors[] = 'Contact number is required.';
    if ($nic === '') $errors[] = 'NIC is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // check uniqueness
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->fetch_assoc();
        $stmt->close();

        if ($exists) {
            $errors[] = 'Username or email already in use.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare('INSERT INTO users (username, email, fullname, contact_number, nic, password) VALUES (?, ?, ?, ?, ?, ?)');
            $insert->bind_param('ssssss', $username, $email, $fullname, $contact_number, $nic, $hash);
            $insert->execute();
            $user_id = (int)$conn->insert_id;
            $insert->close();
            // store logged-in user id in session
            $_SESSION['user_id'] = $user_id;
            // redirect to home
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register — AMUNRA</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* minimal form styling consistent with site */
body{font-family:Segoe UI, Tahoma, sans-serif; background:#fff; color:#333; padding:40px;}
.container{max-width:520px;margin:0 auto;}
.card{border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.08); padding:22px;}
h2{color:#c19a53;margin-bottom:10px;}
.input{width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-top:6px;margin-bottom:12px;}
.btn{display:inline-block;padding:10px 18px;background:#c19a53;color:#fff;border-radius:6px;border:0;cursor:pointer;}
.err{background:#ffe6e6;border:1px solid #f5c2c2;color:#8b1a1a;padding:10px;border-radius:6px;margin-bottom:12px;}
.note{font-size:0.9rem;color:#666;margin-top:12px;}
.link{color:#c19a53;text-decoration:none;}
</style>
</head>
<body>
<div class="container">
    <div class="card">
        <h2>Create account</h2>
        <?php if (!empty($errors)): ?>
            <div class="err">
                <?php foreach ($errors as $e): ?>
                    <div><?php echo htmlspecialchars($e); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php" novalidate>
            <label>Username
                <input class="input" type="text" name="username" required value="<?php echo htmlspecialchars($old['username']); ?>">
            </label>

            <label>Full Name
                <input class="input" type="text" name="fullname" required value="<?php echo htmlspecialchars($old['fullname']); ?>">
            </label>

            <label>Email
                <input class="input" type="email" name="email" required value="<?php echo htmlspecialchars($old['email']); ?>">
            </label>

            <label>Contact Number
                <input class="input" type="tel" name="contact_number" required value="<?php echo htmlspecialchars($old['contact_number']); ?>">
            </label>

            <label>NIC (National ID Card)
                <input class="input" type="text" name="nic" required value="<?php echo htmlspecialchars($old['nic']); ?>">
            </label>

            <label>Password
                <input class="input" type="password" name="password" required>
            </label>

            <label>Confirm Password
                <input class="input" type="password" name="confirm" required>
            </label>

            <div style="display:flex;gap:8px;align-items:center;">
                <button class="btn" type="submit">Register</button> <br>
                <div class="note">Already have an account? <a class="link" href="login.php">Login</a></div>
            </div>
        </form>
    </div>
</div>
</body>
</html>

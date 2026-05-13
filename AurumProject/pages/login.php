<?php


require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

if (isUserLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $result = loginUser($username, $password);
        if ($result['success']) {
            header("Location: index.php");
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Aurum</title>
    <link rel="stylesheet" href="../assets/css/style3.css">
    <style>
        .error-msg   { color: #d9534f; margin: 10px 0; padding: 10px; background: #f8d7da; border-radius: 5px; }
        .success-msg { color: #28a745; margin: 10px 0; padding: 10px; background: #d4edda; border-radius: 5px; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">Aurum</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="hotels.php">Hotels</a></li>
            <li><a href="about-contact.php">About Us</a></li>
            <li><a href="signup.php">Sign Up</a></li>
        </ul>
    </nav>

    <section class="hotel-info" style="max-width:400px;margin:60px auto;">
        <h2>Login</h2>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <div class="filter-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>
            <div class="filter-group" style="margin-top:20px;">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" style="margin-top:30px;width:100%;">Login</button>
        </form>

        <p style="text-align:center;margin-top:20px;">
            Don't have an account? <a href="signup.php">Sign up here</a>
        </p>
    </section>

    <footer class="main-footer">
        <hr>
        <div class="footer-section">
            <h4>Contact Us</h4>
            <p>📞 Phone: +20 123 456 7890</p>
            <p>📧 Email: support@aurum.com</p>
            <p>📍 Address: Nasr City, Cairo, Egypt</p>
        </div>
        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="hotels.php">Browse Hotels</a></li>
                <li><a href="about-contact.php">About Our Company</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>Follow Us</h4>
            <p>
                <a href="https://www.facebook.com/">Facebook</a> |
                <a href="https://www.instagram.com/">Instagram</a> |
                <a href="https://x.com/">X</a> |
                <a href="https://www.tiktok.com/">TikTok</a>
            </p>
        </div>
        <br>
        <div class="footer-bottom">
            <p>&copy; 2026 Aurum Hotel Bookings. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

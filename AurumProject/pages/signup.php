<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
if (isUserLoggedIn()) {
    header("Location: index.php");
    exit();
}

$errors    = [];
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username        = ($_POST['username']        ?? '');
    $email           = ($_POST['email']           ?? '');
    $password        = $_POST['password']             ?? '';
    $password_repeat = $_POST['password-repeat']      ?? '';
    $phone           = ($_POST['phone']           ?? '');
    $full_name       = ($_POST['full_name']       ?? '') ?: $username;

    $form_data = [
        'username' => $username,
        'email'    => $email,
        'phone'    => $phone,
    ];
    if (strlen($username) < 3)                          
        $errors[] = 'Username must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))     
        $errors[] = 'Please enter a valid email.';
    if (strlen($password) < 6)                          
        $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $password_repeat)                 
        $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $result = registerUser($username, $email, $password, $phone, $full_name);
        if ($result['success']) {
            header("Location: login.php");
            exit();
        } else {
            $errors[] = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up | Aurum</title>
    <link rel="stylesheet" href="../assets/css/style3.css">
    <style>
        .error-list { color: #d9534f; margin: 10px 0; padding: 10px; background: #f8d7da; border-radius: 5px; }
        .error-list ul { margin: 5px 0; margin-left: 20px; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">Aurum</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="hotels.php">Hotels</a></li>
            <li><a href="about-contact.php">About Us</a></li>
            <li><a href="login.php">Login</a></li>
        </ul>
    </nav>

    <section class="hotel-info" style="max-width:500px;margin:40px auto;">
        <h2>Create Account</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?php echo htmlspecialchars($e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="signup.php" method="post">
            <div class="filter-group">
                <label for="username"><b>Username:</b></label>
                <input type="text" id="username" name="username"
                       value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>" required>
            </div>
            <div class="filter-group" style="margin-top:15px;">
                <label for="email"><b>Email:</b></label>
                <input type="email" id="email" name="email"
                       value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
            </div>
            <div class="filter-group" style="margin-top:15px;">
                <label for="password"><b>Password:</b></label>
                <input type="password" id="password" name="password" placeholder="At least 6 characters" required>
            </div>
            <div class="filter-group" style="margin-top:15px;">
                <label for="password-repeat"><b>Repeat Password:</b></label>
                <input type="password" id="password-repeat" name="password-repeat" placeholder="Confirm password" required>
            </div>
            <div class="filter-group" style="margin-top:15px;">
                <label for="phone"><b>Phone Number:</b></label>
                <input type="tel" id="phone" name="phone" placeholder="+20 123 456 7890"
                       value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>" required>
            </div>
            <button type="submit" style="margin-top:30px;width:100%;">Sign Up</button>
        </form>

        <p style="text-align:center;margin-top:20px;">
            Already have an account? <a href="login.php">Login here</a>
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

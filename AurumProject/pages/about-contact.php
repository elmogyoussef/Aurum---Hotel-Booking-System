<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
$contact_success = '';
$contact_errors  = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = ($_POST['name']    ?? '');
    $email   = ($_POST['email']   ?? '');
    $subject = ($_POST['subject'] ?? '');
    $message = ($_POST['message'] ?? '');

    if (strlen($name)    < 3)                              
        $contact_errors[] = "Name must be at least 3 characters.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))        
        $contact_errors[] = "Invalid email address.";
    if (strlen($subject) < 5)                              
        $contact_errors[] = "Subject must be at least 5 characters.";
    if (strlen($message) < 10)                             
        $contact_errors[] = "Message must be at least 10 characters.";
    if (empty($contact_errors)) {
        if (saveContactMessage($name, $email, $subject, $message)) {
            $contact_success = "Thank you for your message! We'll get back to you soon.";
        } else {
            $contact_errors[] = "Failed to send message. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About & Contact | Aurum</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .about-section {
            padding: 80px 10%;
            background: linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080') center/cover;
            color: white;
            text-align: center;
        }
        .about-section h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .about-section p {
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto;
        }
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            padding: 60px 10%;
            max-width: 1200px;
            margin: 0 auto;
            align-items: center;
        }
        .about-text h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }
        .about-text p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        .about-features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        .feature-item {
            padding: 20px;
            background: #f8f6f2;
            border-radius: 8px;
        }
        .feature-item h3 {
            color: #d4af37;
            margin-bottom: 10px;
        }
        .feature-item p {
            color: #666;
            font-size: 14px;
        }
        .contact-section {
            padding: 60px 10%;
            background: #f8f6f2;
        }
        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .contact-header {
            text-align: center;
            margin-bottom: 50px;
        }
        .contact-header h2 {
            font-size: 36px;
            margin-bottom: 10px;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        .contact-info {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }
        .info-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .info-box h3 {
            color: #d4af37;
            margin-bottom: 10px;
        }
        .info-box p {
            color: #666;
        }
        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #111;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }
        .submit-btn {
            width: 100%;
            padding: 15px;
            background: #111;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .submit-btn:hover {
            background: #d4af37;
            color: #111;
        }
        .success-message {
            background: #c8e6c9;
            color: #27ae60;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error-message {
            background: #ffcdd2;
            color: #e74c3c;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .about-content, .contact-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">Aurum</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="hotels.php">Hotels</a></li>
                        <li><a href="about-contact.php">About Us</a></li>
            <?php if (isUserLoggedIn()): ?>
                <li><a href="mybookings.php">My Bookings</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <section class="about-section">
        <h1>About Aurum</h1>
        <p>Your trusted partner for finding the perfect hotel stay</p>
    </section>
    <section class="about-content">
        <div class="about-text">
            <h2>Welcome to Aurum</h2>
            <p>Aurum is a leading hotel booking platform dedicated to providing travelers with exceptional experiences. With a network of premium hotels across the globe, we make it easy to find and book your ideal accommodation.</p>    
            <p>Our mission is to simplify hotel booking by offering a user-friendly platform, competitive prices, and outstanding customer service. Whether you're traveling for business or leisure, Aurum has the perfect hotel for you.</p>
            <div class="about-features">
                <div class="feature-item">
                    <h3>✓ Best Prices</h3>
                    <p>We guarantee the lowest prices on hotel bookings</p>
                </div>
                <div class="feature-item">
                    <h3>✓ Easy Booking</h3>
                    <p>Quick and simple reservation process in minutes</p>
                </div>
                <div class="feature-item">
                    <h3>✓ 24/7 Support</h3>
                    <p>Our customer support team is always here to help</p>
                </div>
                <div class="feature-item">
                    <h3>✓ Verified Reviews</h3>
                    <p>Authentic reviews from real travelers</p>
                </div>
            </div>
        </div>
        <div style="text-align: center;">
            <img src="../assets/images/hero-banner.jpg" alt="Hotel" width="500" height="500"   style="border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        </div>
    </section>
    <section class="contact-section">
        <div class="contact-container">
            <div class="contact-header">
                <h2>Get In Touch</h2>
                <p>Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
            </div>
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="info-box">
                        <h3>📞 Phone</h3>
                        <p>+20 123 456 7890</p>
                        <p style="font-size: 12px; margin-top: 10px;">Available Monday - Friday, 9AM - 6PM</p>
                    </div>
                    <div class="info-box">
                        <h3>📧 Email</h3>
                        <p>support@aurum.com</p>
                        <p>info@aurum.com</p>
                    </div>
                    <div class="info-box">
                        <h3>📍 Address</h3>
                        <p>Aurum Headquarters<br>Cairo, Egypt</p>
                    </div>
                </div>
                <div class="contact-form">
                    <?php if (!empty($contact_success)): ?>
                        <div class="success-message">
                            ✓ <?php echo htmlspecialchars($contact_success); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($contact_errors)): ?>
                        <div class="error-message">
                            <strong>Please fix the following errors:</strong>
                            <ul>
                                <?php foreach ($contact_errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="about-contact.php" method="post">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="message" required></textarea>
                        </div>
                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
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
                <li><a href="about.php">About Our Company</a></li>
                <li><a href="contact.php">Get in Touch</a></li>
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
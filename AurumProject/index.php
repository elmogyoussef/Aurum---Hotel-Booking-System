<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$loggedIn = isUserLoggedIn();
$hotels = getAllHotels();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">Aurum</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="hotels.php">Hotels</a></li>
            <li><a href="about-contact.php">About Us</a></li>
            <?php if ($loggedIn): ?>
        <li><a href="mybookings.php">My Bookings</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php"    >Logout</a></li>
      <?php else: ?>
        <li><a href="login.php">Login</a></li>
        <li><a href="signup.php">Sign Up</a></li>
      <?php endif; ?>
        </ul>
    </nav>
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Aurum</h1>
            <p>Your Ultimate hotel booking platform</p>
            <a href="hotels.php">Explore Hotels</a>
            <form action="hotels.php" method="GET">
                <input type="text" name="destination" placeholder="Search for hotels">
                <button type="submit">Search</button>
            </form>
        </div>
    </section>
    <section class="featured-hotels">
        <h2>Featured Hotels</h2>
        

        <div class="hotel-card">
            <img src="assets/images/hotel-banner.jpg" height="200" width="300" alt="Hotel Image">
            <h3>Sofitel El-Gezira Hotel</h3>
            <p>Location: Cairo, Egypt</p>
            <p>Price: $250/night</p>
            <p>Rating: ⭐ 4.8</p>
            <a href="hotel-details.php?id=1"><button>View Details</button></a>
        </div>
        
        <div class="hotel-card">
            <img src="94190040.jpg" height="200" width="300" alt="Hotel Image">
            <h3>Four Seasons Hotel</h3>
            <p>Location: Cairo, Egypt</p>
            <p>Price: $450/night</p>
            <p>Rating: ⭐ 5.0</p>
            <a href="hotel-details.php?id=2"><button>View Details</button></a>
        </div>
        
        <div class="hotel-card">
            <img src="411834440.jpg" height="200" width="300" alt="Hotel Image">
            <h3>Semiramis Hotel</h3>
            <p>Location: Cairo, Egypt</p>
            <p>Price: $180/night</p>
            <p>Rating: ⭐ 4.5</p>
            <a href="hotel-details.php?id=4"><button>View Details</button></a>
        </div>
        <div class="hotel-card">
            <img src="546401319.jpg" height="200" width="300" alt="Hotel Image">
            <h3>Mena House</h3>
            <p>Location: Giza, Egypt</p>
            <p>Price: $300/night</p>
            <p>Rating: ⭐ 4.9</p>
            <a href="hotel-details.php?id=3"><button>View Details</button></a>
        </div>
    </section>
    <section class="services">
        <hr> <h2>Why Choose Us</h2>
        
        <div class="service-item">
            <h3>📅 Easy Booking</h3>
            <p>Book your stay in just a few clicks with our simple and fast interface.</p>
        </div>

        <div class="service-item">
            <h3>💰 Best Prices</h3>
            <p>We guarantee the most competitive rates for all our featured hotels.</p>
        </div>

        <div class="service-item">
            <h3>🏨 Luxury Rooms</h3>
            <p>Enjoy hand-picked premium rooms with top-tier amenities and comfort.</p>
        </div>

        <div class="service-item">
            <h3>📞 24/7 Support</h3>
            <p>Our dedicated customer service team is always here to help you anytime.</p>
        </div>
    </section>
    <section class="special-offers">
        <hr>
        <h2>Special Offers & Deals</h2>
        
        <div class="offer-item">
            <h3>☀️ Summer Flash Sale</h3>
            <p>Book any beachside hotel this month and get <strong>20% OFF</strong> your total stay!</p>
            <p><strong>Promo Code:</strong> <mark>SUMMER20</mark></p>
        </div>
    </section>

    <section class="reviews">
        <hr>
        <h2>What Our Guests Say</h2>
        <div class="reviews-card">
            <p><em>"Aurum made my vacation planning so much easier. I found a luxury room in Mena House for a fraction of the price I saw elsewhere!"</em></p>
            <p><strong>—Sydney Sweeny</strong> ⭐⭐⭐⭐⭐</p>
        </div>
        <br>
        <div class="reviews-card">
            <p><em>"The 24/7 support team really helped me out when I needed to change my booking dates last minute. Exceptional service."</em></p>
            <p><strong>—Scarlett Johansson</strong> ⭐⭐⭐⭐⭐</p>
        </div>
        <br>
        <div class="reviews-card">
            <p><em>"Fast, reliable, and the hotel photos were exactly what I got in person. This is my go-to booking site now."</em></p>
            <p><strong>—Anne Hathaway</strong> ⭐⭐⭐⭐</p>
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
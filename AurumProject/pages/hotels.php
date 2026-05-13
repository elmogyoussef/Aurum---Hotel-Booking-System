<?php

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$loggedIn = isUserLoggedIn();
$hotels = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['destination'])) {
    $location = trim($_GET['destination']);
    $min_price = 0;
    $max_price = 10000;

    if (isset($_GET['price']) && $_GET['price'] !== '') {
        if ($_GET['price'] === '$0 - $200') { $min_price = 0; $max_price = 200; }
        elseif ($_GET['price'] === '$200 - $500') { $min_price = 200; $max_price = 500; }
        elseif ($_GET['price'] === '$500+') { $min_price = 500; $max_price = 10000; }
    }

    $checkin = isset($_GET['checkin']) ? trim($_GET['checkin']) : '';
    $checkout = isset($_GET['checkout']) ? trim($_GET['checkout']) : '';
    $date_error = '';
    if (!empty($checkin) && !empty($checkout)) {
        if (strtotime($checkout) <= strtotime($checkin)) {
            $date_error = 'Check-out date must be after check-in date.';
        }
    }
    
    $hotels = searchHotels($location, $min_price, $max_price);
} else {
    $hotels = getAllHotels();
    $date_error = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link rel="stylesheet" href="../assets/css/style.css">

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
    <section class="page-banner">
    <h1>Find Your Perfect Hotel</h1>
    <p>Browse luxury stays and affordable rooms</p>
</section>


<section class="filter-wrapper">
    <form class="search-filter-bar" action="hotels.php" method="GET">

        <div class="filter-group">
            <label>Destination</label>
            <input type="text" name="destination" placeholder="Cairo, Giza, Alexandria" value="<?php echo htmlspecialchars($_GET['destination'] ?? ''); ?>">
        </div>

        <div class="filter-group">
            <label>Check-In</label>
            <input type="date" name="checkin" value="<?php echo htmlspecialchars($_GET['checkin'] ?? ''); ?>">
        </div>

        <div class="filter-group">
            <label>Check-Out</label>
            <input type="date" name="checkout" value="<?php echo htmlspecialchars($_GET['checkout'] ?? ''); ?>">
        </div>

        <div class="filter-group">
            <label>Guests</label>
            <select name="guests">
                <option>1 Guest</option>
                <option>2 Guests</option>
                <option>3 Guests</option>
                <option>4+ Guests</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Price</label>
            <select name="price">
                <option value="">Any Price</option>
                <option value="$0 - $200" <?php echo (isset($_GET['price']) && $_GET['price'] === '$0 - $200') ? 'selected' : ''; ?>>$0 - $200</option>
                <option value="$200 - $500" <?php echo (isset($_GET['price']) && $_GET['price'] === '$200 - $500') ? 'selected' : ''; ?>>$200 - $500</option>
                <option value="$500+" <?php echo (isset($_GET['price']) && $_GET['price'] === '$500+') ? 'selected' : ''; ?>>$500+</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Rating</label>
            <select name="rating">
                <option>3 Stars+</option>
                <option>4 Stars+</option>
                <option>5 Stars</option>
            </select>
        </div>

        <button type="submit" class="luxury-search-btn">
            Search Hotels
        </button>

    </form>
    <?php if (!empty($date_error)): ?>
        <p style="color:#e74c3c; text-align:center; margin-top:10px; font-weight:bold;">⚠ <?php echo htmlspecialchars($date_error); ?></p>
    <?php endif; ?>
</section>
<section class="hotel-listings">

<?php
$hotel_images = [
    1 => '../assets/images/hotel-banner.jpg',
    2 => '94190040.jpg',
    3 => '546401319.jpg',
    4 => '411834440.jpg',
];
$hotel_badges = [1 => 'SUPERHOST', 2 => 'LUXURY PICK', 3 => 'BEST SELLER', 4 => '20% OFF'];
$default_image = '../assets/images/hotel-banner.jpg';

if (empty($hotels)): ?>
    <p style="text-align:center;padding:40px;color:#666;">No hotels found matching your search. <a href="hotels.php">View all hotels</a></p>
<?php else: foreach ($hotels as $hotel):
    $img = $hotel_images[$hotel['id']] ?? $default_image;
    $badge = $hotel_badges[$hotel['id']] ?? 'FEATURED';
?>
<div class="hotel-card">
    <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
    
    <div class="hotel-info">
        <span class="badge"><?php echo htmlspecialchars($badge); ?></span>

        <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>

        <p>📍 <?php echo htmlspecialchars($hotel['location']); ?>, Egypt</p>
        <p>⭐ <?php echo htmlspecialchars($hotel['rating']); ?> (<?php echo htmlspecialchars($hotel['reviews_count']); ?> Reviews)</p>
        <p><?php echo htmlspecialchars($hotel['description']); ?></p>

        <h4>$<?php echo number_format($hotel['price_per_night'], 0); ?> / night</h4>

        <a href="hotel-details.php?id=<?php echo $hotel['id']; ?>">
            <button>View Details</button>
        </a>
    </div>
</div>
<?php endforeach; endif; ?>

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

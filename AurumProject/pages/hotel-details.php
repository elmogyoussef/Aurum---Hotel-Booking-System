<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

if (!isset($_GET['id'])) {
    header("Location: hotels.php");
    exit();
}

$hotel_id = (int)$_GET['id'];
$hotel = getHotelById($hotel_id);

if (!$hotel) {
    header("Location: hotels.php");
    exit();
}

$loggedIn = isUserLoggedIn();

$hotel_images = [
    1 => '../assets/images/hotel-banner.jpg',
    2 => '94190040.jpg',
    3 => '546401319.jpg',
    4 => '411834440.jpg',
];
$main_img = $hotel_images[$hotel_id] ?? '765735827.jpg';
$rooms = [
    [
        'name'       => 'Deluxe Room',
        'multiplier' => 1.0,
        'desc'       => 'Spacious room with beautiful views, a king-size bed, and all modern amenities for a comfortable stay.',
        'img'        => '776693779.jpg',
        'param'      => 'deluxe',
        'features'   => ['King-size bed', 'City / river view', 'Free Wi-Fi', 'Flat-screen TV'],
    ],
    [
        'name'       => 'Executive Suite',
        'multiplier' => 1.5,
        'desc'       => 'Luxurious suite with a separate living area, premium amenities, and stunning panoramic views.',
        'img'        => '765735780.jpg',
        'param'      => 'executive',
        'features'   => ['Separate living area', 'Panoramic views', 'Mini bar', 'Complimentary breakfast'],
    ],
    [
        'name'       => 'Presidential Suite',
        'multiplier' => 2.0,
        'desc'       => 'Opulent suite with a private terrace, butler service, panoramic views, and exclusive facilities.',
        'img'        => '847554326.jpg',
        'param'      => 'presidential',
        'features'   => ['Private terrace', 'Butler service', 'Jacuzzi', 'Airport transfer'],
    ],
];

$amenities = explode(',', $hotel['amenities']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($hotel['name']); ?> | Aurum</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .details-page { max-width: 1100px; margin: 0 auto; padding: 40px 20px 60px; }

        .hotel-hero-banner {
            width: 100%; height: 420px; object-fit: cover;
            border-radius: 12px; display: block; margin-bottom: 30px;
        }
        .hotel-title { font-size: 36px; font-weight: bold; margin-bottom: 8px; }
        .hotel-meta  { display: flex; gap: 20px; align-items: center; color: #555; margin-bottom: 30px; flex-wrap: wrap; }
        .hotel-meta .rating { color: #d4af37; font-weight: bold; font-size: 16px; }
        .hotel-meta .location { font-size: 15px; }
        .hotel-meta .price-tag {
            background: #111; color: #d4af37; padding: 6px 16px;
            border-radius: 20px; font-weight: bold; font-size: 15px; margin-left: auto;
        }

        .gallery { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 40px; }
        .gallery img {
            width: 100%; height: 140px; object-fit: cover;
            border-radius: 8px; cursor: pointer; transition: opacity 0.2s;
        }
        .gallery img:hover { opacity: 0.85; }

        .section-title {
            font-size: 24px; font-weight: bold;
            border-left: 4px solid #d4af37; padding-left: 12px;
            margin: 40px 0 20px;
        }

        .amenities-grid { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 10px; }
        .amenity-badge {
            background: #f5f0e8; border: 1px solid #e0d5c0;
            color: #333; padding: 8px 16px; border-radius: 20px; font-size: 14px;
        }

        .rooms-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 10px; }

        .room-card {
            border-radius: 12px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.10);
            background: white; display: flex; flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .room-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        .room-card img {
            width: 100%; height: 200px; object-fit: cover; display: block;
        }
        .room-card-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }
        .room-card-body h3 { font-size: 18px; font-weight: bold; margin: 0 0 4px; }
        .room-price { color: #d4af37; font-weight: bold; font-size: 15px; margin-bottom: 10px; }
        .room-desc { color: #555; font-size: 14px; line-height: 1.5; margin-bottom: 14px; }
        .room-features { list-style: none; padding: 0; margin: 0 0 18px; }
        .room-features li { font-size: 13px; color: #444; padding: 3px 0; }
        .room-features li::before { content: "✓ "; color: #d4af37; font-weight: bold; }
        .room-card-body .book-btn {
            display: block; text-align: center; padding: 11px;
            background: #111; color: white; border-radius: 6px;
            text-decoration: none; font-weight: bold; font-size: 14px;
            margin-top: auto; transition: background 0.2s;
        }
        .room-card-body .book-btn:hover { background: #d4af37; color: #111; }

        .reviews-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .review-card {
            background: #fafafa; border: 1px solid #eee;
            border-radius: 10px; padding: 20px;
        }
        .review-card p.quote { font-style: italic; color: #333; margin-bottom: 12px; font-size: 14px; line-height: 1.5; }
        .review-card p.author { font-weight: bold; font-size: 13px; color: #555; }

        .cta-section {
            background: #111; color: white; border-radius: 12px;
            padding: 50px 40px; text-align: center; margin-top: 50px;
        }
        .cta-section h2 { font-size: 28px; margin-bottom: 8px; }
        .cta-section p  { color: #aaa; margin-bottom: 24px; }
        .cta-section a  {
            display: inline-block; background: #d4af37; color: #111;
            padding: 14px 36px; border-radius: 6px; font-weight: bold;
            font-size: 16px; text-decoration: none; transition: opacity 0.2s;
        }
        .cta-section a:hover { opacity: 0.85; }

        @media (max-width: 900px) {
            .rooms-grid   { grid-template-columns: 1fr; }
            .reviews-grid { grid-template-columns: 1fr; }
            .gallery      { grid-template-columns: repeat(2, 1fr); }
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
            <?php if ($loggedIn): ?>
                <li><a href="mybookings.php">My Bookings</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="details-page">

        <img class="hotel-hero-banner"
             src="<?php echo htmlspecialchars($main_img); ?>"
             alt="<?php echo htmlspecialchars($hotel['name']); ?>">

        <div class="hotel-title"><?php echo htmlspecialchars($hotel['name']); ?></div>
        <div class="hotel-meta">
            <span class="rating">⭐ <?php echo $hotel['rating']; ?> (<?php echo $hotel['reviews_count']; ?> reviews)</span>
            <span class="location">📍 <?php echo htmlspecialchars($hotel['location']); ?></span>
            <span class="price-tag">From $<?php echo number_format($hotel['price_per_night'], 2); ?>/night</span>
        </div>
        <p style="color:#444; line-height:1.7; margin-bottom:0;">
            <?php echo htmlspecialchars($hotel['description']); ?>
        </p>

        <div class="section-title">Gallery</div>
        <div class="gallery">
            <img src="765735771.jpg" alt="Lobby">
            <img src="765735820.jpg" alt="Restaurant">
            <img src="847554330.jpg" alt="Gym">
            <img src="776693779.jpg" alt="Room">
            <img src="847554326.jpg" alt="Suite">
            <img src="765735780.jpg" alt="Room">
            <img src="765735836.jpg" alt="Pool">
            <img src="<?php echo htmlspecialchars($main_img); ?>" alt="Hotel">
        </div>

        <div class="section-title">Amenities</div>
        <div class="amenities-grid">
            <?php foreach ($amenities as $a): ?>
                <span class="amenity-badge">✦ <?php echo htmlspecialchars(trim($a)); ?></span>
            <?php endforeach; ?>
        </div>

        <div class="section-title">Room Types</div>
        <div class="rooms-grid">
            <?php foreach ($rooms as $room): ?>
            <div class="room-card">
                <img src="<?php echo $room['img']; ?>" alt="<?php echo $room['name']; ?>">
                <div class="room-card-body">
                    <h3><?php echo $room['name']; ?></h3>
                    <div class="room-price">
                        From $<?php echo number_format($hotel['price_per_night'] * $room['multiplier'], 2); ?>/night
                    </div>
                    <p class="room-desc"><?php echo $room['desc']; ?></p>
                    <ul class="room-features">
                        <?php foreach ($room['features'] as $f): ?>
                            <li><?php echo htmlspecialchars($f); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($loggedIn): ?>
                        <a class="book-btn" href="bookings.php?hotel_id=<?php echo $hotel_id; ?>&room=<?php echo $room['param']; ?>">
                            Book Now
                        </a>
                    <?php else: ?>
                        <a class="book-btn" href="login.php">Login to Book</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="section-title">What Our Guests Say</div>
        <div class="reviews-grid">
            <div class="review-card">
                <p class="quote">"Fantastic stay and amazing service from the moment we arrived."</p>
                <p class="author">— Mohamed Salah &nbsp; ⭐⭐⭐⭐⭐</p>
            </div>
            <div class="review-card">
                <p class="quote">"Excellent! The staff was incredibly helpful and the rooms are stunning."</p>
                <p class="author">— Omar el-sherif (allah yr7mo) &nbsp; ⭐⭐⭐⭐⭐</p>
            </div>
            <div class="review-card">
                <p class="quote">"Best hotel experience I've ever had. Will definitely come back!"</p>
                <p class="author">— En3am Salosa &nbsp; ⭐⭐⭐⭐⭐</p>
            </div>
        </div>

        <div class="cta-section">
            <h2>Ready to Book Your Stay?</h2>
            <p>Secure your room today and enjoy a world-class experience at <?php echo htmlspecialchars($hotel['name']); ?>.</p>
            <?php if ($loggedIn): ?>
                <a href="bookings.php?hotel_id=<?php echo $hotel_id; ?>">Reserve Now</a>
            <?php else: ?>
                <a href="login.php">Login to Reserve</a>
            <?php endif; ?>
        </div>

    </div>

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

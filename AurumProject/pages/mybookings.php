<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

$user_id  = getCurrentUserId();
$user     = getUserById($user_id);
$bookings = getUserBookings($user_id);

$counts = ['total' => count($bookings), 'confirmed' => 0, 'pending' => 0, 'cancelled' => 0];
foreach ($bookings as $b) {
    if (isset($counts[$b['status']])) $counts[$b['status']]++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings | Aurum</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .bookings-page { padding: 50px 8%; max-width: 1300px; margin: 0 auto; }

        .booking-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 36px;
        }
        .stat-box {
            background: white;
            border-radius: 12px;
            padding: 22px 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,.07);
            border-top: 4px solid #ddd;
        }
        .stat-box.all  { border-color: #111; }
        .stat-box.conf { border-color: #27ae60; }
        .stat-box.pend { border-color: #f39c12; }
        .stat-box.canc { border-color: #e74c3c; }
        .stat-box .num { font-size: 32px; font-weight: 700; color: #111; }
        .stat-box .lbl { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }

        .booking-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0,0,0,.08);
            margin-bottom: 22px;
            display: grid;
            grid-template-columns: 200px 1fr auto;
            overflow: hidden;
            transition: box-shadow .25s;
        }
        .booking-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,.14); }

        .booking-img { width: 200px; height: 100%; min-height: 180px; object-fit: cover; }

        .booking-body {
            padding: 22px 28px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .booking-hotel   { font-size: 22px; font-weight: 700; color: #111; margin-bottom: 6px; }
        .booking-location { color: #888; font-size: 13px; margin-bottom: 14px; }
        .booking-meta {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .meta-item .meta-label { font-size: 11px; text-transform: uppercase; color: #aaa; letter-spacing: .8px; }
        .meta-item .meta-val   { font-size: 14px; color: #333; font-weight: 600; margin-top: 2px; }

        .booking-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 13px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 14px;
            width: fit-content;
        }
        .status-confirmed { background: #e8f8f0; color: #27ae60; }
        .status-pending   { background: #fff4e0; color: #e07b00; }
        .status-cancelled { background: #fdecea; color: #e74c3c; }

        .booking-side {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: space-between;
            padding: 22px 24px;
            border-left: 1px solid #f2f2f2;
            min-width: 160px;
        }
        .booking-price       { font-size: 26px; font-weight: 700; color: #d4af37; }
        .booking-price span  { font-size: 13px; color: #aaa; display: block; font-weight: 400; }

        .btn-action {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: block;
            text-align: center;
            width: 100%;
            transition: .2s;
            margin-top: 8px;
        }
        .btn-view     { background: #111; color: white; }
        .btn-view:hover { background: #d4af37; color: #111; }
        .btn-cancel   { background: #fdecea; color: #e74c3c; }
        .btn-cancel:hover { background: #e74c3c; color: white; }
        .btn-disabled { background: #f2f2f2; color: #bbb; cursor: not-allowed; }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.07);
        }
        .empty-icon { font-size: 64px; margin-bottom: 20px; }
        .empty-state h2 { font-size: 28px; color: #333; margin-bottom: 10px; }
        .empty-state p  { color: #888; margin-bottom: 28px; }
        .empty-state a  {
            display: inline-block;
            padding: 14px 34px;
            background: #111;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        .empty-state a:hover { background: #d4af37; color: #111; }

        .alert-success { background:#e8f8f0; color:#27ae60; padding:14px 18px; border-radius:8px; margin-bottom:24px; font-weight:600; }

        .page-title          { margin-bottom: 32px; }
        .page-title h1       { font-size: 42px; color: #111; }
        .page-title p        { color: #888; margin-top: 6px; }

        @media (max-width: 768px) {
            .booking-card  { grid-template-columns: 1fr; }
            .booking-img   { width: 100%; height: 200px; }
            .booking-side  { flex-direction: row; align-items: center; border-left: none; border-top: 1px solid #f2f2f2; }
            .booking-meta  { grid-template-columns: 1fr 1fr; }
            .booking-stats { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">Aurum</div>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="hotels.php">Hotels</a></li>
        <li><a href="mybookings.php">My Bookings</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="bookings-page">
    <div class="page-title">
        <h1>My Bookings</h1>
        <p>Welcome back, <?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?>! Here are all your reservations.</p>
    </div>

    <?php if (isset($_SESSION['cancel_message'])): ?>
        <div class="alert-success">✓ <?php echo htmlspecialchars($_SESSION['cancel_message']); unset($_SESSION['cancel_message']); ?></div>
    <?php endif; ?>

    <div class="booking-stats">
        <div class="stat-box all"><div class="num"><?php echo $counts['total']; ?></div><div class="lbl">Total</div></div>
        <div class="stat-box conf"><div class="num"><?php echo $counts['confirmed']; ?></div><div class="lbl">Confirmed</div></div>
        <div class="stat-box canc"><div class="num"><?php echo $counts['cancelled']; ?></div><div class="lbl">Cancelled</div></div>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="empty-state">
            <div class="empty-icon">🏨</div>
            <h2>No bookings yet</h2>
            <p>Start exploring our hotels and make your first reservation today!</p>
            <a href="hotels.php">Browse Hotels</a>
        </div>
    <?php else: ?>
        <?php foreach ($bookings as $b):
            $ci   = new DateTime($b['check_in']);
            $co   = new DateTime($b['check_out']);
            $now  = new DateTime();
            $nights      = (int)$ci->diff($co)->days;
            $is_past     = $co < $now;
            $is_cancelled = $b['status'] === 'cancelled';
            $can_cancel  = !$is_past && !$is_cancelled && $ci > $now;
            $hotel_images = [
                1 => '../assets/images/hotel-banner.jpg',
                2 => '94190040.jpg',
                3 => '546401319.jpg',
                4 => '411834440.jpg',
            ];
            $img = $hotel_images[$b['hotel_id']] ?? '../assets/images/hotel-banner.jpg';
            $icons = ['confirmed'=>'✅','cancelled'=>'❌'];
            $icon = $icons[$b['status']] ?? '📋';
            $hotel_name = $b['hotel_name'] ?? ($b['name'] ?? 'Hotel');
        ?>
        <div class="booking-card">
            <img class="booking-img" src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($hotel_name); ?>">
            <div class="booking-body">
                <div>
                    <div class="booking-hotel"><?php echo htmlspecialchars($hotel_name); ?></div>
                    <div class="booking-location">📍 <?php echo htmlspecialchars($b['location']); ?></div>
                    <div class="booking-meta">
                        <div class="meta-item"><div class="meta-label">Check-In</div><div class="meta-val"><?php echo date('M d, Y', strtotime($b['check_in'])); ?></div></div>
                        <div class="meta-item"><div class="meta-label">Check-Out</div><div class="meta-val"><?php echo date('M d, Y', strtotime($b['check_out'])); ?></div></div>
                        <div class="meta-item"><div class="meta-label">Duration</div><div class="meta-val"><?php echo $nights; ?> night<?php echo $nights!=1?'s':''; ?></div></div>
                        <div class="meta-item"><div class="meta-label">Room Type</div><div class="meta-val"><?php echo htmlspecialchars(ucfirst($b['room_type'] ?? 'Standard')); ?></div></div>
                        <div class="meta-item"><div class="meta-label">Rooms</div><div class="meta-val"><?php echo $b['rooms']; ?></div></div>
                        <div class="meta-item"><div class="meta-label">Guests</div><div class="meta-val"><?php echo $b['guests']; ?></div></div>
                    </div>
                </div>
                <div class="booking-status status-<?php echo $b['status']; ?>"><?php echo $icon.' '.ucfirst($b['status']); ?></div>
            </div>
            <div class="booking-side">
                <div class="booking-price">$<?php echo number_format($b['total_price'],2); ?><span>total</span></div>
                <div style="width:100%">
                    <a href="confirmation.php?booking_id=<?php echo $b['id']; ?>" class="btn-action btn-view">View Details</a>
                    <?php if ($can_cancel): ?>
                        <a href="cancel-booking.php?booking_id=<?php echo $b['id']; ?>" class="btn-action btn-cancel" onclick="return confirm('Cancel this booking?')">Cancel</a>
                    <?php else: ?>
                        <button class="btn-action btn-disabled" disabled><?php echo $is_cancelled?'Cancelled':($is_past?'Completed':'Active'); ?></button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<footer class="main-footer">
    <hr>
    <div class="footer-section"><h4>Contact Us</h4><p>📞 +20 123 456 7890</p><p>📧 support@aurum.com</p><p>📍 Nasr City, Cairo, Egypt</p></div>
    <div class="footer-section"><h4>Quick Links</h4><ul><li><a href="index.php">Home</a></li><li><a href="hotels.php">Browse Hotels</a></li><li><a href="about-contact.php">About Us</a></li></ul></div>
    <div class="footer-section"><h4>Follow Us</h4><p><a href="https://www.facebook.com/">Facebook</a> | <a href="https://www.instagram.com/">Instagram</a> | <a href="https://x.com/">X</a></p></div>
    <div class="footer-bottom"><p>&copy; 2026 Aurum Hotel Bookings. All rights reserved.</p></div>
</footer>
</body>
</html>
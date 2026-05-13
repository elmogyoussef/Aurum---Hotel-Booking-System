<?php

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $hotel_id         = (int)$_POST['hotel_id'];       
    $room_type        = $_POST['room_type'];                
    $full_name        = ($_POST['full_name']);          
    $email            = ($_POST['email']);
    $phone            = ($_POST['phone']);
    $check_in         = ($_POST['check_in']);           
    $check_out        = ($_POST['check_out']);         
    $guests           = (int)$_POST['guests'];             
    $rooms            = (int)$_POST['rooms'];
    $total_price      = floatval($_POST['total_price']); 
    $special_requests = ($_POST['special_requests']);
    $card_number      = str_replace(' ', '', $_POST['card_number'] ?? ''); 
    $expiry           = $_POST['expiry'] ?? '';
    $cvv              = $_POST['cvv']    ?? '';

    $errors = [];

    if ($hotel_id == 0 || !getHotelById($hotel_id)) {
        $errors[] = "Invalid hotel selected.";
    }

    if (strlen($full_name) < 3) {
        $errors[] = "Full name must be at least 3 characters.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    if (strlen($phone) < 10) {
        $errors[] = "Phone number must be at least 10 digits.";
    }

    if (empty($check_in)) {
        $errors[] = "Please enter a check-in date.";
    }
    if (empty($check_out)) {
        $errors[] = "Please enter a check-out date.";
    }

    if (!empty($check_in) && !empty($check_out)) {
        if (strtotime($check_out) <= strtotime($check_in)) {
            $errors[] = "Check-out date must be after check-in date.";
        }
    }

    if ($guests < 1 || $guests > 10) {
        $errors[] = "Please select between 1 and 10 guests.";
    }
    if ($rooms < 1 || $rooms > 5) {
        $errors[] = "Please select between 1 and 5 rooms.";
    }

    if (!preg_match('/^[0-9]{13,19}$/', $card_number)) {
        $errors[] = "Invalid card number.";
    }

    if (!preg_match('/^[0-9]{2}\/[0-9]{2}$/', $_POST['expiry'])) {
        $errors[] = "Expiry must be in MM/YY format (e.g. 12/26).";
    }

    if (!preg_match('/^[0-9]{3}$/', $_POST['cvv'])) {
        $errors[] = "CVV must be 3 digits.";
    }

    if (!empty($errors)) {
        $booking_errors = $errors;
        goto show_form;
    }

    $user_id = getCurrentUserId();

    updateUserProfile($user_id, $full_name, $email, $phone);
    $result = createBooking($user_id, $hotel_id, $check_in, $check_out, $guests, $rooms, $total_price);

    if ($result['success']) {
        $_SESSION['last_booking'] = [
            'booking_id'  => $result['booking_id'],
            'hotel_id'    => $hotel_id,
            'check_in'    => $check_in,
            'check_out'   => $check_out,
            'guests'      => $guests,
            'rooms'       => $rooms,
            'total_price' => $total_price,
            'room_type'   => $room_type,
        ];

        header("Location: confirmation.php?booking_id=" . $result['booking_id']);
        exit();

    } else {
        $_SESSION['booking_errors'] = ['Failed to create booking. Please try again.'];
        header("Location: bookings.php?hotel_id=" . $hotel_id . "&room=" . $room_type);
        exit();
    }
}
show_form:

if (!isset($_GET['hotel_id']) && !isset($hotel_id)) {
    header("Location: hotels.php");
    exit();
}

if (!isset($hotel_id)) {
    $hotel_id = (int)$_GET['hotel_id'];
}
$hotel = getHotelById($hotel_id);

if (!isset($room_type)) {
    $room_type = isset($_GET['room']) ? $_GET['room'] : 'deluxe';
}

if (!$hotel) {
    header("Location: hotels.php");
    exit();
}

$user_id         = getCurrentUserId();
$user            = getUserById($user_id);
$base_price = $hotel['price_per_night'];

if ($room_type === 'executive') {
    $price_per_night = $base_price * 1.5;
} elseif ($room_type === 'presidential') {
    $price_per_night = $base_price * 2.0;
} else {
    $price_per_night = $base_price; 
}

if (!isset($booking_errors)) {
    $booking_errors = isset($_SESSION['booking_errors']) ? $_SESSION['booking_errors'] : [];
    unset($_SESSION['booking_errors']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Hotel | Aurum</title>
    <link rel="stylesheet" href="../assets/css/style.css">    
    <style>
        .booking-page {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 40px 10%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .booking-form-container { padding: 30px; background: white; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .booking-summary { padding: 30px; background: #f8f6f2; border-radius: 8px; }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .input-group input, .input-group select, .input-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .booking-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .price-line, .total-line { display: flex; justify-content: space-between; padding: 10px 0; }
        .total-line { font-weight: bold; font-size: 18px; border-top: 2px solid #d4af37; margin-top: 10px; }
        .confirm-btn { width: 100%; padding: 15px; background: #111; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; }
        .confirm-btn:hover { background: #d4af37; color: #111; }
    </style>
    <script>
    window.addEventListener('DOMContentLoaded', function() {

        const today = new Date().toISOString().split('T')[0];

        const checkinInput  = document.getElementById('check-in');
        const checkoutInput = document.getElementById('check-out');

        checkinInput.min  = today;
        checkoutInput.min = today;

        checkinInput.addEventListener('change', function() {

            if (checkinInput.value) {

                const nextDay = new Date(checkinInput.value);
                nextDay.setDate(nextDay.getDate() + 1);

                checkoutInput.min = nextDay.toISOString().split('T')[0];

                if (checkoutInput.value && checkoutInput.value <= checkinInput.value) {
                    checkoutInput.value = '';
                    document.getElementById('date-error').style.display = 'none';
                }
            }

            calculateTotal();
        });

        checkoutInput.addEventListener('change', function() {

            const checkinValue  = checkinInput.value;
            const checkoutValue = checkoutInput.value;

            if (checkinValue && checkoutValue <= checkinValue) {
                document.getElementById('date-error').style.display = 'block';
                checkoutInput.value = '';
            } else {
                document.getElementById('date-error').style.display = 'none';
            }

            calculateTotal();
        });

    });

    function calculateTotal() {

        const checkinValue  = document.getElementById('check-in').value;
        const checkoutValue = document.getElementById('check-out').value;
        const pricePerNight = <?php echo $price_per_night; ?>;
        const rooms         = parseFloat(document.getElementById('rooms').value) || 1;

        if (checkinValue && checkoutValue) {

            const checkinDate  = new Date(checkinValue);
            const checkoutDate = new Date(checkoutValue);
            const nights       = Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24));

            if (nights > 0) {

                const subtotal   = nights * pricePerNight * rooms;
                const tax        = subtotal * 0.05;   // 5% tax
                const serviceFee = subtotal * 0.025;  // 2.5% service fee
                const total      = subtotal + tax + serviceFee;

                document.getElementById('subtotal').innerText    = '$' + subtotal.toFixed(2);
                document.getElementById('tax').innerText         = '$' + tax.toFixed(2);
                document.getElementById('service-fee').innerText = '$' + serviceFee.toFixed(2);
                document.getElementById('total').innerText       = '$' + total.toFixed(2);

                document.getElementById('total-price').value = total.toFixed(2);

            } else {
                document.getElementById('date-error').style.display = 'block';
            }
        }
    }
</script>
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

    <section class="booking-banner" style="text-align:center; padding:60px 10%; background: linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945') center/cover; color:white;">
        <h1>Complete Your Reservation</h1>
        <p>You're one step away from your stay</p>
    </section>

    <section class="booking-page">
        <div class="booking-form-container">
            <h2>Guest & Payment Information</h2>

            <?php if (!empty($booking_errors)): ?>
                <div style="background:#ffcdd2; color:#c0392b; padding:15px; border-radius:6px; margin-bottom:20px;">
                    <strong>Please fix the following:</strong>
                    <ul style="margin:8px 0 0 20px;">
                        <?php foreach ($booking_errors as $err): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="bookings.php" method="post">
                <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                <input type="hidden" name="room_type" value="<?php echo htmlspecialchars($room_type); ?>">
                <input type="hidden" id="total-price" name="total_price" value="0">

                <h3>Guest Information</h3>
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                </div>

                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                </div>

                <h3 style="margin-top: 30px;">Booking Details</h3>
                <div class="booking-row">
                    <div class="input-group">
                        <label>Check In</label>
                        <input type="date" id="check-in" name="check_in" required>
                    </div>
                    <div class="input-group">
                        <label>Check Out</label>
                        <input type="date" id="check-out" name="check_out" required>
                    </div>
                </div>
                <div id="date-error" style="display:none; color:#e74c3c; font-weight:bold; margin-bottom:10px;">⚠ Check-out date must be after check-in date.</div>

                <div class="booking-row">
                    <div class="input-group">
                        <label>Guests</label>
                        <select name="guests" required>
                            <option value="1">1 Guest</option>
                            <option value="2">2 Guests</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4+ Guests</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Rooms</label>
                        <select id="rooms" name="rooms" required onchange="calculateTotal()">
                            <option value="1">1 Room</option>
                            <option value="2">2 Rooms</option>
                            <option value="3">3+ Rooms</option>
                        </select>
                    </div>
                </div>

                <h3 style="margin-top: 30px;">Payment Details</h3>
                <div class="input-group">
                    <label>Cardholder Name</label>
                    <input type="text" name="cardholder" required>
                </div>

                <div class="input-group">
                    <label>Card Number</label>
                    <input type="text" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                </div>

                <div class="booking-row">
                    <div class="input-group">
                        <label>Expiry (MM/YY)</label>
                        <input type="text" name="expiry" placeholder="12/26" maxlength="5" required>
                    </div>
                    <div class="input-group">
                        <label>CVV</label>
                        <input type="text" name="cvv" placeholder="123" maxlength="3" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Special Requests</label>
                    <textarea name="special_requests" rows="3" placeholder="Any special requests?"></textarea>
                </div>

                <button type="submit" class="confirm-btn">Confirm Reservation</button>
            </form>
        </div>

        <div class="booking-summary">
            <?php
            $hotel_images = [
                1 => '../assets/images/hotel-banner.jpg',
                2 => '94190040.jpg',
                3 => '546401319.jpg',
                4 => '411834440.jpg',
            ];
            $summary_img = $hotel_images[$hotel_id] ?? '../assets/images/hotel-banner.jpg';
            ?>
            <img src="<?php echo $summary_img; ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" style="width:100%; border-radius:8px; margin-bottom:20px;">

            <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
            <p><?php echo htmlspecialchars(ucfirst($room_type)); ?> Room</p>
            <hr>

            <div class="price-line">
                <span>Nightly Rate</span>
                <span>$<?php echo number_format($price_per_night, 2); ?></span>
            </div>

            <div class="price-line">
                <span>Subtotal</span>
                <span id="subtotal">$0.00</span>
            </div>

            <div class="price-line">
                <span>Taxes (5%)</span>
                <span id="tax">$0.00</span>
            </div>

            <div class="price-line">
                <span>Service Fee (2.5%)</span>
                <span id="service-fee">$0.00</span>
            </div>

            <hr>
            <div class="total-line">
                <span>Total</span>
                <span id="total">$0.00</span>
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

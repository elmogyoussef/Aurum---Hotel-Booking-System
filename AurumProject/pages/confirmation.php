<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if (!isset($_GET['booking_id'])) {
    header("Location: hotels.php");
    exit();
}
$booking_id = (int)$_GET['booking_id'];
$user_id = getCurrentUserId();
global $conn;
if (!$conn) die("Connection failed: " . mysqli_connect_error());
$stmt = $conn->prepare("SELECT b.*, h.name, h.location FROM bookings b JOIN hotels h ON b.hotel_id = h.id WHERE b.id = ? AND b.user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();
if (!$booking) {
    header("Location: mybookings.php");
    exit();
}
$room_type = isset($_SESSION['last_booking']['room_type']) ? $_SESSION['last_booking']['room_type'] : 'Deluxe';
unset($_SESSION['last_booking']);
$date1 = new DateTime($booking['check_in']);
$date2 = new DateTime($booking['check_out']);
$nights = $date2->diff($date1)->days;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation | Aurum</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .confirmation-header {
            margin-bottom: 30px;
        }
        .success-icon {
            font-size: 60px;
            color: #27ae60;
            margin-bottom: 15px;
        }
        .confirmation-details {
            background: #f8f6f2;
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #ddd;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        .confirmation-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #111;
            color: white;
        }
        .btn-primary:hover {
            background: #d4af37;
            color: #111;
        }
        .btn-secondary {
            background: #ddd;
            color: #111;
        }
        .btn-secondary:hover {
            background: #bbb;
        }
        .booking-id {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 18px;
            color: #1976d2;
            margin-bottom: 20px;
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

    <div class="confirmation-container">
        <div class="confirmation-header">
            <div class="success-icon">✓</div>
            <h1>Booking Confirmed!</h1>
            <p>Your reservation has been successfully completed</p>
        </div>

        <div class="booking-id">
            Booking ID: #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>
        </div>

        <div class="confirmation-details">
            <h2 style="text-align: center; margin-bottom: 20px;">Reservation Details</h2>

            <div class="detail-row">
                <span class="detail-label">Hotel Name</span>
                <span><?php echo htmlspecialchars($booking['name']); ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Location</span>
                <span><?php echo htmlspecialchars($booking['location']); ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Room Type</span>
                <span><?php echo htmlspecialchars(ucfirst($room_type)); ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Check-In</span>
                <span><?php echo date('l, M d, Y', strtotime($booking['check_in'])); ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Check-Out</span>
                <span><?php echo date('l, M d, Y', strtotime($booking['check_out'])); ?></span>
            </div>
                            <?php
                if ($nights > 1) {
                    $night_label = 'Nights';
                } else {
                    $night_label = 'Night';
                }
                ?>
            <div class="detail-row">
                <span class="detail-label">Duration</span>
                <span><?php echo $nights . ' ' . $night_label; ?></span>
                </div>

            <div class="detail-row">
                <span class="detail-label">Number of Guests</span>
                <span><?php echo $booking['guests']; ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Number of Rooms</span>
                <span><?php echo $booking['rooms']; ?></span>
            </div>

            <div class="detail-row" style="border-bottom: 2px solid #d4af37; padding-bottom: 15px;">
                <span class="detail-label" style="font-size: 18px;">Total Amount</span>
                <span style="font-size: 18px; font-weight: bold; color: #d4af37;">$<?php echo number_format($booking['total_price'], 2); ?></span>
            </div>

            <div class="detail-row" style="border-bottom: none; margin-top: 15px;">
                <span class="detail-label">Booking Status</span>
                <span style="color: #27ae60; font-weight: bold;">✓ CONFIRMED</span>
            </div>
        </div>

        <p style="color: #666; margin: 20px 0;">
            A confirmation email has been sent to your registered email address. <br>
            You can view and manage your bookings from your account dashboard.
        </p>

        <div class="confirmation-buttons">
            <a href="mybookings.php" class="btn btn-primary">View My Bookings</a>
            <a href="hotels.php" class="btn btn-secondary">Browse More Hotels</a>
        </div>
    </div>
    <footer class="main-footer">
        <hr>
        <div class="footer-section">
            <h4>Contact Us</h4>
            <p>📞 Phone: +20 123 456 7890</p>
            <p>📧 Email: support@aurum.com</p>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Aurum Hotel Bookings. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

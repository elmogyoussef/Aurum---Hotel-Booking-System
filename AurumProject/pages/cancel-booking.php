<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if (!isset($_GET['booking_id'])) {
    header("Location: mybookings.php");
    exit();
}
$booking_id = (int)$_GET['booking_id'];
$user_id = getCurrentUserId();
global $conn;
if (!$conn) die("Connection failed: " . mysqli_connect_error());
$stmt = $conn->prepare("SELECT id, status FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();
if (!$booking) {
    $_SESSION['error'] = "Booking not found";
    header("Location: mybookings.php");
    exit();
}
if ($booking['status'] === 'cancelled') {
    $_SESSION['error'] = "This booking is already cancelled";
    header("Location: mybookings.php");
    exit();
}
$stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
if ($stmt->execute()) {
    $_SESSION['cancel_message'] = "Your booking has been successfully cancelled. A refund will be processed within 5-7 business days.";
} else {
    $_SESSION['error'] = "Failed to cancel booking: " . $stmt->error;
}
$stmt->close();
header("Location: mybookings.php");
exit();
?>
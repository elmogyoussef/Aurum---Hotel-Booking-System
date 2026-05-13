<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_booking');
$conn = mysqli_connect("localhost", "root", "", "hotel_booking");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");
define('SITE_URL', 'http://localhost/Project/');
?>

<?php
require_once __DIR__ . '/config.php';
function registerUser($username, $email, $password, $phone, $full_name) {
    global $conn;
    
    if (empty($username) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Username or email already exists'];
    }
    
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, phone, full_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $hashed_password, $phone, $full_name);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Registration successful'];
    } else {
        return ['success' => false, 'message' => 'Registration failed: ' . $conn->error];
    }
}

function loginUser($username, $password) {
    global $conn;
    
    if (empty($username) || empty($password)) {
        return ['success' => false, 'message' => 'Username and password required'];
    }
    
    $stmt = $conn->prepare("SELECT id, username, password, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['loggedIn'] = true;
            
            return ['success' => true, 'message' => 'Login successful'];
        } else {
            return ['success' => false, 'message' => 'Invalid password'];
        }
    } else {
        return ['success' => false, 'message' => 'User not found'];
    }
}

function getUserById($user_id) {
    global $conn;

    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) DEFAULT NULL");
    
    $stmt = $conn->prepare("SELECT id, username, email, phone, full_name, profile_picture, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function updateUserProfile($user_id, $full_name, $email, $phone, $new_password = '', $profile_picture = '') {
    global $conn;

    $fields = "full_name = ?, email = ?, phone = ?";
    $params = [$full_name, $email, $phone];
    $types  = "sss";

    if (!empty($new_password)) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $fields .= ", password = ?";
        $params[] = $hashed;
        $types   .= "s";
    }

    if (!empty($profile_picture)) {
        $fields .= ", profile_picture = ?";
        $params[] = $profile_picture;
        $types   .= "s";
    }

    $params[] = $user_id;
    $types   .= "i";

    $stmt = $conn->prepare("UPDATE users SET $fields WHERE id = ?");
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Profile updated successfully'];
    } else {
        return ['success' => false, 'message' => 'Update failed'];
    }
}

function getAllHotels() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, name, location, price_per_night, rating, reviews_count, description FROM hotels ORDER BY rating DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getHotelById($hotel_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function searchHotels($location = '', $min_price = 0, $max_price = 10000) {
    global $conn;
    
    $query = "SELECT id, name, location, price_per_night, rating, reviews_count, description FROM hotels WHERE 1=1";
    
    if (!empty($location)) {
        $query .= " AND location LIKE ?";
    }
    
    $query .= " AND price_per_night BETWEEN ? AND ? ORDER BY rating DESC";
    
    $min_price = (float)$min_price;
    $max_price = (float)$max_price;

    if (!empty($location)) {
        $location_param = "%{$location}%";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sdd", $location_param, $min_price, $max_price);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("dd", $min_price, $max_price);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}
function createBooking($user_id, $hotel_id, $check_in, $check_out, $guests, $rooms, $total_price) {
    global $conn;
    
    if (empty($check_in) || empty($check_out) || $total_price <= 0) {
        return ['success' => false, 'message' => 'Invalid booking details'];
    }
    
    $status = 'confirmed';
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, hotel_id, check_in, check_out, guests, rooms, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissiids", $user_id, $hotel_id, $check_in, $check_out, $guests, $rooms, $total_price, $status);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Booking created successfully', 'booking_id' => $conn->insert_id];
    } else {
        return ['success' => false, 'message' => 'Booking failed'];
    }
}
function getUserBookings($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT b.id, b.hotel_id, b.check_in, b.check_out, b.total_price, b.status, b.guests, b.rooms,
               h.name, h.location, h.price_per_night, h.image_url
        FROM bookings b
        JOIN hotels h ON b.hotel_id = h.id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getAllBookings() {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT b.id, b.check_in, b.check_out, b.total_price, b.status, 
               u.username, h.name
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN hotels h ON b.hotel_id = h.id
        ORDER BY b.created_at DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function cancelBooking($booking_id, $user_id) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Booking cancelled successfully'];
    } else {
        return ['success' => false, 'message' => 'Cancellation failed'];
    }
}

function saveContactMessage($name, $email, $subject, $message) {
    global $conn;
    
    if (empty($name) || empty($email) || empty($message)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $subject, $message);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Message sent successfully'];
    } else {
        return ['success' => false, 'message' => 'Message failed to send'];
    }
}
?>
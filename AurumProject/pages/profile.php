<?php

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

$user_id = getCurrentUserId();
global $conn;

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $full_name        = ($_POST['full_name']        ?? '');
    $email            = ($_POST['email']            ?? '');
    $phone            = ($_POST['phone']            ?? '');
    $new_password     = ($_POST['new_password']     ?? '');
    $confirm_password = ($_POST['confirm_password'] ?? '');

    
    if (strlen($full_name) < 3) {
        $errors[] = "Full name must be at least 3 characters.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!empty($phone) && strlen($phone) < 10) {
        $errors[] = "Phone number must be at least 10 digits.";
    }

    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "This email is already used by another account.";
    }
    $stmt->close();

    $profile_picture = '';

    if (!empty($_FILES['profile_picture']['name'])) {

        $file     = $_FILES['profile_picture'];
        $allowed  = ['image/jpeg', 'image/png'];
        $max_size = 2 * 1024 * 1024; 

        if (!in_array($file['type'], $allowed)) {
            $errors[] = "Profile picture must be a JPG or PNG image.";
        } elseif ($file['size'] > $max_size) {
            $errors[] = "Profile picture must be smaller than 2 MB.";
        } else {
           
        $upload_dir = __DIR__ . '/uploads/profiles/';

            $folder_exists = is_dir($upload_dir);

            if ($folder_exists === false) {
                mkdir($upload_dir, 0755, true);
            }

            $ext          = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
            $destination  = $upload_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $profile_picture = 'uploads/profiles/' . $new_filename;
            } else {
                $errors[] = "Failed to upload the picture. Please try again.";
            }
        }
    }

    if (empty($errors)) {
        $result = updateUserProfile($user_id, $full_name, $email, $phone, $new_password, $profile_picture);
        if ($result['success']) {
            $success = "Profile updated successfully!";
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}

$user = getUserById($user_id);

if (!$user) {
    header("Location: logout.php");
    exit();
}

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$booking_count = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$show_edit = !empty($errors);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | Aurum</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .profile-container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .profile-grid { display: grid; grid-template-columns: 300px 1fr; gap: 30px; }

        .profile-sidebar {
            background: white; padding: 30px; border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; height: fit-content;
        }
        .profile-avatar {
            width: 120px; height: 120px; border-radius: 50%; background: #d4af37;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; font-size: 50px; color: white; overflow: hidden;
        }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-name  { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        .profile-email { color: #666; font-size: 14px; margin-bottom: 20px; }
        .profile-stats {
            display: grid; grid-template-columns: 1fr 1fr; gap: 15px;
            margin-top: 25px; padding-top: 25px; border-top: 1px solid #ddd;
        }
        .stat-item   { text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #d4af37; }
        .stat-label  { font-size: 12px; color: #666; margin-top: 5px; }
        .edit-btn {
            width: 100%; padding: 12px; background: #111; color: white;
            border: none; border-radius: 4px; margin-top: 20px; font-weight: bold; cursor: pointer;
        }
        .edit-btn:hover { background: #d4af37; color: #111; }

        .profile-content {
            background: white; padding: 30px; border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .info-section { margin-bottom: 30px; }
        .info-section h2 { font-size: 20px; margin-bottom: 20px; border-bottom: 2px solid #d4af37; padding-bottom: 10px; }
        .info-grid    { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .info-item    { display: flex; flex-direction: column; }
        .info-label   { font-size: 12px; color: #999; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; }
        .info-value   { font-size: 16px; color: #111; }
        .action-buttons { display: flex; gap: 15px; margin-top: 30px; }
        .btn { padding: 12px 25px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-weight: bold; }
        .btn-primary  { background: #111; color: white; }
        .btn-primary:hover  { background: #d4af37; color: #111; }
        .btn-secondary { background: #ddd; color: #111; }
        .btn-secondary:hover { background: #bbb; }

        .alert-success { background: #c8e6c9; color: #27ae60; padding: 15px; border-radius: 6px; margin-top: 20px; font-weight: bold; }
        .alert-error   { background: #ffcdd2; color: #e74c3c; padding: 15px; border-radius: 6px; margin-top: 20px; }
        .alert-error p { margin: 4px 0; }

        .edit-form-box {
            background: white; padding: 30px; border-radius: 8px;
            margin-top: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input {
            width: 100%; padding: 10px; border: 1px solid #ddd;
            border-radius: 4px; box-sizing: border-box; font-size: 15px;
        }
        .form-group small { color: #999; font-size: 12px; display: block; margin-top: 4px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .picture-preview {
            width: 80px; height: 80px; border-radius: 50%; object-fit: cover;
            border: 3px solid #d4af37; margin-top: 10px; display: none;
        }
        .form-actions { display: flex; gap: 15px; margin-top: 10px; }
        .section-divider { border-top: 1px solid #eee; padding-top: 20px; margin-top: 10px; }

        @media (max-width: 768px) {
            .profile-grid { grid-template-columns: 1fr; }
            .form-row      { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
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

    <div class="profile-container">
        <h1>My Profile</h1>

        <div class="profile-grid">

            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <?php if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/' . $user['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                    <?php else: ?>
                        👤
                    <?php endif; ?>
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>

                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $booking_count; ?></div>
                        <div class="stat-label">Bookings</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">⭐</div>
                        <div class="stat-label">Member</div>
                    </div>
                </div>

                <button class="edit-btn" onclick="toggleEditForm()">Edit Profile</button>
            </div>

            <div class="profile-content">
                <div class="info-section">
                    <h2>Account Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Full Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Member Since</span>
                            <span class="info-value">
                                <?php echo !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="info-section">
                    <h2>Quick Links</h2>
                    <div class="action-buttons">
                        <a href="mybookings.php" class="btn btn-primary">📅 My Bookings</a>
                        <a href="hotels.php"     class="btn btn-secondary">🏨 Browse Hotels</a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert-success">✓ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <?php foreach ($errors as $err): ?>
                    <p>✗ <?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div id="editForm" class="edit-form-box" style="display: <?php echo $show_edit ? 'block' : 'none'; ?>;">
            <h2>Edit Profile</h2>
            <form action="profile.php" method="post" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Profile Picture <span style="color:#999; font-weight:normal;">(optional)</span></label>
                    <input type="file" name="profile_picture" accept="image/*" onchange="previewPicture(this)">
                    <small>JPG, PNG, GIF or WEBP — max 2 MB. Leave empty to keep your current picture.</small>
                    <img id="picturePreview" class="picture-preview" alt="Preview">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name"
                               value="<?php echo htmlspecialchars($user['full_name']); ?>"
                               placeholder="Your full name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email"
                               value="<?php echo htmlspecialchars($user['email']); ?>"
                               placeholder="your@email.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Phone <span style="color:#999; font-weight:normal;">(optional)</span></label>
                    <input type="tel" name="phone"
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                           placeholder="e.g. 01001234567">
                </div>

                <div class="section-divider">
                    <p style="font-weight:bold; margin-bottom: 15px;">
                        Change Password
                        <span style="color:#999; font-weight:normal;">(optional — leave blank to keep your current password)</span>
                    </p>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="At least 6 characters">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Repeat new password">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="toggleEditForm()">Cancel</button>
                </div>

            </form>
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
                <li><a href="about-contact.php">About / Contact</a></li>
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

    <script>
        function toggleEditForm() {
            var form = document.getElementById('editForm');
            form.style.display = (form.style.display === 'none') ? 'block' : 'none';
        }

        function previewPicture(input) {
            var preview = document.getElementById('picturePreview');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
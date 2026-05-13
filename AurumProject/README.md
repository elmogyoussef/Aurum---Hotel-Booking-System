# Aurum – Hotel Booking System

A PHP-based hotel booking web application that allows users to browse hotels, make reservations, manage bookings, and handle their profiles.

## Features

- Browse hotels and view detailed room information
- User registration, login, and profile management (including optional profile photo upload)
- Book hotel rooms and view booking confirmation
- View and cancel existing bookings
- Admin can manage hotel listings and bookings

## Project Structure

```
AurumProject/
├── index.php               # Home / landing page
├── includes/
│   ├── config.php          # DB credentials (not committed – see config.example.php)
│   ├── config.example.php  # Template for config.php
│   ├── auth.php            # Session & authentication helpers
│   └── functions.php       # Shared utility functions
├── pages/
│   ├── hotels.php          # Hotel listing
│   ├── hotel-details.php   # Single hotel view & booking form
│   ├── about-contact.php   # About / contact page
│   ├── login.php           # User login
│   ├── logout.php          # Session logout
│   ├── signup.php          # User registration
│   ├── profile.php         # Edit profile
│   ├── mybookings.php      # User's booking history
│   ├── bookings.php        # Admin bookings management
│   ├── confirmation.php    # Booking confirmation
│   └── cancel-booking.php  # Cancel a booking
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   ├── style2.css
│   │   └── style3.css
│   └── images/             # Static hotel images
├── uploads/
│   └── profiles/           # User-uploaded profile photos (git-ignored)
└── .gitignore
```

## Setup

### Requirements
- PHP 7.4+
- MySQL / MariaDB
- A local server like XAMPP, WAMP, or Laragon

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/AurumProject.git
   ```

2. **Place it in your server's web root**  
   e.g. `C:/xampp/htdocs/AurumProject`

3. **Create the database**  
   Import the SQL schema (ask your team for `hotel_booking.sql`) into MySQL:
   ```bash
   mysql -u root -p hotel_booking < hotel_booking.sql
   ```

4. **Configure the database connection**
   ```bash
   cp includes/config.example.php includes/config.php
   ```
   Then open `includes/config.php` and fill in your credentials.

5. **Visit the site**  
   Open `http://localhost/AurumProject/` in your browser.

## Notes

- `includes/config.php` is git-ignored — never commit real credentials.
- User-uploaded profile photos land in `uploads/profiles/` which is also git-ignored.

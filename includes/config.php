<?php
// === APP CONFIG ===
define('APP_NAME', 'Gupta Sanitary & Hardware Store');
define('BASE_URL', 'http://localhost/gupta-hardware-store/'); // change this when live

// === DB CONFIG (optional, you can move from db.php) ===
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gupta_store');

// === SECURITY CONFIG ===
define('SESSION_TIMEOUT', 1800); // 30 minutes

// === IMAGE SETTINGS ===
define('MAX_IMAGE_SIZE_MB', 2);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

<?php
// app/config/config.php
define('APP_NAME', 'Shop Online');
define('BASE_URL', 'http://localhost/shop/public');

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'shop_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Upload configuration (Buoi 3)
define('UPLOAD_MAX_SIZE', 2 * 1024 * 1024); // 2MB
define('UPLOAD_ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'webp']);
define('UPLOAD_MIN_SIDE', 200);
define('UPLOAD_MAX_SIDE', 3000);
define('IMAGE_MAIN_WIDTH', 800);
define('IMAGE_THUMB_WIDTH', 300);
define('UPLOAD_MAX_IMAGES', 8);

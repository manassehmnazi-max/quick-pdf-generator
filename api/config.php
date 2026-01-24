<?php
// config.php

// ---------------------
// Database
// ---------------------
if (!defined('DB_PATH')) {
    define('DB_PATH', __DIR__ . '/../database/app.db');
}

// ---------------------
// Rate limiting
// ---------------------
if (!defined('RATE_LIMIT')) {
    define('RATE_LIMIT', 5);
}

// ---------------------
// JWT Secret
// ---------------------
if (!defined('TOKEN_SECRET')) {
    $secret = getenv('JWT_SECRET');

    if (!$secret || !is_string($secret) || strlen($secret) < 32) {
        die(json_encode([
            'error' => 'JWT_SECRET missing or too short'
        ]));
    }

    define('TOKEN_SECRET', $secret);
}

// ---------------------
// Error handling
// ---------------------
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// ---------------------
// Database connection
// ---------------------
try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die(json_encode(['error' => 'Database connection failed']));
}

// ---------------------
// PDF Storage Path
// ---------------------
if (!defined('PDF_PATH')) {
    define('PDF_PATH', __DIR__ . '/../storage/pdfs/');
    if (!file_exists(PDF_PATH)) {
        mkdir(PDF_PATH, 0750, true);
    }
}
?>

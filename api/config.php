<?php

// Only define DB_PATH if it doesn't exist
if (!defined('DB_PATH')) {
    define('DB_PATH', __DIR__ . '/../database/app.db');
}

if (!defined('RATE_LIMIT')) {
    define('RATE_LIMIT', 5);
}

if (!defined('TOKEN_SECRET')) {
    define('TOKEN_SECRET', getenv('JWT_SECRET'));
}

// Database connection
try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die(json_encode(['error' => 'Database connection failed']));
}

?>
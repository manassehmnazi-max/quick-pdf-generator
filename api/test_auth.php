<?php
header('Content-Type: application/json');

require_once __DIR__ . '/utils.php';

// Only for quick testing: generate a JWT for test user
$userId = 1; // match your temporary auth
$token = create_jwt($userId);

echo json_encode([
    'message' => 'Test JWT generated',
    'token' => $token,
    'expires_in' => 3600
]);
?>
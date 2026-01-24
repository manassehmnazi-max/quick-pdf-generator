<?php
header('Content-Type: application/json');

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/stripe.php';

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Ensure Authorization header is read even if Apache strips it
if (!isset($_SERVER['HTTP_AUTHORIZATION']) && function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $headers['Authorization'];
    }
}

// Check JWT
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['error' => 'JWT missing']);
    exit;
}

$token = $matches[1];
$userId = validate_jwt($token);
if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

// Get amount from POST (optional; default $5)
$amount = floatval($_POST['amount'] ?? 5.00);
if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

// Create Stripe payment intent for this user
$result = create_payment_intent($amount, $userId);

// Return JSON response
if (isset($result['error'])) {
    http_response_code(400);
}
echo json_encode($result);

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

// Handle Authorization header (Render / Apache)
if (!isset($_SERVER['HTTP_AUTHORIZATION']) && function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $headers['Authorization'];
    }
}

// Validate JWT
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['error' => 'JWT missing']);
    exit;
}

$userId = validate_jwt($matches[1]);
if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

// Amount
$amount = floatval($_POST['amount'] ?? 0);
if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

// Create Stripe Payment Intent
$result = create_payment_intent($amount, $userId);

echo json_encode($result);

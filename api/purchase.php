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

// Check JWT
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$token = $matches[1];
$userId = validate_jwt($token);
if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

// Get amount from POST
$amount = floatval($_POST['amount'] ?? 0);
if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

// Create payment intent
$result = create_payment_intent($amount);
echo json_encode($result);

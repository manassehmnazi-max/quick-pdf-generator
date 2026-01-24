<?php
header('Content-Type: application/json');

require_once __DIR__ . '/utils.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? null;

if (!$action) {
    http_response_code(400);
    echo json_encode(['error' => 'Action is required']);
    exit;
}

if ($action === 'login') {
    $username = sanitize($_POST['username'] ?? '');
    $password = sanitize($_POST['password'] ?? '');

    if (!$username || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password required']);
        exit;
    }

    /**
     * TEMPORARY AUTH (for testing)
     * Replace with DB check later
     */
    if ($username !== 'testuser' || $password !== '123456') {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }

    // User ID would normally come from DB
    $userId = 1;

    $token = create_jwt($userId);

    echo json_encode([
        'token' => $token,
        'expires_in' => 3600
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);

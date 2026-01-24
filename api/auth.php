<?php
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) {
    echo json_encode([
        'error' => 'No action provided'
    ]);
    exit;
}

if ($action === 'login') {
    echo json_encode([
        'message' => 'Login endpoint reached'
    ]);
    exit;
}

if ($action === 'register') {
    echo json_encode([
        'message' => 'Register endpoint reached'
    ]);
    exit;
}

echo json_encode([
    'error' => 'Invalid action'
]);

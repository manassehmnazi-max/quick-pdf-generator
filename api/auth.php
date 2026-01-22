<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Load all functions
require_once __DIR__ . '/utils.php';

// Handle POST actions
$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $username = sanitize($_POST['username'] ?? '');
    $password = sanitize($_POST['password'] ?? '');

    $db = get_db();

    // Create table if it doesn't exist (for first run)
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE,
            password TEXT,
            credits INTEGER DEFAULT 5
        )
    ");

    // Check user
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }

    $token = create_jwt($user['id']);
    echo json_encode([
        'token' => $token,
        'credits' => $user['credits']
    ]);
    exit;
}

if ($action === 'register') {
    $username = sanitize($_POST['username'] ?? '');
    $password = sanitize($_POST['password'] ?? '');
    $db = get_db();

    // Create table if it doesn't exist
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE,
            password TEXT,
            credits INTEGER DEFAULT 5
        )
    ");

    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hash]);
        echo json_encode(['success' => 'User registered']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'User already exists']);
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);

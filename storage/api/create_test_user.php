<?php
header('Content-Type: application/json');
require __DIR__ . '/config.php';

try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Hash password
    $passwordHash = password_hash('123456', PASSWORD_DEFAULT);

    // Insert test user
    $stmt = $db->prepare("INSERT OR IGNORE INTO users (username, password, credits) VALUES (?, ?, ?)");
    $stmt->execute(['testuser', $passwordHash, 5]);

    echo json_encode(['success' => 'Test user created']);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

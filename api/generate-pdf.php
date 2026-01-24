<?php
header('Content-Type: application/json');
require_once __DIR__ . '/utils.php';

// Allow POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JWT from Authorization header
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['error' => 'JWT missing']);
    exit;
}

$user_id = validate_jwt($matches[1]);
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

// Get content
$content = sanitize($_POST['content'] ?? '');
if (!$content) {
    http_response_code(400);
    echo json_encode(['error' => 'No content provided']);
    exit;
}

$db = get_db();

// Get user payment status
$stmt = $db->prepare("SELECT has_paid FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

// FREE USER LIMIT CHECK
if ((int)$user['has_paid'] === 0) {

    // Count PDFs generated today
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM pdfs
        WHERE user_id = ?
        AND date(created_at) = date('now')
    ");
    $stmt->execute([$user_id]);
    $todayCount = (int)$stmt->fetchColumn();

    if ($todayCount >= 3) {
        http_response_code(429); // Too Many Requests
        echo json_encode([
            'error' => 'Daily free limit reached',
            'limit' => 3,
            'message' => 'Upgrade to generate unlimited PDFs'
        ]);
        exit;
    }
}

// Generate PDF
$filename = generate_pdf($content);

// Store PDF record
$stmt = $db->prepare("INSERT INTO pdfs (user_id, filename) VALUES (?, ?)");
$stmt->execute([$user_id, $filename]);

echo json_encode([
    'success' => 'PDF generated',
    'file' => "/storage/pdfs/$filename",
    'paid_user' => (bool)$user['has_paid']
]);

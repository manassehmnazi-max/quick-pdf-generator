<?php
header('Content-Type: application/json');
require_once __DIR__ . '/utils.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get token from Authorization header (Bearer token)
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: JWT missing']);
    exit;
}
$token = $matches[1];

// Validate JWT
$user_id = validate_jwt($token);
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: Invalid token']);
    exit;
}

// Get content for PDF
$content = sanitize($_POST['content'] ?? '');
if (!$content) {
    http_response_code(400);
    echo json_encode(['error' => 'No content provided']);
    exit;
}

$db = get_db();

// Check user credits / payment
$stmt = $db->prepare("SELECT credits, has_paid FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

// Require payment or credits
if (!$user['has_paid'] && $user['credits'] < 1) {
    http_response_code(402); // Payment Required
    echo json_encode(['error' => 'Insufficient credits or payment required']);
    exit;
}

// Generate PDF
$filename = generate_pdf($content);

// Deduct 1 credit if user hasn’t fully paid
if (!$user['has_paid']) {
    $stmt = $db->prepare("UPDATE users SET credits = credits - 1 WHERE id = ?");
    $stmt->execute([$user_id]);
    $remainingCredits = $user['credits'] - 1;
} else {
    $remainingCredits = $user['credits'];
}

echo json_encode([
    'success' => 'PDF generated',
    'file' => "/storage/pdfs/$filename",
    'remaining_credits' => $remainingCredits
]);

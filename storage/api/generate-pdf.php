<?php
header('Content-Type: application/json');
require_once __DIR__ . '/utils.php';

// Get token from POST
$token = $_POST['token'] ?? '';
$user_id = validate_jwt($token);

if (!$user_id) {
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

// Get content for PDF
$content = sanitize($_POST['content'] ?? '');
if (!$content) {
    echo json_encode(['error' => 'No content provided']);
    exit;
}

$db = get_db();

// Check user credits
$stmt = $db->prepare("SELECT credits FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['credits'] < 1) {
    echo json_encode(['error' => 'Insufficient credits']);
    exit;
}

// Generate PDF
$filename = generate_pdf($content);

// Deduct 1 credit
$stmt = $db->prepare("UPDATE users SET credits = credits - 1 WHERE id = ?");
$stmt->execute([$user_id]);

echo json_encode([
    'success' => 'PDF generated',
    'file' => "/storage/pdfs/$filename",
    'remaining_credits' => $user['credits'] - 1
]);
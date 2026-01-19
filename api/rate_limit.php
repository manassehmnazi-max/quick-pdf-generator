<?php
// rate_limit.php

require 'config.php';

function check_rate_limit($user_id) {
    $db = get_db();
    $hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));
    $stmt = $db->prepare("SELECT COUNT(*) FROM pdfs WHERE user_id = ? AND created_at >= ?");
    $stmt->execute([$user_id, $hour_ago]);
    $count = $stmt->fetchColumn();
    if ($count >= RATE_LIMIT) {
        http_response_code(429);
        echo json_encode(['error' => 'Rate limit exceeded. Try again later.']);
        exit;
    }
}

?>
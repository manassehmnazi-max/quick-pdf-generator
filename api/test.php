<?php
header('Content-Type: application/json');

echo json_encode([
    'status' => 'ok',
    'message' => 'API test route is working',
    'time' => date('Y-m-d H:i:s')
]);

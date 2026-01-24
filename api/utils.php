<?php
// utils.php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// ---------------------
// SQLite connection
// ---------------------
function get_db() {
    global $db;
    return $db;
}

// ---------------------
// PDF Generation
// ---------------------
function generate_pdf($content) {
    require_once __DIR__ . '/../vendor/setasign/fpdf/fpdf.php';

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->MultiCell(0,10,$content);

    $filename = uniqid() . '.pdf';
    $fullPath = PDF_PATH . $filename;
    $pdf->Output('F', $fullPath);

    return $filename;
}

// ---------------------
// JWT Functions
// ---------------------
function create_jwt($user_id) {
    $payload = [
        'iss' => 'https://quick-pdf-generator.onrender.com',
        'iat' => time(),
        'exp' => time() + 3600, // 1 hour
        'user_id' => $user_id
    ];
    return JWT::encode($payload, TOKEN_SECRET, 'HS256');
}

function validate_jwt($token) {
    try {
        $decoded = JWT::decode($token, new Key(TOKEN_SECRET, 'HS256'));
        return $decoded->user_id;
    } catch (Exception $e) {
        return false;
    }
}

// ---------------------
// Authorization Middleware
// ---------------------
function require_auth() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);

    $user_id = validate_jwt($token);

    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    return $user_id;
}

// ---------------------
// Sanitize Input
// ---------------------
function sanitize($str) {
    return htmlspecialchars(trim($str));
}
?>

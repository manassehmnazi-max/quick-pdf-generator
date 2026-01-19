<?php
// utils.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// SQLite connection
function get_db() {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}

// Generate PDF
function generate_pdf($content) {
    require_once __DIR__ . '/../vendor/setasign/fpdf/fpdf.php';
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->MultiCell(0,10,$content);
    $filename = uniqid() . '.pdf';
    $pdf->Output('F', __DIR__."/../storage/pdfs/$filename");
    return $filename;
}

// JWT functions
function create_jwt($user_id) {
    $payload = [
        'iss' => 'localhost',
        'iat' => time(),
        'exp' => time() + 3600,
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

// Sanitize input
function sanitize($str) {
    return htmlspecialchars(trim($str));
}

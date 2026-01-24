<?php
// utils.php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * =========================
 * ENV + CONSTANT VALIDATION
 * =========================
 */

// Validate DB_PATH
if (!defined('DB_PATH') || !DB_PATH) {
    throw new Exception('DB_PATH is not defined');
}

// Validate JWT secret from environment
$jwtSecret = getenv('JWT_SECRET');

if (!$jwtSecret || !is_string($jwtSecret) || strlen($jwtSecret) < 32) {
    throw new Exception('JWT_SECRET is missing or too short (must be at least 32 characters)');
}

define('TOKEN_SECRET', $jwtSecret);

/**
 * =========================
 * DATABASE
 * =========================
 */

// SQLite connection
function get_db() {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        throw new Exception('Database connection failed');
    }
}

/**
 * =========================
 * PDF GENERATION
 * =========================
 */

function generate_pdf($content) {
    require_once __DIR__ . '/../vendor/setasign/fpdf/fpdf.php';

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->MultiCell(0, 10, $content);

    $filename = uniqid('pdf_', true) . '.pdf';
    $path = __DIR__ . "/../storage/pdfs/$filename";

    $pdf->Output('F', $path);

    return $filename;
}

/**
 * =========================
 * JWT FUNCTIONS
 * =========================
 */

function create_jwt($user_id) {
    $payload = [
        'iss' => 'quick-pdf-generator',
        'iat' => time(),
        'exp' => time() + 3600, // 1 hour
        'user_id' => (int) $user_id
    ];

    return JWT::encode($payload, TOKEN_SECRET, 'HS256');
}

function validate_jwt($token) {
    try {
        $decoded = JWT::decode($token, new Key(TOKEN_SECRET, 'HS256'));
        return $decoded->user_id ?? false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * =========================
 * HELPERS
 * =========================
 */

function sanitize($str) {
    return htmlspecialchars(trim((string) $str), ENT_QUOTES, 'UTF-8');
}

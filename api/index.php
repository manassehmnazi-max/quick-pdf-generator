<?php
// index.php
// Simple router
$request = $_SERVER['REQUEST_URI'];

if (strpos($request, '/generate-pdf') !== false) {
    require 'generate-pdf.php';
} elseif (strpos($request, '/auth') !== false) {
    require 'auth.php';
} else {
    echo json_encode(['message' => 'Welcome to PDF API']);
}

?>
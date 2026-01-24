<?php
require_once __DIR__ . '/../vendor/autoload.php';

\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
$endpoint_secret = getenv('STRIPE_WEBHOOK_SECRET'); // set this in Render

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);

    if ($event->type === 'payment_intent.succeeded') {
        $paymentIntent = $event->data->object;
        // TODO: Mark user as paid in DB
        // $paymentIntent->metadata->user_id could be used if sent during creation
    }

    http_response_code(200);
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    exit();
}

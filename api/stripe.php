<?php
require_once __DIR__ . '/../vendor/autoload.php';

\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

/**
 * Create a payment intent
 * @param float $amount Amount in USD (e.g., 10.50)
 * @return array
 */
function create_payment_intent($amount) {
    try {
        $intent = \Stripe\PaymentIntent::create([
            'amount' => intval($amount * 100), // Stripe expects cents
            'currency' => 'usd',
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        return [
            'client_secret' => $intent->client_secret,
            'status' => $intent->status,
            'id' => $intent->id
        ];
    } catch (\Stripe\Exception\ApiErrorException $e) {
        return [
            'error' => $e->getMessage()
        ];
    }
}

<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Stripe\Stripe;
use Stripe\PaymentIntent;

Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

/**
 * Create a Stripe payment intent for a user
 * @param float $amount Amount in USD (e.g., 10.50)
 * @param int $user_id ID of the user making the payment
 * @return array
 */
function create_payment_intent($amount, $user_id) {
    // Validate amount
    if (!is_numeric($amount) || $amount <= 0) {
        return ['error' => 'Invalid amount'];
    }

    // Convert dollars to cents (Stripe expects integer)
    $amount_cents = intval($amount * 100);

    try {
        $intent = PaymentIntent::create([
            'amount' => $amount_cents,
            'currency' => 'usd',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'user_id' => $user_id // essential for webhook unlock
            ]
        ]);

        return [
            'client_secret' => $intent->client_secret,
            'status' => $intent->status,
            'id' => $intent->id
        ];

    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Return structured error
        return ['error' => $e->getMessage()];
    }
}

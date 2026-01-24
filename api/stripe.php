<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Stripe\Stripe;
use Stripe\PaymentIntent;

// Set Stripe secret key
$stripeSecret = getenv('STRIPE_SECRET_KEY');
if (!$stripeSecret) {
    die(json_encode(['error' => 'STRIPE_SECRET_KEY not set']));
}

Stripe::setApiKey($stripeSecret);

/**
 * Create a Stripe Payment Intent
 *
 * @param float $amount Amount in USD (e.g. 5.00)
 * @param int $userId
 * @return array
 */
function create_payment_intent(float $amount, int $userId): array
{
    if ($amount <= 0) {
        return ['error' => 'Invalid amount'];
    }

    try {
        $intent = PaymentIntent::create([
            'amount' => (int) round($amount * 100), // cents
            'currency' => 'usd',

            // 🔒 Disable redirect-based payment methods
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],

            // Track who paid
            'metadata' => [
                'user_id' => (string) $userId,
            ],
        ]);

        return [
            'id' => $intent->id,
            'client_secret' => $intent->client_secret,
            'status' => $intent->status,
        ];

    } catch (\Stripe\Exception\ApiErrorException $e) {
        return [
            'error' => $e->getMessage(),
        ];
    }
}

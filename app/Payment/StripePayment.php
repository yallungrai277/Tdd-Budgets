<?php

namespace App\Payment;

use Stripe\Charge;
use Stripe\StripeClient;

class StripePayment implements PaymentContract
{
    protected $stripeClient;

    private $total;

    public function __construct()
    {
        $this->stripeClient = new StripeClient(config('services.stripe.secret_key'));
    }

    public function charge(int $amount, string $cardToken)
    {
        $charge = $this->stripeClient->charges->create([
            'amount' => $amount,
            'currency' => config('services.stripe.currency'),
            'source' => $cardToken,
            'description' => '',
        ]);

        $this->total = $amount;
        return $charge;
    }

    public function totalCharged()
    {
        return $this->total / 100;
    }

    public function totalChargedInCents()
    {
        return $this->total;
    }
}
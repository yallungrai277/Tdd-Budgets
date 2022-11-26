<?php

namespace App\Payment;

use Stripe\Charge;

class FakePayment implements PaymentContract
{
    private $total;

    public function getTestToken()
    {
        return 'valid-token';
    }

    public function totalCharged()
    {
        return $this->total / 100;
    }

    public function totalChargedInCents()
    {
        return $this->total;
    }

    public function charge(int $amount, string $cardToken)
    {
        $this->total = $amount;
    }
}
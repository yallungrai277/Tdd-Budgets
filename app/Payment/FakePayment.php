<?php

namespace App\Payment;

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

    public function charge(int $total, string $token)
    {
        $this->total = $total;
    }
}
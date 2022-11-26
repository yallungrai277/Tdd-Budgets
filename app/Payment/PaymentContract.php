<?php

namespace App\Payment;

interface PaymentContract
{
    public function charge(int $amount, string $cardToken);
}
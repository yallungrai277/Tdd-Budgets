<?php

namespace App\Payment;

interface PaymentContract
{
    public function charge(int $total, string $token);
}
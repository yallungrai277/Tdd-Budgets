<?php

namespace Tests\Unit;

use App\Payment\FakePayment;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FakePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_a_total_charge()
    {
        $payment = new FakePayment();
        $payment->charge(1000, $payment->getTestToken());

        $this->assertEquals(10, $payment->totalCharged());
    }

    public function test_it_has_a_total_charged_in_cents()
    {
        $payment = new FakePayment();
        $payment->charge(1000, $payment->getTestToken());

        $this->assertEquals(1000, $payment->totalChargedInCents());
    }
}
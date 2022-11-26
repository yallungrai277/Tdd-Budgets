<?php

namespace Tests\Unit;

use Tests\TestCase;
use Stripe\StripeClient;
use App\Payment\StripePayment;
use Illuminate\Foundation\Testing\RefreshDatabase;


class StripePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_make_real_charges_with_a_valid_token(): void
    {
        $payment = new StripePayment();
        $stripeClient = new StripeClient(config('services.stripe.secret_key'));

        $token = $stripeClient->tokens->create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => now()->addYear()->format('Y'),
                'cvc' => '123',
            ],
        ]);

        $charge = $payment->charge(1000, $token->id);
        $this->assertEquals(1000, $charge->amount);
    }
}
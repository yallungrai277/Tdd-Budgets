<?php

namespace App\Http\Controllers;

use App\Cart\Cart;
use Illuminate\Http\Request;
use App\Payment\PaymentContract;

class CheckoutController extends Controller
{
    public function __construct(protected PaymentContract $payment)
    {
    }

    public function index(Cart $cart)
    {
        return view('checkout.index', [
            'cart' => $cart
        ]);
    }
}
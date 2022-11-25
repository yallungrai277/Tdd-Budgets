<?php

namespace App\Http\Controllers;

use App\Cart\Cart;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Payment\PaymentContract;
use App\Http\Requests\OrderStoreRequest;

class OrderController extends Controller
{
    public function __construct(protected PaymentContract $payment)
    {
    }

    public function store(OrderStoreRequest $request, Cart $cart)
    {
        $data = $request->validated();

        $user = $this->retrieveUser($data);

        $this->payment->charge($cart->totalPriceInCents(), request('token'));
        $order = Order::create([
            'user_id' => $user->id,
            'total' => $this->payment->totalCharged()
        ]);


        $order->addProducts($cart->items);
    }

    private function retrieveUser(array $data): User
    {
        if (auth()->check()) {
            return auth()->user();
        }

        $user = User::where('email', $data['email'])->first();
        if ($user) return $user;

        return User::create([
            'name' => $data['email'],
            'email' => $data['email'],
            'password' => Str::random(10),
        ]);
    }
}
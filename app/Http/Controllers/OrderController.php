<?php

namespace App\Http\Controllers;

use Exception;
use App\Cart\Cart;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Payment\PaymentContract;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\OrderStoreRequest;
use App\Jobs\OrderCreated;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(protected PaymentContract $payment)
    {
    }

    public function index(): View
    {
        $orders = Order::query()
            ->with([
                'products'
            ])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return view('orders.index', [
            'orders' => $orders
        ]);
    }

    public function store(OrderStoreRequest $request, Cart $cart): RedirectResponse
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $user = $this->retrieveUser($data);

            $this->payment->charge($cart->totalPriceInCents(), $data['token']);
            $order = Order::create([
                'user_id' => $user->id,
                'total' => $this->payment->totalCharged()
            ]);

            $order->addProducts($cart->items);
            $cart->clear();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            session()->flash('error-toast', $e->getMessage());
            return redirect()->back();
        }

        if (!auth()->check()) {
            auth()->login($user);
        }

        OrderCreated::dispatch($order);
        return redirect()->route('orders.index');
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
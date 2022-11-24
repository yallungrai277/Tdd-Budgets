<?php

namespace App\Http\Controllers;

use App\Cart\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Cart $cart)
    {
        return view('cart.index', [
            'cart' => $cart
        ]);
    }

    public function update(Cart $cart, Product $product)
    {
        $cart->add($product);
        session()->flash('success-toast', 'Item added.');
        return redirect()->route('cart.index');
    }
}
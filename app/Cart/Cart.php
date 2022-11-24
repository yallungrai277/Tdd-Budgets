<?php

namespace App\Cart;

use App\Models\Product;
use App\Exceptions\CartException;

class Cart
{
    public $items;

    public function __construct()
    {
        $this->items = collect();
        if (session()->has('cart')) {
            $this->items = session()->get('cart')->items;
        }
    }

    public function add(Product $product, int $qty = 1): void
    {
        $this->items->put($product->id, $this->setCartItem($product, $qty));
        session()->put('cart', $this);
    }

    public function totalPrice()
    {
        $totalPrice = $this->items->reduce(function ($total, $item) {
            return $total + $item['line_item_total'];
        }, 0);

        return round($totalPrice, 2);
    }

    public function incrementQuantity(Product $product)
    {
        if (is_null($item = $this->items->get($product->id))) {
            throw new CartException('Item cannot be found in the cart.');
        }

        $this->items->put($product->id, $this->setCartItem($product, ++$item['quantity']));
        session()->put('cart', $this);
    }

    public function decrementQuantity(Product $product)
    {
        if (is_null($item = $this->items->get($product->id))) {
            throw new CartException('Item cannot be found in the cart.');
        }

        if ($item['quantity'] === 1) {
            $this->items->forget($product->id);
        } else {
            $this->items->put($product->id, $this->setCartItem($product, --$item['quantity']));
        }
        session()->put('cart', $this);
    }

    private function setCartItem(Product $product, int $qty): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $qty,
            'line_item_total' => round($qty * $product->price, 2)
        ];
    }
}
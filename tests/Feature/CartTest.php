<?php

namespace Tests\Feature;

use App\Cart\Cart;
use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_update_cart_content()
    {
        $product = Product::factory()->create();
        $response = $this->put('cart/' . $product->id);
        $response->assertRedirect('/cart');
        $response->assertSessionHas('cart', function (Cart $cart) use ($product) {
            return $cart->items->contains('id', $product->id);
        });

        $this->get('/cart')
            ->assertViewHas('cart', function (Cart $cart) use ($product) {
                return $cart->items->contains('id', $product->id);
            });
    }
}
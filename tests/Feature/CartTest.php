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

    public function test_it_can_remove_cart_item()
    {
        $product = Product::factory()->create();
        $cart = new Cart();
        $cart->add($product);

        $this->delete('cart/' . $product->id)
            ->assertRedirect('/cart');

        $this->get('/cart')
            ->assertViewHas('cart', function (Cart $cart) use ($product) {
                return !$cart->items->contains('id', $product->id);
            });
    }

    public function test_it_can_clear_cart()
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $cart = new Cart();
        $cart->add($product1);
        $cart->add($product2);

        $this->delete('/cart')
            ->assertRedirect('/cart');

        $this->get('/cart')
            ->assertViewHas('cart', function (Cart $cart) use ($product1) {
                return !$cart->items->contains('id', $product1->id);
            })
            ->assertViewHas('cart', function (Cart $cart) use ($product2) {
                return !$cart->items->contains('id', $product2->id);
            });
    }
}
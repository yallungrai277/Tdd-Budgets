<?php

namespace Tests\Unit;

use App\Cart\Cart;
use App\Exceptions\CartException;
use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_add_items_to_the_cart(): void
    {
        $product = Product::factory()->create([
            'price' => 10
        ]);
        $cart = new Cart();

        $cart->add($product);

        $this->assertEquals(1, $cart->items->count());
    }

    public function test_it_can_add_same_item_with_multiple_quantity_to_the_cart(): void
    {
        $product = Product::factory()->create([
            'price' => 10
        ]);
        $cart = new Cart();

        $cart->add($product, 10);

        $this->assertEquals(1, $cart->items->count());
        $this->assertEquals(10, $cart->items->get($product->id)['quantity']);
    }

    public function test_it_has_a_total_price(): void
    {
        $products = Product::factory(3)->create([
            'price' => 10
        ]);
        $cart = new Cart();
        foreach ($products as $product) {
            $cart->add($product, 2);
        }

        $this->assertEquals($cart->totalPrice(), 60);
    }

    public function test_it_has_a_total_price_in_cents(): void
    {
        $products = Product::factory(3)->create([
            'price' => 10
        ]);
        $cart = new Cart();
        foreach ($products as $product) {
            $cart->add($product, 2);
        }

        $this->assertEquals($cart->totalPriceInCents(), 6000);
    }

    public function test_it_throws_an_exception_if_product_is_not_found_in_the_cart_while_decrementing_and_incrementing_quantity()
    {
        $this->expectException(CartException::class);
        $product = Product::factory()->create([
            'price' => 10
        ]);
        $cart = new Cart();


        $cart->decrementQuantity($product);
        $cart->incrementQuantity($product);
    }

    public function test_it_can_increment_the_product_quantity_in_cart(): void
    {
        $product = Product::factory()->create([
            'price' => 10
        ]);

        $cart = new Cart();

        $cart->add($product, 10);
        $cart->incrementQuantity($product);

        $this->assertEquals(11, $cart->items->get($product->id)['quantity']);
    }

    public function test_it_can_decrement_the_product_quantity_in_cart(): void
    {
        $product = Product::factory()->create([
            'price' => 10
        ]);

        $cart = new Cart();

        $cart->add($product, 10);
        $cart->decrementQuantity($product);

        $this->assertEquals(9, $cart->items->get($product->id)['quantity']);
    }

    public function test_it_can_delete_the_product_if_quantity_is_less_than_one_in_cart(): void
    {
        $product = Product::factory()->create([
            'price' => 10
        ]);

        $cart = new Cart();

        $cart->add($product, 1);
        $cart->decrementQuantity($product);

        $this->assertEquals(0, $cart->items->count());
    }

    public function test_it_can_remove_item_from_the_cart()
    {
        $product = Product::factory()->create([
            'price' => 10
        ]);

        $cart = new Cart();

        $cart->add($product, 1);
        $cart->removeItem($product);

        $this->assertEquals(0, $cart->items->count());
    }

    public function test_it_can_clear_cart()
    {
        $product1 = Product::factory()->create([
            'price' => 10
        ]);
        $product2 = Product::factory()->create([
            'price' => 40
        ]);
        $product3 = Product::factory()->create([
            'price' => 50
        ]);

        $cart = new Cart();

        $cart->add($product1, 1);
        $cart->add($product2, 3);
        $cart->add($product3, 2);
        $cart->clear();

        $this->assertCount(0, $cart->items);
    }

    public function test_it_can_check_cart_items()
    {
        $product1 = Product::factory()->create([
            'price' => 10
        ]);
        $product2 = Product::factory()->create([
            'price' => 40
        ]);
        $product3 = Product::factory()->create([
            'price' => 50
        ]);

        $cart = new Cart();

        $cart->add($product1, 1);
        $cart->add($product2, 3);
        $cart->add($product3, 2);

        $this->assertTrue($cart->hasItems());
        $cart->clear();
        $this->assertNotTrue($cart->hasItems());
    }
}
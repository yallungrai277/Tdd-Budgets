<?php

namespace Tests\Unit;

use App\Cart\Cart;
use Tests\TestCase;
use App\Models\Order;
use App\Models\Product;
use App\Jobs\OrderCreated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Mail\OrderCreated as MailOrderCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_will_be_saved_in_cents_and_retrieved_in_dollars(): void
    {
        $order = Order::factory()->create([
            'total' => 10
        ]);

        $this->assertCount(1, Order::all());
        $this->assertDatabaseHas('orders', [
            'total' => 1000,
            'id' => $order->id
        ]);

        $this->assertEquals($order->total, 10);
    }

    public function test_it_can_add_products_to_order(): void
    {
        $product1 = Product::factory()->create([
            'price' => 10
        ]);
        $product2 = Product::factory()->create([
            'price' => 20
        ]);

        $cart = new Cart();
        $cart->add($product1, 2);
        $cart->add($product2, 4);

        $order = Order::factory()->create();
        $order->addProducts($cart->items);

        $this->assertDatabaseHas('order_product', [
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'total' => 2000
        ]);

        $this->assertDatabaseHas('order_product', [
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 4,
            'total' => 8000
        ]);
    }

    public function test_order_reference_number_will_automatically_be_generated(): void
    {
        $order = Order::factory()->create();
        $this->assertNotNull($order->order_reference_number);
        $this->assertEquals(Order::ORDER_REF_NUMBER_STRING . '1', $order->order_reference_number);
    }

    public function test_order_email_can_be_sent_successfully()
    {
        Mail::fake();
        $order = Order::factory()->create();
        (new OrderCreated($order))->handle();

        Mail::assertQueued(MailOrderCreated::class);
    }
}
<?php

namespace Tests\Feature;

use App\Cart\Cart;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Payment\FakePayment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected $testEmail = 'test@gmail.com';

    public function test_it_requires_user_to_be_authenticated_to_view_orders_page(): void
    {
        $this->get('/orders')
            ->assertRedirect('/login');
    }

    public function test_orders_can_be_seen_on_orders_page_along_with_items(): void
    {


        $product1 = Product::factory()->create([
            'price' => 10
        ]);

        $product2 = Product::factory()->create([
            'price' => 20
        ]);

        $cart = new Cart();
        $cart->add($product1);
        $cart->add($product2, 2);

        $payment = new FakePayment();

        $this->post('/orders', [
            'email' => $this->testEmail,
            'token' => $payment->getTestToken()
        ]);

        $user = User::whereEmail($this->testEmail)->first();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 5000
        ]);

        $order = Order::first();

        $this->actAsAuthenticatedUser($user)
            ->get('/orders')
            ->assertStatus(200)
            ->assertSee('orders', fn (Collection $orders) => $orders->contains($order));
    }

    public function test_it_can_purchase_products(): void
    {
        $this->withExceptionHandling();
        $product = Product::factory()->create([
            'price' => 10
        ]);

        $cart = new Cart();
        $cart->add($product);

        $payment = new FakePayment();

        $this->post('/orders', [
            'email' => 'test@email.com',
            'token' => $payment->getTestToken()
        ]);

        $this->assertDatabaseHas('orders', [
            'total' => 1000
        ]);
    }

    public function test_email_is_required_when_purchasing_items_if_user_is_not_authenticated(): void
    {
        $product = Product::factory()->create([
            'price' => 10
        ]);

        $cart = new Cart();
        $cart->add($product);

        $payment = new FakePayment();

        $response = $this->post('/orders', [
            'token' => $payment->getTestToken()
        ]);
        $response->assertSessionHasErrors('email');
    }

    public function test_token_is_required_when_purchasing_items(): void
    {
        $product = Product::factory()->create([
            'price' => 10
        ]);

        $cart = new Cart();
        $cart->add($product);

        $response = $this->post('/orders', [
            'email' => $this->testEmail
        ]);
        $response->assertSessionHasErrors('token');
    }

    public function test_a_new_user_account_is_created_when_purchasing_items_if_user_is_not_authenticated_and_user_account_not_created(): void
    {
        $product = Product::factory()->create([
            'price' => 10
        ]);

        $cart = new Cart();
        $cart->add($product);

        $payment = new FakePayment();


        $this->post('/orders', [
            'email' => $this->testEmail,
            'token' => $payment->getTestToken()
        ]);

        $this->assertDatabaseHas('users', [
            'email' => $this->testEmail
        ]);

        $user = User::whereEmail($this->testEmail)->first();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 1000
        ]);

        $cart = new Cart();
        $this->assertCount(1, Order::all());
        $this->assertCount(0, $cart->items);
        $this->assertAuthenticated('web');
    }

    public function test_user_is_not_created_twice_even_if_user_is_not_authenticated_while_purchase(): void
    {
        $product = Product::factory()->create([
            'price' => 10
        ]);
        $user = User::factory([
            'email' => $this->testEmail
        ])->create();

        $cart = new Cart();
        $cart->add($product);

        $payment = new FakePayment();

        $this->actAsAuthenticatedUser($user)
            ->post('/orders', [
                'email' => $this->testEmail,
                'token' => $payment->getTestToken()
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $this->testEmail
        ]);

        $this->assertEquals(1, User::count());

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 1000
        ]);
        $this->assertCount(1, Order::all());
    }

    public function test_logged_in_users_purchase_will_be_created_even_if_email_is_provided(): void
    {
        $product = Product::factory()->create([
            'price' => 10
        ]);
        $user = User::factory([
            'email' => $this->testEmail
        ])->create();

        $cart = new Cart();
        $cart->add($product);

        $payment = new FakePayment();

        $this->actAsAuthenticatedUser($user)
            ->post('/orders', [
                'email' => 'james@gmail.com',
                'token' => $payment->getTestToken()
            ]);

        $this->assertEquals(1, User::count());
        $this->assertDatabaseMissing('users', [
            'email' => 'james@gmail.com'
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 1000
        ]);
        $this->assertCount(1, Order::all());
    }

    public function test_it_can_create_products_with_correct_amount_and_quantities_for_order_after_purchase()
    {
        $product1 = Product::factory()->create([
            'price' => 10
        ]);

        $product2 = Product::factory()->create([
            'price' => 20
        ]);

        $cart = new Cart();
        $cart->add($product1);
        $cart->add($product2, 2);

        $payment = new FakePayment();

        $this->post('/orders', [
            'email' => $this->testEmail,
            'token' => $payment->getTestToken()
        ]);

        $user = User::whereEmail($this->testEmail)->first();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 5000
        ]);

        $order = Order::first();

        $this->assertDatabaseHas('order_product', [
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 1,
            'total' => 1000
        ]);

        $this->assertDatabaseHas('order_product', [
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 2,
            'total' => 4000
        ]);
    }
}
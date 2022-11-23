<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function data(): array
    {
        return [
            'description' => "Test description",
            'amount' => 20,
            'date' => now()->format('Y-m-d')
        ];
    }

    public function test_unauthenticated_users_cannot_visit_transactions_pages()
    {
        $this->get('/transactions')
            ->assertRedirect('/login');

        $this->get('/transactions/create')
            ->assertRedirect('/login');
    }

    public function test_only_authenticated_users_can_visit_transactions_pages()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->actAsAuthenticatedUser($this->user)
            ->get('/transactions')
            ->assertStatus(200);

        $this->actAsAuthenticatedUser($this->user)
            ->get('/transactions/create')
            ->assertStatus(200);

        $this->actAsAuthenticatedUser($this->user)
            ->get('transactions/' . $transaction->id . '/edit')
            ->assertStatus(200);
    }

    public function test_it_can_display_all_transactions_with_category()
    {
        $transaction = Transaction::factory([
            'user_id' => $this->user->id
        ])->create();

        $this->actAsAuthenticatedUser($this->user)
            ->get('/transactions')
            ->assertSee($transaction->description)
            ->assertSee($transaction->category->name)
            ->assertSee($transaction->amount);
    }

    public function test_it_can_display_all_transactions_without_category()
    {
        $transaction = Transaction::factory([
            'user_id' => $this->user->id,
            'category_id' => null
        ])->create();

        $this->actAsAuthenticatedUser($this->user)
            ->get('/transactions')
            ->assertSee($transaction->description)
            ->assertSee($transaction->category?->name)
            ->assertSee($transaction->amount);
    }

    public function test_it_can_filter_transactions_by_category()
    {
        $category = Category::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id
        ]);
        $otherTransaction = Transaction::factory()->create([
            'user_id' => User::factory()->create(),
            'category_id' => null
        ]);

        $this->actAsAuthenticatedUser($this->user)
            ->get('/transactions?category_id=' . $category->id)
            ->assertSee($transaction->description)
            ->assertDontSee($otherTransaction->description);
    }

    public function test_it_can_filter_transactions_by_month()
    {
        $this->withExceptionHandling();
        $transaction1 = Transaction::factory()->create([
            'user_id' => $this->user,
            'date' => now()
        ]);

        $transaction2 = Transaction::factory()->create([
            'user_id' => $this->user,
            'date' => now()->subMonth()
        ]);

        $transaction3  = Transaction::factory()->create([
            'user_id' => $this->user,
            'date' => now()->subYear()
        ]);

        $transaction4 = Transaction::factory()->create([
            'user_id' => $this->user,
            'date' => now()->subYear()->subMonth()
        ]);

        $this->actAsAuthenticatedUser($this->user)
            ->get('/transactions?month=' . $transaction1->date->format('Y-m-d'))
            ->assertSeeText($transaction1->description)
            ->assertDontSeeText($transaction3->description)
            ->assertDontSeeText($transaction2->description)
            ->assertDontSeeText($transaction4->description);
    }

    public function test_it_can_only_display_transactions_related_to_user()
    {
        $category = Category::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id
        ]);
        $otherTransaction = Transaction::factory()->create([
            'user_id' => User::factory()->create(),
            'category_id' => $category->id
        ]);

        $this->actAsAuthenticatedUser($this->user)
            ->get('/transactions')
            ->assertSee($transaction->description)
            ->assertDontSee($otherTransaction->description);
    }

    public function test_it_can_render_transaction_create_screen()
    {
        $this->actAsAuthenticatedUser(User::factory()->create())
            ->get('/transactions/create')
            ->assertStatus(200);
    }

    public function test_it_requires_a_description_to_create_transaction()
    {
        $response = $this->actAsAuthenticatedUser(User::factory()->create())
            ->post('/transactions', []);

        $response->assertSessionHasErrors('description');
    }

    public function test_it_requires_a_valid_category_to_create_transaction()
    {
        $response = $this->actAsAuthenticatedUser(User::factory()->create())
            ->post('/transactions', array_merge($this->data(), [
                'category_id' => 10000000
            ]));

        $response->assertSessionHasErrors('category_id');
    }

    public function test_it_requires_a_valid_amount_to_create_transaction()
    {
        $response = $this->actAsAuthenticatedUser(User::factory()->create())
            ->post('/transactions', array_merge($this->data(), [
                'amount' => ''
            ]));

        $response2 = $this->actAsAuthenticatedUser(User::factory()->create())
            ->post('/transactions', array_merge($this->data(), [
                'amount' => 'asd'
            ]));

        $response->assertSessionHasErrors('amount');
        $response2->assertSessionHasErrors('amount');
    }

    public function test_it_requires_a_valid_date_to_create_transaction()
    {
        $response = $this->actAsAuthenticatedUser(User::factory()->create())
            ->post('/transactions', array_merge($this->data(), [
                'date' => ''
            ]));

        $response2 = $this->actAsAuthenticatedUser(User::factory()->create())
            ->post('/transactions', array_merge($this->data(), [
                'date' => '123-121-123'
            ]));

        $response->assertSessionHasErrors('date');
        $response2->assertSessionHasErrors('date');
    }


    public function test_it_can_create_transaction_without_category()
    {
        $this->actAsAuthenticatedUser($this->user)
            ->post('/transactions', $this->data());

        $transaction = Transaction::where('user_id', $this->user->id)->first();
        $this->assertEquals(1, $this->user->transactions->count());
        $this->assertEquals($transaction->user_id, $this->user->id);
        $this->assertNull($transaction->category_id);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'description' => $this->data()['description'],
            'amount' => $this->data()['amount'],
            'category_id' => null
        ]);
    }

    public function test_it_can_create_transaction_with_category()
    {
        $this->withoutExceptionHandling();

        $category = Category::factory()->create();
        $this->actAsAuthenticatedUser($this->user)
            ->post('/transactions', array_merge($this->data(), ['category_id' => $category->id]));

        $transaction = Transaction::where('user_id', $this->user->id)->first();
        $this->assertNotNull($transaction->category_id);
        $this->assertEquals($category->id, $transaction->category_id);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'description' => $this->data()['description'],
            'amount' => $this->data()['amount'],
            'category_id' => $category->id
        ]);
    }

    public function test_404_is_thrown_if_transaction_is_not_found()
    {
        $this->actAsAuthenticatedUser($this->user)
            ->get('transactions/200/edit')
            ->assertStatus(404);
    }

    public function test_it_can_render_edit_transaction_page_with_appropriate_info()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->actAsAuthenticatedUser($this->user)
            ->get('transactions/' . $transaction->id . '/edit')
            ->assertSee($transaction->description)
            ->assertSee($transaction->amount);
    }

    public function test_it_cannot_render_edit_transaction_page_for_other_users_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => User::factory()->create()
        ]);
        $this->actAsAuthenticatedUser($this->user)
            ->get('transactions/' . $transaction->id . '/edit')
            ->assertStatus(403);

        $this->actAsAuthenticatedUser($this->user)
            ->put('transactions/' . $transaction->id, $this->data())
            ->assertStatus(403);
    }

    public function test_it_requires_a_description_to_update_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);
        $response = $this->actAsAuthenticatedUser($this->user)
            ->put('transactions/' . $transaction->id, [
                'amount' => 123
            ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_it_requires_a_valid_date_to_update_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response1 = $this->actAsAuthenticatedUser($this->user)
            ->put('transactions/' . $transaction->id, [
                'date' => '123-123-12'
            ]);

        $response2 = $this->actAsAuthenticatedUser($this->user)
            ->put('transactions/' . $transaction->id, [
                'date' => ''
            ]);

        $response1->assertSessionHasErrors('date');
        $response2->assertSessionHasErrors('date');
    }

    public function test_it_requires_a_valid_amount_to_update_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);
        $response1 = $this->actAsAuthenticatedUser($this->user)
            ->put('transactions/' . $transaction->id, [
                'description' => 123,
                'amount' => 'asd'
            ]);

        $response2 = $this->actAsAuthenticatedUser($this->user)
            ->put('transactions/' . $transaction->id, [
                'description' => 123,
                'amount' => ''
            ]);

        $response1->assertSessionHasErrors('description');
        $response2->assertSessionHasErrors('description');
    }

    public function test_it_requires_a_valid_category_is_required_to_update_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);
        $response = $this->actAsAuthenticatedUser($this->user)
            ->put('transactions/' . $transaction->id, array_merge($this->data(), [
                'category_id' => 100000
            ]));

        $response->assertSessionHasErrors('category_id');
    }

    public function test_category_can_be_nullable_to_update_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => Category::factory()->create()->id
        ]);

        $this->actAsAuthenticatedUser($this->user)
            ->put('transactions/' . $transaction->id, array_merge($this->data(), [
                'category_id' => null
            ]));

        $this->assertNull($transaction->fresh()->category_id);
    }

    public function test_it_can_update_transaction_with_valid_attributes()
    {
        $otherCategory = Category::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => Category::factory()->create()->id
        ]);

        $this->actAsAuthenticatedUser($this->user)
            ->put('transactions/' . $transaction->id, array_merge($this->data(), array_merge($this->data(), [
                'category_id' => $otherCategory->id
            ])));

        $transaction = $transaction->fresh();
        $this->assertEquals($transaction->category_id, $otherCategory->id);
        $this->assertEquals($transaction->description, $this->data()['description']);
        $this->assertEquals($transaction->amount, $this->data()['amount']);

        $this->assertDatabaseHas('transactions', [
            'category_id' => $otherCategory->id,
            'description' => $this->data()['description'],
            'amount' => $this->data()['amount'],
            'user_id' => $this->user->id
        ]);
    }

    public function test_it_can_delete_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->actAsAuthenticatedUser($this->user)
            ->delete("transactions/" . $transaction->id);

        $this->assertDatabaseEmpty("transactions");
        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id
        ]);
    }

    public function test_it_cannot_delete_other_users_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => User::factory()->create()
        ]);

        $response = $this->actAsAuthenticatedUser($this->user)
            ->delete("transactions/" . $transaction->id);

        $response->assertStatus(403);
    }
}
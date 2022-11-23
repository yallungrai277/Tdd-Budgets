<?php

namespace Tests\Unit;

use App\Models\Budget;
use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_it_has_a_balance()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $transactions = Transaction::factory(3)->create([
            'category_id' => $category->id,
            'user_id' => $user->id
        ]);

        Transaction::factory(3)->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'date' => now()->subMonth(1)
        ]);

        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);

        $expectedBalance = $budget->amount - $transactions->sum('amount');
        $this->assertEquals($expectedBalance, $budget->balance());
    }

    public function test_it_only_calculates_remaning_balance_for_personal_transaction()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $transactions = Transaction::factory(3)->create([
            'category_id' => $category->id,
            'user_id' => $user->id
        ]);

        Transaction::factory(3)->create([
            'category_id' => Category::factory()->create(),
            'user_id' => $user->id
        ]);

        Transaction::factory(3)->create([
            'category_id' => $category->id,
            'user_id' => User::factory()->create()
        ]);

        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);

        $expectedBalance = $budget->amount - $transactions->sum('amount');
        $this->assertEquals($expectedBalance, $budget->balance());
    }
}
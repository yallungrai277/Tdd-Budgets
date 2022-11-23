<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setup(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function data(): array
    {
        return [
            'user_id' => $this->user->id,
            'amount' => 200,
            'year' => now()->format('Y'),
            'month' => now()->format('m'),
        ];
    }

    public function test_only_authenticated_users_can_visit_budget_page()
    {
        $this->get('budgets')
            ->assertRedirect('/login');

        $this->get('/budgets/create')
            ->assertRedirect('/login');

        $this->post('/budgets')
            ->assertRedirect('/login');
    }

    public function test_it_displays_budget_for_curent_month_by_default()
    {
        $budgetForThisMonth = Budget::factory()->create($this->data());

        $budgetForLastMonth = Budget::factory([
            'month' => now()->subMonth()->format('m'),
            'user_id' => $this->user->id
        ])->create();

        $budgetForLastYear = Budget::factory([
            'month' => now()->subYear()->subMonth()->format('m'),
            'user_id' => $this->user->id
        ])->create();

        $this->actAsAuthenticatedUser($this->user)
            ->get('/budgets')
            ->assertSeeText((string) $budgetForThisMonth->amount)
            ->assertSeeText((string) $budgetForThisMonth->balance())
            ->assertDontSeeText((string) $budgetForLastMonth->amount)
            ->assertDontSeeText((string) $budgetForLastMonth->balance())
            ->assertDontSeeText((string) $budgetForLastYear->amount)
            ->assertDontSeeText((string) $budgetForLastYear->balance());
    }

    public function test_it_filters_budget_by_month()
    {
        $budgetForThisMonth = Budget::factory()->create($this->data());

        $budgetForLastMonth = Budget::factory(array_merge($this->data(), [
            'month' => now()->subMonth()->format('m'),
            'amount' => 500,
        ]))->create();

        $budgetForLastYear = Budget::factory(
            array_merge(
                $this->data(),
                [
                    'month' => now()->subYear()->subMonth()->format('m'),
                    'amount' => 600,
                ]
            )
        )->create();


        $this->actAsAuthenticatedUser($this->user)
            ->get("/budgets?month={$budgetForThisMonth->year}-{$budgetForThisMonth->month}")
            ->assertSeeText((string) $budgetForThisMonth->amount)
            ->assertSeeText((string) $budgetForThisMonth->balance())
            ->assertDontSeeText((string) $budgetForLastMonth->amount)
            ->assertDontSeeText((string) $budgetForLastMonth->balance())
            ->assertDontSeeText((string) $budgetForLastYear->amount)
            ->assertDontSeeText((string) $budgetForLastYear->balance());
    }

    public function test_it_only_displays_budgets_for_currently_logged_in_user()
    {
        $budget1 = Budget::factory()->create($this->data());

        $budget2 = Budget::factory()->create(array_merge($this->data(), [
            'user_id' => User::factory()->create(),
            'amount' => 3000
        ]));

        $this->actAsAuthenticatedUser($this->user)
            ->get("/budgets")
            ->assertSeeText((string) $budget1->amount)
            ->assertDontSeeText((string) $budget2->amount);
    }

    public function test_it_can_render_budget_create_page()
    {
        $this->actAsAuthenticatedUser($this->user)
            ->get('/budgets/create')
            ->assertStatus(200);
    }

    public function test_it_requires_a_valid_category_to_create_budget()
    {
        $response = $this->actAsAuthenticatedUser($this->user)
            ->post('/budgets', array_merge($this->data(), [
                'date' => now()->format('Y-m-d'),
                'category_id' => ''
            ]));

        $response2 = $this->actAsAuthenticatedUser($this->user)
            ->post('/budgets', array_merge($this->data(), [
                'date' => now()->format('Y-m-d'),
                'category_id' => 200
            ]));
        $response->assertSessionHasErrors('category_id');
        $response2->assertSessionHasErrors('category_id');
    }

    public function test_it_requires_a_valid_amount_to_create_budget()
    {
        $category = Category::factory()->create();
        $response = $this->actAsAuthenticatedUser($this->user)
            ->post('/budgets', array_merge($this->data(), [
                'date' => now()->format('Y-m-d'),
                'amount' => '',
                'category_id' => $category->id
            ]));

        $response2 = $this->actAsAuthenticatedUser($this->user)
            ->post('/budgets', array_merge($this->data(), [
                'date' => now()->format('Y-m'),
                'amount' => 'asd',
                'category_id' => $category->id
            ]));
        $response->assertSessionHasErrors('amount');
        $response2->assertSessionHasErrors('amount');
    }

    public function test_it_requires_a_valid_date_to_create_budget()
    {
        $category = Category::factory()->create();
        $response = $this->actAsAuthenticatedUser($this->user)
            ->post('/budgets', array_merge($this->data(), [
                'date' => '',
                'amount' => '',
                'category_id' => $category->id
            ]));

        $response2 = $this->actAsAuthenticatedUser($this->user)
            ->post('/budgets', array_merge($this->data(), [
                'date' => now()->format('Y-m-d'),
                'amount' => 'asd',
                'category_id' => $category->id
            ]));
        $response->assertSessionHasErrors('date');
        $response2->assertSessionHasErrors('date');
    }

    public function test_it_can_create_budgets()
    {
        $response = $this->actAsAuthenticatedUser($this->user)
            ->post('/budgets', array_merge($this->data(), [
                'date' => now()->format('Y-m'),
                'category_id' => Category::factory()->create()->id
            ]));

        $budget = Budget::first();
        $response->assertRedirectToRoute('budgets.index', [
            'month' => "{$budget->year}-{$budget->month}"
        ]);
    }

    public function test_it_cannot_create_budgets_with_same_month_and_same_category_for_user()
    {
        $category = Category::factory()->create();
        Budget::factory()->create(array_merge($this->data(), [
            'category_id' => $category->id
        ]));

        $response = $this->actAsAuthenticatedUser($this->user)
            ->post('/budgets', array_merge($this->data(), [
                'category_id' => $category->id,
                'date' => now()->format('Y-m'),
            ]));

        $response->assertSessionHasErrors('budget');
    }

    public function test_it_can_create_budgets_with_different_month_and_category_for_user()
    {
        $category = Category::factory()->create();
        Budget::factory()->create(array_merge($this->data(), [
            'category_id' => Category::factory()->create()
        ]));

        $response = $this->actAsAuthenticatedUser($this->user)
            ->post('/budgets', array_merge($this->data(), [
                'category_id' => $category->id,
                'date' => now()->format('Y-m'),
            ]));

        $response = $this->actAsAuthenticatedUser($this->user)
            ->post('/budgets', array_merge($this->data(), [
                'category_id' => $category2Id = Category::factory()->create()->id,
                'date' => now()->format('Y-m'),
            ]));

        $response = $this->actAsAuthenticatedUser($this->user)
            ->post('/budgets', array_merge($this->data(), [
                'category_id' => $category->id,
                'date' => now()->subMonth()->format('Y-m'),
            ]));

        $this->assertDatabaseHas('budgets', [
            'category_id' => $category->id,
            'year' => now()->format('Y'),
            'month' => now()->format('m')
        ]);

        $this->assertDatabaseHas('budgets', [
            'category_id' => $category2Id,
            'year' => now()->format('Y'),
            'month' => now()->format('m')
        ]);

        $this->assertDatabaseHas('budgets', [
            'category_id' => $category->id,
            'year' => now()->subMonth()->format('Y'),
            'month' => now()->subMonth()->format('m')
        ]);
    }

    public function test_404_is_thrown_if_budget_is_not_found()
    {
        $this->actAsAuthenticatedUser($this->user)
            ->get('budgets/200/edit')
            ->assertStatus(404);
    }

    public function test_it_can_render_edit_budget_page_with_appropriate_info()
    {
        $budget = Budget::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->actAsAuthenticatedUser($this->user)
            ->get('budgets/' . $budget->id . '/edit')
            ->assertSee($budget->description)
            ->assertSee($budget->amount);
    }

    public function test_it_cannot_render_edit_budget_page_for_other_users_budget()
    {
        $budget = Budget::factory()->create([
            'user_id' => User::factory()->create(),
            'category_id' => $categoryId = Category::factory()->create()->id,
        ]);
        $this->actAsAuthenticatedUser($this->user)
            ->get('budgets/' . $budget->id . '/edit')
            ->assertStatus(403);
    }

    public function test_it_requires_a_valid_category_to_update_budget()
    {
        $budget = Budget::factory()->create(array_merge($this->data(), [
            'category_id' => Category::factory()->create()->id,
            'user_id' => $this->user->id
        ]));

        $response = $this->actAsAuthenticatedUser($this->user)
            ->put('budgets/' . $budget->id, array_merge($this->data(), [
                'date' => now()->format('Y-m-d'),
                'category_id' => ''
            ]));

        $response2 = $this->actAsAuthenticatedUser($this->user)
            ->put('budgets/' . $budget->id, array_merge($this->data(), [
                'date' => now()->format('Y-m-d'),
                'category_id' => 200
            ]));
        $response->assertSessionHasErrors('category_id');
        $response2->assertSessionHasErrors('category_id');
    }

    public function test_it_requires_a_valid_amount_to_update_budget()
    {
        $budget = Budget::factory()->create(array_merge($this->data(), [
            'category_id' => $categoryId = Category::factory()->create()->id,
            'user_id' => $this->user->id
        ]));

        $response = $this->actAsAuthenticatedUser($this->user)
            ->put('budgets/' . $budget->id, array_merge($this->data(), [
                'date' => now()->format('Y-m-d'),
                'amount' => '',
                'category_id' => $categoryId
            ]));

        $response2 = $this->actAsAuthenticatedUser($this->user)
            ->put('budgets/' . $budget->id, array_merge($this->data(), [
                'date' => now()->format('Y-m'),
                'amount' => 'asd',
                'category_id' => $categoryId
            ]));
        $response->assertSessionHasErrors('amount');
        $response2->assertSessionHasErrors('amount');
    }

    public function test_it_requires_a_valid_date_to_update_budget()
    {
        $budget = Budget::factory()->create(array_merge($this->data(), [
            'category_id' => $categoryId = Category::factory()->create()->id,
            'user_id' => $this->user->id
        ]));

        $response = $this->actAsAuthenticatedUser($this->user)
            ->put('budgets/' . $budget->id, array_merge($this->data(), [
                'date' => '',
                'amount' => '',
                'category_id' => $categoryId
            ]));

        $response2 = $this->actAsAuthenticatedUser($this->user)
            ->put('budgets/' . $budget->id, array_merge($this->data(), [
                'date' => now()->format('Y-m-d'),
                'amount' => 'asd',
                'category_id' => $categoryId
            ]));
        $response->assertSessionHasErrors('date');
        $response2->assertSessionHasErrors('date');
    }

    public function test_it_can_update_budget()
    {
        $category = Category::factory()->create();
        $category2 = Category::factory()->create();
        $budget = Budget::factory([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
        ])->create();

        $this->actAsAuthenticatedUser($this->user)
            ->put("budgets/" . $budget->id, array_merge($this->data(), [
                'category_id' => $category2->id,
                'date' => now()->format('Y-m')
            ]));

        $this->assertDatabaseHas('budgets', [
            'category_id' => $category2->id,
            'amount' => $this->data()['amount'],
            'user_id' => $this->user->id,
            'year' => now()->format('Y'),
            'month' => now()->format('m')
        ]);
    }

    public function test_it_can_update_budgets_with_different_month_and_category_for_user()
    {
        $category = Category::factory()->create();
        $budget = Budget::factory([
            'category_id' => Category::factory()->create(),
            'user_id' => $this->user->id,
        ])->create();

        $budget2 = Budget::factory([
            'category_id' => Category::factory()->create(),
            'user_id' => $this->user->id,
        ])->create();

        $budget3 = Budget::factory([
            'category_id' => Category::factory()->create(),
            'user_id' => $this->user->id,
        ])->create();

        $this->actAsAuthenticatedUser($this->user)
            ->put('budgets/' . $budget->id, array_merge($this->data(), [
                'category_id' => $category->id,
                'date' => now()->format('Y-m'),
            ]));

        $this->actAsAuthenticatedUser($this->user)
            ->put('budgets/' . $budget2->id, array_merge($this->data(), [
                'category_id' => $category2Id = Category::factory()->create()->id,
                'date' => now()->format('Y-m'),
            ]));

        $this->actAsAuthenticatedUser($this->user)
            ->put('budgets/' . $budget3->id, array_merge($this->data(), [
                'category_id' => $category->id,
                'date' => now()->subMonth()->format('Y-m'),
            ]));

        $this->assertDatabaseHas('budgets', [
            'category_id' => $category->id,
            'year' => now()->format('Y'),
            'month' => now()->format('m')
        ]);

        $this->assertDatabaseHas('budgets', [
            'category_id' => $category2Id,
            'year' => now()->format('Y'),
            'month' => now()->format('m')
        ]);

        $this->assertDatabaseHas('budgets', [
            'category_id' => $category->id,
            'year' => now()->subMonth()->format('Y'),
            'month' => now()->subMonth()->format('m')
        ]);
    }

    public function test_it_cannot_update_budgets_which_has_existing_category_and_month_for_user()
    {
        $category = Category::factory()->create();
        $budget = Budget::factory()->create(array_merge($this->data(), [
            'category_id' => $category->id,
            'user_id' => $this->user->id,
        ]));

        $budget2 = Budget::factory()->create(array_merge($this->data(), [
            'category_id' => $category2Id = Category::factory()->create()->id,
            'user_id' => $this->user->id,
        ]));

        $response = $this->actAsAuthenticatedUser($this->user)
            ->put('budgets/' . $budget->id, array_merge($this->data(), [
                'category_id' => $category2Id,
                'date' => now()->format('Y-m'),
            ]));

        $response->assertSessionHasErrors('budget');
    }


    public function test_it_can_delete_budget()
    {
        $budget = Budget::factory()->create(array_merge($this->data(), [
            'category_id' => Category::factory()->create()
        ]));

        $this->actAsAuthenticatedUser($this->user)
            ->delete("budgets/" . $budget->id);

        $this->assertDatabaseEmpty("budgets");
        $this->assertDatabaseMissing('budgets', [
            'id' => $budget->id
        ]);
    }

    public function test_it_cannot_delete_other_users_transaction()
    {
        $budget = Budget::factory()->create(array_merge($this->data(), [
            'category_id' => Category::factory()->create(),
            'user_id' => User::factory()->create()->id
        ]));

        $response = $this->actAsAuthenticatedUser($this->user)
            ->delete("budgets/" . $budget->id);

        $response->assertStatus(403);
        $this->assertDatabaseCount('budgets', 1);
    }
}
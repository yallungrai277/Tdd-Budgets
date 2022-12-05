<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setup(): void
    {
        parent::setup();
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

    public function test_it_can_list_users_transaction()
    {
        [$transaction1, $transaction2] = Transaction::factory(2)->create([
            'user_id' => $this->user->id
        ]);

        $this->actAsAuthenticatedSanctumUser($this->user);

        $response = $this->getJson('api/transactions');
        $response->assertOk();

        $response->assertJsonFragment([
            'id' => $transaction1->id,
            'amount' => $transaction1->amount,
            'category' => is_null($transaction1->category) ? null : [
                'id' => $transaction1->category->id,
                'name' => $transaction1->category->name
            ],
            'date' => $transaction1->date->format('Y-m-d'),
            'description' => $transaction1->description
        ]);

        $response->assertJsonCount(2, 'data');
    }

    public function test_it_will_only_return_transaction_belonging_to_authenticated_user()
    {
        $transaction1 = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);

        $transaction2 = Transaction::factory()->create();

        $this->actAsAuthenticatedSanctumUser($this->user);

        $response = $this->getJson('api/transactions');
        $response->assertOk();

        $response->assertJsonFragment([
            'id' => $transaction1->id,
            'amount' => $transaction1->amount,
            'category' => is_null($transaction1->category) ? null : [
                'id' => $transaction1->category->id,
                'name' => $transaction1->category->name
            ],
            'date' => $transaction1->date->format('Y-m-d'),
            'description' => $transaction1->description
        ]);

        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing([
            'data' => [
                'id' => $transaction2->id,
                'amount' => $transaction2->amount,
                'category' => is_null($transaction2->category) ? null : [
                    'id' => $transaction2->category->id,
                    'name' => $transaction2->category->name
                ],
                'date' => $transaction2->date->format('Y-m-d'),
                'description' => $transaction2->description
            ]
        ]);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'amount',
                    'date',
                    'description',
                    'category'
                ]
            ],
            'links',
            'meta'
        ]);
    }

    public function test_it_can_return_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->actAsAuthenticatedSanctumUser($this->user);

        $response = $this->getJson('api/transactions/' . $transaction->id);

        $response->assertOk();
        $response->assertExactJson(
            [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'category' => is_null($transaction->category) ? null : [
                    'id' => $transaction->category->id,
                    'name' => $transaction->category->name
                ],
                'date' => $transaction->date->format('Y-m-d'),
                'description' => $transaction->description
            ]
        );
        $response->assertJsonMissingPath('data.created_at');
        $response->assertJsonStructure([
            'id',
            'amount'
        ]);
    }

    public function test_it_returns_404_if_transaction_not_found()
    {
        $this->actAsAuthenticatedSanctumUser($this->user);
        $response = $this->getJson('api/transactions/1');
        $response->assertNotFound();
    }

    public function test_it_returns_403_if_transaction_does_not_belong_to_the_user()
    {
        $transaction = Transaction::factory()->create();

        $this->actAsAuthenticatedSanctumUser($this->user);
        $this->getJson('api/transactions/' . $transaction->id)
            ->assertForbidden();
    }

    public function test_it_can_delete_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->actAsAuthenticatedSanctumUser($this->user);
        $response = $this->deleteJson('api/transactions/' . $transaction->id);
        $response->assertStatus(204);

        $this->assertModelMissing($transaction);
    }

    public function test_it_returns_404_if_transaction_not_found_when_deleting()
    {
        $this->actAsAuthenticatedSanctumUser($this->user);
        $response = $this->deleteJson('api/transactions/1');
        $response->assertNotFound();
    }

    public function test_it_returns_403_if_transaction_does_not_belong_to_the_user_when_deleting()
    {
        $transaction = Transaction::factory()->create();

        $this->actAsAuthenticatedSanctumUser($this->user);
        $this->deleteJson('api/transactions/' . $transaction->id)
            ->assertForbidden();
    }

    public function test_it_requires_a_description_to_create_transaction()
    {
        $this->actAsAuthenticatedSanctumUser($this->user);
        $this->actAsAuthenticatedSanctumUser($this->user);
        $response = $this->postJson('/api/transactions', []);

        $response->assertJsonValidationErrorFor('description');
    }

    public function test_it_requires_a_valid_category_to_create_transaction()
    {
        $this->actAsAuthenticatedSanctumUser($this->user);
        $response = $this->postJson('/api/transactions', array_merge($this->data(), [
            'category_id' => 10000000
        ]));

        $response->assertJsonValidationErrorFor('category_id');
    }

    public function test_it_requires_a_valid_amount_to_create_transaction()
    {
        $this->actAsAuthenticatedSanctumUser($this->user);
        $response = $this->postJson('/api/transactions', array_merge($this->data(), [
            'amount' => ''
        ]));

        $this->actAsAuthenticatedSanctumUser($this->user);
        $response2 = $this->postJson('/api/transactions', array_merge($this->data(), [
            'amount' => 'asd'
        ]));

        $response->assertJsonValidationErrorFor('amount');
        $response2->assertJsonValidationErrorFor('amount');
    }

    public function test_it_requires_a_valid_date_to_create_transaction()
    {
        $this->actAsAuthenticatedSanctumUser($this->user);
        $response = $this->postJson('/api/transactions', array_merge($this->data(), [
            'date' => ''
        ]));

        $this->actAsAuthenticatedSanctumUser($this->user);
        $response2 = $this->postJson('/api/transactions', array_merge($this->data(), [
            'date' => '123-121-123'
        ]));

        $response->assertJsonValidationErrorFor('date');
        $response2->assertJsonValidationErrorFor('date');
    }


    public function test_it_can_create_transaction_without_category()
    {
        $this->actAsAuthenticatedSanctumUser($this->user);
        $response = $this->postJson('/api/transactions', $this->data());

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
        $response->assertOk();
        $response->assertExactJson(
            [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'category' => is_null($transaction->category) ? null : [
                    'id' => $transaction->category->id,
                    'name' => $transaction->category->name
                ],
                'date' => $transaction->date->format('Y-m-d'),
                'description' => $transaction->description
            ]
        );
    }

    public function test_it_can_create_transaction_with_category()
    {
        $this->withoutExceptionHandling();

        $category = Category::factory()->create();
        $this->actAsAuthenticatedSanctumUser($this->user);
        $response = $this->postJson('/api/transactions', array_merge($this->data(), ['category_id' => $category->id]));

        $transaction = Transaction::where('user_id', $this->user->id)->first();
        $this->assertNotNull($transaction->category_id);
        $this->assertEquals($category->id, $transaction->category_id);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'description' => $this->data()['description'],
            'amount' => $this->data()['amount'],
            'category_id' => $category->id
        ]);

        $response->assertOk();
        $response->assertExactJson(
            [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'category' => is_null($transaction->category) ? null : [
                    'id' => $transaction->category->id,
                    'name' => $transaction->category->name
                ],
                'date' => $transaction->date->format('Y-m-d'),
                'description' => $transaction->description
            ]
        );
    }

    public function test_it_requires_a_description_to_update_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->actAsAuthenticatedSanctumUser($this->user);
        $response = $this->putJson('api/transactions/' . $transaction->id, [
            'amount' => 123
        ]);

        $response->assertJsonValidationErrorFor('description');
    }

    public function test_it_requires_a_valid_date_to_update_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->actAsAuthenticatedSanctumUser($this->user);
        $response1 = $this->putJson('api/transactions/' . $transaction->id, array_merge($this->data(), [
            'date' => '123-123-12'
        ]));

        $this->actAsAuthenticatedSanctumUser($this->user);
        $response2 = $this->putJson('api/transactions/' . $transaction->id, array_merge($this->data(), [
            'date' => ''
        ]));

        $response1->assertJsonValidationErrorFor('date');
        $response2->assertJsonValidationErrorFor('date');
    }

    public function test_it_requires_a_valid_amount_to_update_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->actAsAuthenticatedSanctumUser($this->user);
        $response1 = $this->putJson('api/transactions/' . $transaction->id, [
            'description' => 123,
            'amount' => 'asd'
        ]);

        $this->actAsAuthenticatedSanctumUser($this->user);
        $response2 = $this->putJson('api/transactions/' . $transaction->id, [
            'description' => 123,
            'amount' => ''
        ]);

        $response1->assertJsonValidationErrorFor('description');
        $response2->assertJsonValidationErrorFor('description');
    }

    public function test_it_requires_a_valid_category_is_required_to_update_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->actAsAuthenticatedSanctumUser($this->user);
        $response = $this->putJson('api/transactions/' . $transaction->id, array_merge($this->data(), [
            'category_id' => 100000
        ]));

        $response->assertJsonValidationErrorFor('category_id');
    }

    public function test_category_can_be_nullable_to_update_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => Category::factory()->create()->id
        ]);

        $this->actAsAuthenticatedUser($this->user);
        $response = $this->putJson('api/transactions/' . $transaction->id, array_merge($this->data(), [
            'category_id' => null
        ]));

        $transaction = $transaction->refresh();
        $this->assertNull($transaction->category_id);

        $response->assertOk();
        $response->assertExactJson(
            [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'category' => is_null($transaction->category) ? null : [
                    'id' => $transaction->category->id,
                    'name' => $transaction->category->name
                ],
                'date' => $transaction->date->format('Y-m-d'),
                'description' => $transaction->description
            ]
        );
    }

    public function test_it_can_update_transaction_with_valid_attributes()
    {
        $otherCategory = Category::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => Category::factory()->create()->id
        ]);

        $this->assertModelExists($transaction);
        $this->actAsAuthenticatedSanctumUser($this->user);
        $response = $this->putJson('api/transactions/' . $transaction->id, array_merge($this->data(), array_merge($this->data(), [
            'category_id' => $otherCategory->id
        ])));


        $transaction = $transaction->refresh();
        $this->assertEquals($transaction->category_id, $otherCategory->id);
        $this->assertEquals($transaction->description, $this->data()['description']);
        $this->assertEquals($transaction->amount, $this->data()['amount']);

        $this->assertDatabaseHas('transactions', [
            'category_id' => $otherCategory->id,
            'description' => $this->data()['description'],
            'amount' => $this->data()['amount'],
            'user_id' => $this->user->id
        ]);

        $response->assertOk();
        $response->assertExactJson(
            [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'category' => is_null($transaction->category) ? null : [
                    'id' => $transaction->category->id,
                    'name' => $transaction->category->name
                ],
                'date' => $transaction->date->format('Y-m-d'),
                'description' => $transaction->description
            ]
        );
    }
}
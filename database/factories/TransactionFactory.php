<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'description' => fake()->sentence(12),
            'user_id' => User::factory()->create(),
            'category_id' => Category::factory()->create(),
            'amount' => rand(10, 200),
            'date' => now()->format('Y-m-d')
        ];
    }
}
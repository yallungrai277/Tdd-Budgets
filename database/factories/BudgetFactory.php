<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'category_id' => Category::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
            'amount' => rand(100, 500),
            'year' => now()->format('Y'),
            'month' => now()->format('m'),
        ];
    }
}
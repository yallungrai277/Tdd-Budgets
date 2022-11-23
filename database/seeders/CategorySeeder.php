<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            'Health',
            'Shopping',
            'Income',
            'Education',
            'Eating Out',
            'Food',
            'Entertainment',
            'Transport',
            'Travel',
            'Utilities',
            'Other',
            'Groceries'
        ];


        foreach ($categories as $category) {
            Category::firstOrCreate([
                'name' => $category
            ]);
        }
    }
}
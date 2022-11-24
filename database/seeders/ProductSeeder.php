<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [
            'Sofa',
            'Bed',
            'Table',
            'Table Lights',
            'Decors',
            'Plates',
            'Knives',
            'Telivision',
            'Washing Machine',
            'Refrigerator',
            'Fan',
            'Lamp',
        ];


        foreach ($products as $product) {
            Product::updateOrCreate([
                'name' => $product,
            ], [
                'description' => fake()->sentences(5, true),
                'price' => rand(30, 200)
            ]);
        }
    }
}
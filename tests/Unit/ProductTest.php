<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_will_be_saved_in_cents_and_retrieved_in_dollars(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 10
        ]);

        $this->assertCount(1, Product::all());
        $this->assertDatabaseHas('products', [
            'price' => 1000,
            'name' => $product->name,
            'id' => $product->id
        ]);

        $this->assertEquals($product->price, 10);
    }
}
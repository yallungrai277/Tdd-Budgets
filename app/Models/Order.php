<?php

namespace App\Models;

use App\Pivots\OrderProduct;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    const ORDER_REF_NUMBER_STRING = 'ORD-';

    protected static function booted()
    {
        static::creating(function (Order $order) {
            $order->order_reference_number = self::ORDER_REF_NUMBER_STRING . self::max('id') + 1;
        });
    }

    public function total(): Attribute
    {
        return new Attribute(
            get: fn ($value) => round($value / 100, 2),
            set: fn ($value) => $value * 100
        );
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['quantity', 'price', 'total'])
            ->using(OrderProduct::class);
    }

    public function addProducts(Collection $products)
    {
        foreach ($products as $product) {
            $this->products()->attach($product['id'], [
                'quantity' => $product['quantity'],
                'price' => $product['price'],
                'total' => $product['line_item_total']
            ]);
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price'
    ];

    public function price(): Attribute
    {
        return new Attribute(
            get: fn ($value) => round($value / 100, 2),
            set: fn ($value) => $value * 100
        );
    }
}
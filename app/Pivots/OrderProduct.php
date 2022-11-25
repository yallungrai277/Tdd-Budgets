<?php

namespace App\Pivots;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderProduct extends Pivot
{
    public function price(): Attribute
    {
        return new Attribute(
            get: fn ($value) => round($value / 100, 2),
            set: fn ($value) => $value * 100
        );
    }

    public function total(): Attribute
    {
        return new Attribute(
            get: fn ($value) => round($value / 100, 2),
            set: fn ($value) => $value * 100
        );
    }
}
<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'user_id',
        'amount',
        'year',
        'month'
    ];

    /*
    Fetches balance for current month
    */

    public function balance(): float
    {
        return $this->amount -
            $this->category
            ->transactions()
            ->where('user_id', $this->user_id)
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->sum('amount');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function displayableDate()
    {
        try {
            $date =  Carbon::createFromFormat('Y-m', "{$this->year}-{$this->month}")->format('M Y');
            return $date;
        } catch (Exception $e) {
            return 'N/A';
        }
    }
}
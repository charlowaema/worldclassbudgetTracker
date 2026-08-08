<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'month',
        'year',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** Amount actually spent against this budget so far. */
    public function spentAmount(): float
    {
        return (float) Transaction::forUser($this->user_id)
            ->where('category_id', $this->category_id)
            ->expense()
            ->inMonth($this->month, $this->year)
            ->sum('amount');
    }

    public function percentUsed(): float
    {
        if ((float) $this->amount <= 0) {
            return 0;
        }

        return min(100, round(($this->spentAmount() / (float) $this->amount) * 100, 1));
    }

    public function remaining(): float
    {
        return (float) $this->amount - $this->spentAmount();
    }
}

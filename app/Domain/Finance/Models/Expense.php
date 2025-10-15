<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'amount',
        'reference_month',
        'paid_at',
        'notes',
        'is_recurring',
    ];

    protected $casts = [
        'amount' => 'float',
        'reference_month' => 'date',
        'paid_at' => 'datetime',
        'is_recurring' => 'boolean',
    ];
}

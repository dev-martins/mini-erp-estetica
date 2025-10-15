<?php

namespace App\Domain\Marketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'starts_at',
        'ends_at',
        'max_uses',
        'used_count',
        'min_purchase_amount',
        'active',
    ];

    protected $casts = [
        'value' => 'float',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'min_purchase_amount' => 'float',
        'active' => 'boolean',
    ];
}

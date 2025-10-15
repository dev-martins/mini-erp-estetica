<?php

namespace App\Domain\Loyalty\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_type',
        'value',
        'points',
        'active',
        'conditions',
    ];

    protected $casts = [
        'value' => 'float',
        'points' => 'integer',
        'active' => 'boolean',
        'conditions' => 'array',
    ];

    public function points(): HasMany
    {
        return $this->hasMany(LoyaltyPoint::class, 'rule_id');
    }
}

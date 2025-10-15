<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Clients\Models\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'rule_id',
        'points',
        'reason',
        'ref_id',
        'ref_type',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(LoyaltyRule::class);
    }
}

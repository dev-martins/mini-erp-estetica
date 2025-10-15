<?php

namespace App\Domain\Clients\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'referred_by_client_id',
        'channel',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'referred_by_client_id');
    }
}

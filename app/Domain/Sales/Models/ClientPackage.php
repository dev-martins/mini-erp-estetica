<?php

namespace App\Domain\Sales\Models;

use App\Domain\Clients\Models\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'package_id',
        'sale_id',
        'purchased_at',
        'remaining_sessions',
        'expiry_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'expiry_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}

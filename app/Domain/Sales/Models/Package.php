<?php

namespace App\Domain\Sales\Models;

use App\Domain\Services\Models\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'service_id',
        'sessions_count',
        'min_interval_hours',
        'price',
        'expiry_days',
        'description',
        'active',
    ];

    protected $casts = [
        'sessions_count' => 'integer',
        'min_interval_hours' => 'integer',
        'price' => 'float',
        'active' => 'boolean',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function clientPackages(): HasMany
    {
        return $this->hasMany(ClientPackage::class);
    }
}

<?php

namespace App\Domain\Services\Models;

use App\Domain\Appointments\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'duration_min',
        'min_interval_hours',
        'list_price',
        'kit_id',
        'active',
        'description',
    ];

    protected $casts = [
        'duration_min' => 'integer',
        'min_interval_hours' => 'integer',
        'list_price' => 'float',
        'active' => 'boolean',
    ];

    public function kit(): BelongsTo
    {
        return $this->belongsTo(ServiceKit::class, 'kit_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}

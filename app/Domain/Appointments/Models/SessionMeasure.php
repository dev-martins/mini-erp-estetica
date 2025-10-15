<?php

namespace App\Domain\Appointments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionMeasure extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'metric',
        'value',
        'unit',
    ];

    protected $casts = [
        'value' => 'float',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}

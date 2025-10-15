<?php

namespace App\Domain\Appointments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'path',
        'type',
        'consent_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}

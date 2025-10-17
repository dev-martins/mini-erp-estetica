<?php

namespace App\Domain\Appointments\Models;

use App\Domain\Clients\Models\Client;
use App\Domain\Services\Models\Service;
use App\Domain\Sales\Models\ClientPackage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'professional_id',
        'room_id',
        'equipment_id',
        'service_id',
        'client_package_id',
        'package_session_number',
        'scheduled_at',
        'duration_min',
        'status',
        'source',
        'notes',
        'started_at',
        'ended_at',
        'confirmation_token',
        'cancellation_reason',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'package_session_number' => 'integer',
        'attendance_alerted_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function clientPackage(): BelongsTo
    {
        return $this->belongsTo(ClientPackage::class);
    }

    public function sessionItems(): HasMany
    {
        return $this->hasMany(SessionItem::class);
    }

    public function sessionPhotos(): HasMany
    {
        return $this->hasMany(SessionPhoto::class);
    }

    public function sessionMeasures(): HasMany
    {
        return $this->hasMany(SessionMeasure::class);
    }
}

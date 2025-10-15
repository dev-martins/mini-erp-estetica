<?php

namespace App\Domain\Sales\Models;

use App\Domain\Appointments\Models\Appointment;
use App\Domain\Clients\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'appointment_id',
        'processed_by',
        'sold_at',
        'subtotal',
        'discount_total',
        'total_amount',
        'channel',
        'source',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'subtotal' => 'float',
        'discount_total' => 'float',
        'total_amount' => 'float',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}

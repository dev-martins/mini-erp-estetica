<?php

namespace App\Domain\Finance\Models;

use App\Domain\Appointments\Models\Professional;
use App\Domain\Sales\Models\Sale;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'professional_id',
        'sale_id',
        'amount',
        'status',
        'calculated_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'calculated_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}

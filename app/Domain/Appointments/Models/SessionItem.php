<?php

namespace App\Domain\Appointments\Models;

use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Models\ProductBatch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'product_id',
        'batch_id',
        'quantity_used',
    ];

    protected $casts = [
        'quantity_used' => 'float',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }
}

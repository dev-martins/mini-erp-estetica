<?php

namespace App\Domain\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'item_type',
        'item_id',
        'qty',
        'unit_price',
        'discount',
        'total',
    ];

    protected $casts = [
        'qty' => 'float',
        'unit_price' => 'float',
        'discount' => 'float',
        'total' => 'float',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}

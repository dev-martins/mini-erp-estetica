<?php

namespace App\Domain\Services\Models;

use App\Domain\Inventory\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'kit_id',
        'product_id',
        'qty_per_session',
    ];

    protected $casts = [
        'qty_per_session' => 'float',
    ];

    public function kit(): BelongsTo
    {
        return $this->belongsTo(ServiceKit::class, 'kit_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

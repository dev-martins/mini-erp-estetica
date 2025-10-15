<?php

namespace App\Domain\Services\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceKit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'notes',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(KitItem::class, 'kit_id');
    }
}

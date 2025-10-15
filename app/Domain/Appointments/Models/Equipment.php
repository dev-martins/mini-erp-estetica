<?php

namespace App\Domain\Appointments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipments';

    protected $fillable = [
        'name',
        'serial',
        'maint_cycle_days',
        'last_maintenance_at',
    ];

    protected $casts = [
        'maint_cycle_days' => 'integer',
        'last_maintenance_at' => 'date',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}

<?php

namespace App\Domain\Clients\Models;

use App\Domain\Appointments\Models\Appointment;
use App\Domain\Clients\Models\Lead;
use App\Domain\Clients\Models\Referral;
use App\Domain\Sales\Models\ClientPackage;
use App\Domain\Sales\Models\Sale;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'birthdate',
        'instagram',
        'consent_marketing',
        'source',
        'last_appointment_at',
        'tags',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'consent_marketing' => 'boolean',
        'last_appointment_at' => 'datetime',
        'tags' => 'array',
    ];

    protected function firstName(): Attribute
    {
        return Attribute::get(fn () => explode(' ', (string) $this->full_name)[0] ?? $this->full_name);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function clientPackages(): HasMany
    {
        return $this->hasMany(ClientPackage::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }
}

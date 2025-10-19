<?php

namespace App\Domain\Clients\Models;

use App\Domain\Appointments\Models\Appointment;
use App\Domain\Clients\Models\Lead;
use App\Domain\Clients\Models\Referral;
use App\Domain\Sales\Models\ClientPackage;
use App\Domain\Sales\Models\Sale;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Client extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'password',
        'birthdate',
        'instagram',
        'consent_marketing',
        'source',
        'last_appointment_at',
        'tags',
        'verification_channels',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'consent_marketing' => 'boolean',
        'last_appointment_at' => 'datetime',
        'tags' => 'array',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
        'verification_channels' => 'array',
        'password' => 'hashed',
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

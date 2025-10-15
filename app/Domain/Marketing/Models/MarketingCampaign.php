<?php

namespace App\Domain\Marketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'channel',
        'budget',
        'starts_at',
        'ends_at',
        'utm_source',
        'utm_campaign',
        'status',
    ];

    protected $casts = [
        'budget' => 'float',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(CampaignEvent::class, 'campaign_id');
    }
}

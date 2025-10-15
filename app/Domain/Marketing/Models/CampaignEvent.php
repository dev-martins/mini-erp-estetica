<?php

namespace App\Domain\Marketing\Models;

use App\Domain\Clients\Models\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'client_id',
        'event',
        'at',
        'meta_json',
    ];

    protected $casts = [
        'at' => 'datetime',
        'meta_json' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}

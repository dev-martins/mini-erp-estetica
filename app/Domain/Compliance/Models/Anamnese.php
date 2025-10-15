<?php

namespace App\Domain\Compliance\Models;

use App\Domain\Clients\Models\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anamnese extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'form_json',
        'signed_at',
        'signer_name',
        'signature_path',
    ];

    protected $casts = [
        'form_json' => 'array',
        'signed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}

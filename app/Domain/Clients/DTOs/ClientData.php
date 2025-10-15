<?php

namespace App\Domain\Clients\DTOs;

use App\Domain\Clients\Models\Client;
use Illuminate\Http\Request;

class ClientData
{
    public function __construct(
        public readonly string $fullName,
        public readonly string $phone,
        public readonly ?string $email,
        public readonly ?string $birthdate,
        public readonly ?string $instagram,
        public readonly bool $consentMarketing,
        public readonly ?string $source,
        public readonly ?array $tags,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            fullName: (string) $request->input('full_name'),
            phone: (string) $request->input('phone'),
            email: $request->input('email'),
            birthdate: $request->input('birthdate'),
            instagram: $request->input('instagram'),
            consentMarketing: (bool) $request->boolean('consent_marketing'),
            source: $request->input('source'),
            tags: $request->input('tags'),
        );
    }

    public static function fromModel(Client $client): self
    {
        return new self(
            fullName: $client->full_name,
            phone: $client->phone,
            email: $client->email,
            birthdate: optional($client->birthdate)?->format('Y-m-d'),
            instagram: $client->instagram,
            consentMarketing: (bool) $client->consent_marketing,
            source: $client->source,
            tags: $client->tags,
        );
    }

    public function toArray(): array
    {
        return [
            'full_name' => $this->fullName,
            'phone' => $this->phone,
            'email' => $this->email,
            'birthdate' => $this->birthdate,
            'instagram' => $this->instagram,
            'consent_marketing' => $this->consentMarketing,
            'source' => $this->source,
            'tags' => $this->tags,
        ];
    }
}

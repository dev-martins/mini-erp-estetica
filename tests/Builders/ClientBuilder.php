<?php

declare(strict_types=1);

namespace Tests\Builders;

use App\Domain\Clients\DTOs\ClientData;
use App\Domain\Clients\Models\Client;
use App\Domain\Clients\Services\ClientService;
use Carbon\CarbonImmutable;
use Faker\Factory as FakerFactory;
use Faker\Generator;

class ClientBuilder
{
    private readonly ClientService $service;

    private readonly Generator $faker;

    private array $overrides = [];

    private bool $markVerified = false;

    private ?CarbonImmutable $emailVerifiedAt = null;

    public function __construct(?ClientService $service = null, ?Generator $faker = null)
    {
        $this->service = $service ?? app(ClientService::class);
        $this->faker = $faker ?? FakerFactory::create('pt_BR');
    }

    public static function new(): self
    {
        return new self();
    }

    public function with(array $attributes): self
    {
        $clone = clone $this;
        $clone->overrides = array_merge($this->overrides, $attributes);

        return $clone;
    }

    public function verified(?CarbonImmutable $timestamp = null): self
    {
        $clone = clone $this;
        $clone->markVerified = true;
        $clone->emailVerifiedAt = $timestamp;

        return $clone;
    }

    public function create(): Client
    {
        $data = $this->makeData($this->overrides);

        $client = $this->service->create($data);

        if ($this->markVerified) {
            $client->forceFill([
                'email_verified_at' => ($this->emailVerifiedAt ?? now())->toDateTimeString(),
            ])->save();
        }

        return $client->fresh();
    }

    private function makeData(array $overrides): ClientData
    {
        $faker = $this->faker;

        return new ClientData(
            fullName: $overrides['full_name'] ?? $faker->name(),
            phone: $overrides['phone'] ?? $faker->unique()->numerify('5598########'),
            email: $overrides['email'] ?? $faker->unique()->safeEmail(),
            birthdate: $overrides['birthdate'] ?? null,
            instagram: $overrides['instagram'] ?? null,
            consentMarketing: (bool) ($overrides['consent_marketing'] ?? true),
            source: $overrides['source'] ?? 'tests',
            tags: $overrides['tags'] ?? null,
            password: $overrides['password'] ?? 'SenhaSegura123'
        );
    }
}

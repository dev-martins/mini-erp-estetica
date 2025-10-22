<?php

namespace App\Domain\Clients\Services;

use App\Domain\Clients\DTOs\ClientData;
use App\Domain\Clients\Models\Client;
use App\Domain\Clients\Repositories\ClientRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ClientService
{
    public function __construct(private readonly ClientRepository $repository)
    {
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function create(ClientData $data): Client
    {
        $client = $this->repository->create($data);

        $this->resetVerifications($client);

        return $client->refresh();
    }

    public function findWithTrashed(int $clientId): Client
    {
        return $this->repository->findWithTrashed($clientId);
    }

    public function update(Client $client, ClientData $data): Client
    {
        $originalEmail = $client->email;
        $originalPhone = $client->phone;

        $updated = $this->repository->update($client, $data)->refresh();

        $emailChanged = $this->contactChanged($originalEmail, $updated->email);
        $phoneChanged = $this->contactChanged($originalPhone, $updated->phone);

        if ($emailChanged || $phoneChanged) {
            $this->resetVerifications($updated, resetEmail: $emailChanged, resetPhone: $phoneChanged);
        }

        return $updated;
    }

    public function delete(Client $client): void
    {
        $this->repository->delete($client);
    }

    public function changeStatus(Client $client, string $status): Client
    {
        if ($status === 'inactive') {
            $this->repository->deactivate($client);
        } else {
            $this->repository->activate($client);
        }

        return $this->repository->findWithTrashed($client->id);
    }

    public function verifyContact(Client $client, string $channel, string $code): Client
    {
        $channel = strtolower($channel);

        if (! in_array($channel, ['email', 'phone'], true)) {
            throw ValidationException::withMessages([
                'channel' => 'Canal de verificação inválido.',
            ]);
        }

        $contactValue = $channel === 'email' ? $client->email : $client->phone;

        if (! $contactValue) {
            throw ValidationException::withMessages([
                'channel' => 'O canal informado não está disponível para este cliente.',
            ]);
        }

        $pendingChannels = collect($client->verification_channels ?? []);
        if (! $pendingChannels->contains($channel)) {
            throw ValidationException::withMessages([
                'channel' => 'O canal informado já foi validado.',
            ]);
        }

        if (! $client->verification_code || ! $client->verification_code_expires_at) {
            throw ValidationException::withMessages([
                'code' => 'Não há código de verificação ativo.',
            ]);
        }

        if ($client->verification_code_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => 'O código de verificação expirou. Solicite um novo código.',
            ]);
        }

        if (! Hash::check($code, $client->verification_code)) {
            throw ValidationException::withMessages([
                'code' => 'Código de verificação inválido.',
            ]);
        }

        $attributes = [];

        if ($channel === 'email') {
            $attributes['email_verified_at'] = Carbon::now();
        }

        if ($channel === 'phone') {
            $attributes['phone_verified_at'] = Carbon::now();
        }

        $remainingChannels = $pendingChannels
            ->reject(fn (string $value) => $value === $channel)
            ->values();

        if ($remainingChannels->isEmpty()) {
            $attributes['verification_code'] = null;
            $attributes['verification_code_expires_at'] = null;
        }

        $attributes['verification_channels'] = $remainingChannels->all();

        $client->forceFill($attributes)->save();

        return $client->refresh();
    }

    public function regenerateVerificationCode(Client $client): ?string
    {
        return $this->resetVerifications($client, forceGenerate: true);
    }

    private function resetVerifications(
        Client $client,
        bool $forceGenerate = false,
        bool $resetEmail = true,
        bool $resetPhone = true
    ): ?string {
        $pendingChannels = collect($client->verification_channels ?? [])
            ->filter(fn ($channel) => in_array($channel, ['email', 'phone'], true));

        $channels = collect();

        if ($resetEmail && $client->email) {
            $channels->push('email');
            $client->email_verified_at = null;
        } elseif ($client->email_verified_at && $client->email) {
            $channels->push('email');
        }

        if ($resetPhone && $client->phone) {
            $channels->push('phone');
            $client->phone_verified_at = null;
        } elseif ($client->phone_verified_at && $client->phone) {
            $channels->push('phone');
        }

        $channels = $channels->merge($pendingChannels)->unique()->values();

        if ($channels->isEmpty()) {
            $client->forceFill([
                'verification_code' => null,
                'verification_code_expires_at' => null,
                'verification_channels' => [],
            ])->save();

            return null;
        }

        if (! $forceGenerate && $client->verification_code && $client->verification_code_expires_at?->isFuture()) {
            $client->forceFill([
                'verification_channels' => $channels->all(),
            ])->save();

            return null;
        }

        $code = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);

        $client->forceFill([
            'verification_code' => Hash::make($code),
            'verification_code_expires_at' => Carbon::now()->addMinutes(30),
            'verification_channels' => $channels->all(),
        ])->save();

        return $code;
    }

    private function contactChanged(?string $original, ?string $current): bool
    {
        return trim((string) $original) !== trim((string) $current);
    }
}

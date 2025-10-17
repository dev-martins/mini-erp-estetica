<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\Models\ClientPackage;
use App\Domain\Services\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientPackageService
{
    /**
     * Reserve one package session for a scheduled appointment.
     *
     * @return array{0: ClientPackage, 1: int}
     */
    public function reserveForAppointment(
        int $clientPackageId,
        int $clientId,
        int $serviceId,
        CarbonImmutable $scheduledAt
    ): array {
        return DB::transaction(function () use ($clientPackageId, $clientId, $serviceId, $scheduledAt): array {
            /** @var ClientPackage|null $clientPackage */
            $clientPackage = ClientPackage::query()
                ->with(['package.service.kit.items.product'])
                ->where('id', $clientPackageId)
                ->where('client_id', $clientId)
                ->lockForUpdate()
                ->first();

            if (! $clientPackage) {
                throw new ModelNotFoundException('Pacote nao encontrado para o cliente informado.');
            }

            $package = $clientPackage->package;
            if (! $package) {
                throw ValidationException::withMessages([
                    'client_package_id' => 'Pacote associado invalido ou foi removido.',
                ]);
            }

            if ($package->service_id !== $serviceId) {
                throw ValidationException::withMessages([
                    'client_package_id' => 'O pacote selecionado nao esta vinculado ao servico escolhido.',
                ]);
            }

            if ($clientPackage->remaining_sessions < 1) {
                throw ValidationException::withMessages([
                    'client_package_id' => 'Nao ha sessoes disponiveis neste pacote.',
                ]);
            }

            if ($clientPackage->expiry_at && $scheduledAt->greaterThan($clientPackage->expiry_at)) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'A data selecionada ultrapassa a validade do pacote.',
                ]);
            }

            $remainingBefore = $clientPackage->remaining_sessions;
            $clientPackage->remaining_sessions = $remainingBefore - 1;
            $clientPackage->save();

            $sessionNumber = ($package->sessions_count ?? $remainingBefore) - $clientPackage->remaining_sessions;

            return [
                $clientPackage->fresh(['package.service.kit.items.product']),
                $sessionNumber,
            ];
        });
    }

    public function releaseSession(ClientPackage $clientPackage): void
    {
        DB::transaction(function () use ($clientPackage): void {
            /** @var ClientPackage $locked */
            $locked = ClientPackage::query()
                ->whereKey($clientPackage->id)
                ->lockForUpdate()
                ->firstOrFail();

            $locked->increment('remaining_sessions');
            $clientPackage->refresh();
        });
    }

    public function reReserveSession(ClientPackage $clientPackage): void
    {
        DB::transaction(function () use ($clientPackage): void {
            /** @var ClientPackage $locked */
            $locked = ClientPackage::query()
                ->whereKey($clientPackage->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->remaining_sessions < 1) {
                throw ValidationException::withMessages([
                    'client_package_id' => 'O pacote nao possui sessoes suficientes para reativar o agendamento.',
                ]);
            }

            $locked->decrement('remaining_sessions');
            $clientPackage->refresh();
        });
    }

    public function ensureServiceStock(Service $service): void
    {
        $kit = $service->kit;
        if (! $kit) {
            return;
        }

        $insufficientProducts = $kit->items
            ->filter(fn ($item) => $item->product && $item->product->current_stock < $item->qty_per_session)
            ->map(function ($item) {
                return sprintf(
                    '%s (necessario %.3f %s, disponivel %.3f %s)',
                    $item->product->name,
                    $item->qty_per_session,
                    $item->product->unit,
                    $item->product->current_stock,
                    $item->product->unit
                );
            })
            ->values();

        if ($insufficientProducts->isNotEmpty()) {
            throw ValidationException::withMessages([
                'service_id' => 'Estoque insuficiente para o kit do servico: ' . $insufficientProducts->implode('; '),
            ]);
        }
    }
}

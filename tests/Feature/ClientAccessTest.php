<?php

namespace Tests\Feature;

use App\Domain\Appointments\Models\Appointment;
use App\Domain\Appointments\Models\Professional;
use App\Domain\Clients\DTOs\ClientData;
use App\Domain\Clients\Models\Client;
use App\Domain\Clients\Services\ClientService;
use App\Domain\Sales\Models\ClientPackage;
use App\Domain\Sales\Models\Package;
use App\Domain\Services\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ClientAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_registers_and_receives_verification_code(): void
    {
        $response = $this->postJson('/api/client/auth/register', [
            'full_name' => 'Ana Souza',
            'email' => 'ana@example.com',
            'password' => 'senhaSegura123',
            'consent_marketing' => true,
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'message',
            'client' => ['id', 'full_name', 'email'],
            'pending_channels',
            'verification_code',
        ]);

        $client = Client::where('email', 'ana@example.com')->first();

        $this->assertNotNull($client);
        $this->assertEquals('Ana Souza', $client->full_name);
        $this->assertTrue(Hash::check('senhaSegura123', $client->password));
        $this->assertContains('email', $client->verification_channels ?? []);
        $this->assertNull($client->email_verified_at);
    }

    public function test_client_can_verify_contact_and_login(): void
    {
        $service = app(ClientService::class);

        $client = $service->create($this->makeClientData([
            'email' => 'bianca@example.com',
            'password' => 'senhaForte123',
        ]));

        $code = $service->regenerateVerificationCode($client);

        $verify = $this->postJson('/api/client/auth/verify', [
            'channel' => 'email',
            'code' => $code,
            'login' => 'bianca@example.com',
        ]);

        $verify->assertOk();
        $this->assertNotNull($client->fresh()->email_verified_at);

        $login = $this->postJson('/api/client/auth/login', [
            'login' => 'bianca@example.com',
            'password' => 'senhaForte123',
        ]);

        $login->assertOk();
        $login->assertJsonStructure([
            'token_type',
            'access_token',
            'client' => ['id', 'email'],
        ]);

        $token = $login->json('access_token');

        $me = $this->withToken($token)->getJson('/api/client/auth/me');
        $me->assertOk();
        $me->assertJsonFragment(['email' => 'bianca@example.com']);
    }

    public function test_client_cannot_schedule_without_available_sessions(): void
    {
        $service = app(ClientService::class);
        $client = $service->create($this->makeClientData([
            'email' => 'juliana@example.com',
            'password' => 'senhaForte123',
        ]));

        $code = $service->regenerateVerificationCode($client);
        $this->postJson('/api/client/auth/verify', [
            'channel' => 'email',
            'code' => $code,
            'login' => 'juliana@example.com',
        ]);

        $token = $this->loginClient('juliana@example.com', 'senhaForte123');

        $serviceModel = Service::create([
            'name' => 'Limpeza de pele',
            'category' => 'Estética facial',
            'duration_min' => 60,
            'min_interval_hours' => 48,
            'list_price' => 180,
            'active' => true,
        ]);

        $package = Package::create([
            'name' => 'Pacote Limpeza Premium',
            'service_id' => $serviceModel->id,
            'sessions_count' => 5,
            'min_interval_hours' => 48,
            'price' => 750,
            'expiry_days' => 120,
            'active' => true,
        ]);

        $clientPackage = ClientPackage::create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'purchased_at' => now()->subWeek(),
            'remaining_sessions' => 0,
            'expiry_at' => now()->addMonths(4),
        ]);

        $response = $this->withToken($token)->postJson('/api/client/appointments', [
            'client_package_id' => $clientPackage->id,
            'service_id' => $serviceModel->id,
            'professional_id' => $this->makeProfessional()->id,
            'scheduled_at' => now()->addDays(5)->toIso8601String(),
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('client_package_id');
    }

    public function test_client_can_schedule_reschedule_and_cancel(): void
    {
        $service = app(ClientService::class);
        $client = $service->create($this->makeClientData([
            'email' => 'rafaela@example.com',
            'password' => 'senhaForte123',
        ]));

        $verificationCode = $service->regenerateVerificationCode($client);
        $this->postJson('/api/client/auth/verify', [
            'channel' => 'email',
            'code' => $verificationCode,
            'login' => 'rafaela@example.com',
        ]);

        $token = $this->loginClient('rafaela@example.com', 'senhaForte123');

        $serviceModel = Service::create([
            'name' => 'Drenagem linfática',
            'category' => 'Massagem',
            'duration_min' => 90,
            'min_interval_hours' => 72,
            'list_price' => 220,
            'active' => true,
        ]);

        $package = Package::create([
            'name' => 'Pacote Drenagem 5 sessões',
            'service_id' => $serviceModel->id,
            'sessions_count' => 5,
            'min_interval_hours' => 72,
            'price' => 950,
            'expiry_days' => 150,
            'active' => true,
        ]);

        $clientPackage = ClientPackage::create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'purchased_at' => now()->subDays(2),
            'remaining_sessions' => 5,
            'expiry_at' => now()->addMonths(5),
        ]);

        $professional = $this->makeProfessional();

        $scheduleResponse = $this->withToken($token)->postJson('/api/client/appointments', [
            'client_package_id' => $clientPackage->id,
            'service_id' => $serviceModel->id,
            'professional_id' => $professional->id,
            'scheduled_at' => now()->addDays(7)->setHour(10)->format(DATE_ATOM),
            'duration_min' => 90,
            'notes' => 'Primeira sessão agendada pelo aplicativo.',
        ]);

        $scheduleResponse->assertCreated();
        $appointmentId = $scheduleResponse->json('data.id');

        $rescheduleResponse = $this->withToken($token)->postJson(
            "/api/client/appointments/{$appointmentId}/reschedule",
            [
                'scheduled_at' => now()->addDays(14)->setHour(11)->format(DATE_ATOM),
                'notes' => 'Remarcado por conta de viagem.',
            ]
        );

        $rescheduleResponse->assertOk();
        $this->assertEquals('pending', Appointment::find($appointmentId)?->status);

        $cancelResponse = $this->withToken($token)
            ->postJson("/api/client/appointments/{$appointmentId}/cancel");

        $cancelResponse->assertOk();
        $this->assertEquals('cancelled', Appointment::find($appointmentId)?->status);
    }

    private function makeClientData(array $overrides = []): ClientData
    {
        $faker = fake();

        return new ClientData(
            fullName: $overrides['full_name'] ?? $faker->name(),
            phone: array_key_exists('phone', $overrides)
                ? $overrides['phone']
                : $faker->unique()->numerify('5598########'),
            email: $overrides['email'] ?? $faker->unique()->safeEmail(),
            birthdate: null,
            instagram: null,
            consentMarketing: (bool) ($overrides['consent_marketing'] ?? true),
            source: $overrides['source'] ?? 'test',
            tags: null,
            password: $overrides['password'] ?? 'senhaSegura123',
        );
    }

    private function loginClient(string $login, string $password): string
    {
        $response = $this->postJson('/api/client/auth/login', [
            'login' => $login,
            'password' => $password,
        ]);

        $response->assertOk();

        return $response->json('access_token');
    }

    private function makeProfessional(): Professional
    {
        $user = User::factory()->create([
            'password' => 'password',
            'role' => 'professional',
        ]);

        return Professional::create([
            'user_id' => $user->id,
            'display_name' => 'Equipe Teste',
            'specialty' => 'Estética',
            'commission_type' => 'percentage',
            'commission_value' => 30,
            'work_hours' => [
                'monday' => ['08:00-12:00', '13:00-18:00'],
            ],
            'active' => true,
        ]);
    }
}

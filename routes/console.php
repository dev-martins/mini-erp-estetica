<?php

use App\Domain\Appointments\Services\AttendanceAlertService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('appointments:attendance-alerts', function (AttendanceAlertService $service) {
    $count = $service->dispatch();
    $this->info("Alertas de atendimento enviados: {$count}");
})->purpose('Notificar atendimentos sem confirmação de comparecimento após 24h.');

Schedule::command('appointments:attendance-alerts')->hourly();

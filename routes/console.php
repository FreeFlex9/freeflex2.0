<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:desbloquear-usuarios-expirados')->everyMinute();
Schedule::command('app:detectar-faltas')->everyFifteenMinutes();
Schedule::command('app:aplicar-penalidade-faltas')->everyFifteenMinutes();

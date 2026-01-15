<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar generación automática de envíos cada 15 días
Schedule::command('envios:generar-automatico')
    ->twiceMonthly(15, 1, '23:00') // Día 15 a las 11pm y día 1 a las 11pm
    ->timezone('America/Mexico_City') // Ajustar a tu zona horaria
    ->withoutOverlapping()
    ->onSuccess(function () {
        info('Envío automático generado exitosamente');
    })
    ->onFailure(function () {
        logger()->error('Error al generar envío automático');
    });


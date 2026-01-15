<?php

namespace App\Console\Commands;

use App\Models\Envio;
use App\Models\Venta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerarEnviosAutomaticos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'envios:generar-automatico
                            {--fecha= : Fecha específica para generar el envío (formato: Y-m-d)}
                            {--force : Forzar la creación aunque ya exista un envío para el periodo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera automáticamente envíos cada 15 días (del 1-15 y del 16-fin de mes) con las ventas que tienen envío en ese periodo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando generación automática de envíos...');
        
        // Determinar la fecha de referencia
        $fechaReferencia = $this->option('fecha') 
            ? Carbon::parse($this->option('fecha'))
            : Carbon::now();
        
        // Determinar el periodo (primera o segunda quincena)
        $periodos = $this->determinarPeriodo($fechaReferencia);
        
        $this->info("📅 Fecha de referencia: {$fechaReferencia->format('d/m/Y')}");
        $this->info("📦 Periodo: {$periodos['nombre']}");
        $this->info("📆 Rango: {$periodos['inicio']->format('d/m/Y')} - {$periodos['fin']->format('d/m/Y')}");
        $this->newLine();
        
        // Verificar si ya existe un envío automático para este periodo
        if (!$this->option('force')) {
            $envioExistente = Envio::where('tipo_generacion', 'automatico')
                ->where('periodo_inicio', $periodos['inicio'])
                ->where('periodo_fin', $periodos['fin'])
                ->first();
            
            if ($envioExistente) {
                $this->warn("⚠️  Ya existe un envío automático para este periodo (ID: {$envioExistente->id})");
                $this->info("💡 Usa la opción --force para crear otro envío del mismo periodo");
                return Command::FAILURE;
            }
        }
        
        // Buscar ventas con envío en el periodo que NO estén en ningún envío
        $ventas = $this->obtenerVentasDelPeriodo($periodos['inicio'], $periodos['fin']);
        
        if ($ventas->isEmpty()) {
            $this->warn('⚠️  No hay ventas con envío pendientes en este periodo.');
            return Command::SUCCESS;
        }
        
        $this->info("📋 Ventas encontradas: {$ventas->count()}");
        $this->newLine();
        
        // Mostrar resumen de ventas
        $this->mostrarResumenVentas($ventas);
        
        // Calcular monto total de envío
        $montoTotal = $ventas->sum('costo_envio');
        
        // Crear el envío automático
        DB::beginTransaction();
        try {
            $envio = Envio::create([
                'guia' => $this->generarGuia($periodos),
                'fecha_envio' => Carbon::now(),
                'monto_a_pagar' => $montoTotal,
                'estado_pago' => 'pendiente',
                'tipo_generacion' => 'automatico',
                'periodo_inicio' => $periodos['inicio'],
                'periodo_fin' => $periodos['fin'],
                'notas' => "Envío generado automáticamente para el periodo {$periodos['nombre']} ({$periodos['inicio']->format('d/m/Y')} - {$periodos['fin']->format('d/m/Y')})",
                'usuario' => 'Sistema (Automático)',
            ]);
            
            // Asociar las ventas al envío
            $envio->ventas()->attach($ventas->pluck('id'));
            
            DB::commit();
            
            $this->newLine();
            $this->info('✅ Envío creado exitosamente!');
            $this->info("🆔 ID del envío: {$envio->id}");
            $this->info("📦 Guía: {$envio->guia}");
            $this->info("💰 Monto total: $" . number_format($montoTotal, 2));
            $this->info("📊 Ventas asociadas: {$ventas->count()}");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error al crear el envío: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    /**
     * Determinar el periodo (primera o segunda quincena)
     */
    private function determinarPeriodo(Carbon $fecha): array
    {
        $dia = $fecha->day;
        
        if ($dia <= 15) {
            // Primera quincena: del 1 al 15
            return [
                'nombre' => 'Primera Quincena',
                'inicio' => $fecha->copy()->startOfMonth(),
                'fin' => $fecha->copy()->day(15)->endOfDay(),
            ];
        } else {
            // Segunda quincena: del 16 al fin de mes
            return [
                'nombre' => 'Segunda Quincena',
                'inicio' => $fecha->copy()->day(16)->startOfDay(),
                'fin' => $fecha->copy()->endOfMonth(),
            ];
        }
    }
    
    /**
     * Obtener ventas con envío del periodo que NO estén en ningún envío
     */
    private function obtenerVentasDelPeriodo(Carbon $inicio, Carbon $fin)
    {
        return Venta::where('tiene_envio', true)
            ->where('estado', '!=', 'cancelada')
            ->whereBetween('fecha_venta', [$inicio, $fin])
            ->whereDoesntHave('envios') // Solo ventas que NO estén en ningún envío
            ->with(['cliente', 'movimientos'])
            ->orderBy('fecha_venta', 'asc')
            ->get();
    }
    
    /**
     * Generar código de guía automático
     */
    private function generarGuia(array $periodos): string
    {
        $fecha = $periodos['inicio'];
        $quincena = $periodos['nombre'] === 'Primera Quincena' ? 'Q1' : 'Q2';
        $mes = $fecha->format('m');
        $anio = $fecha->format('Y');
        
        // Formato: ENV-YYYYMM-Q1/Q2-001
        $prefijo = "ENV-{$anio}{$mes}-{$quincena}";
        
        // Buscar el último envío con este prefijo
        $ultimoEnvio = Envio::where('guia', 'like', "{$prefijo}-%")
            ->orderBy('guia', 'desc')
            ->first();
        
        if ($ultimoEnvio) {
            // Extraer el número y sumar 1
            $partes = explode('-', $ultimoEnvio->guia);
            $numero = intval(end($partes)) + 1;
        } else {
            $numero = 1;
        }
        
        return $prefijo . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Mostrar resumen de las ventas encontradas
     */
    private function mostrarResumenVentas($ventas)
    {
        $this->info('📝 Resumen de ventas:');
        $this->table(
            ['ID Venta', 'Fecha', 'Cliente', 'Costo Envío'],
            $ventas->map(function ($venta) {
                return [
                    $venta->id,
                    $venta->fecha_venta->format('d/m/Y'),
                    $venta->cliente ? $venta->cliente->nombre : 'Sin cliente',
                    '$' . number_format($venta->costo_envio, 2),
                ];
            })->toArray()
        );
        $this->newLine();
    }
}

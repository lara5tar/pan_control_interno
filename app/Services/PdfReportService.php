<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Servicio para generar reportes PDF reutilizables
 * 
 * Proporciona funcionalidades comunes para todos los reportes PDF:
 * - Encabezados consistentes
 * - Estilos uniformes
 * - Pie de página con información del sistema
 * - Manejo de filtros aplicados
 */
class PdfReportService
{
    /**
     * Colores del tema para reportes
     */
    private const COLORS = [
        'PRIMARY' => '#1F2937',      // gray-800
        'SECONDARY' => '#6B7280',    // gray-500
        'SUCCESS' => '#10B981',      // green-500
        'DANGER' => '#EF4444',       // red-500
        'WARNING' => '#F59E0B',      // amber-500
        'INFO' => '#3B82F6',         // blue-500
        'LIGHT_BG' => '#F5F5F5',     // gray-100
        'BORDER' => '#D1D5DB',       // gray-300
    ];

    /**
     * Genera estilos CSS base para reportes PDF
     * 
     * @return string CSS con estilos comunes
     */
    public function getBaseStyles(): string
    {
        return "
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 20px;
            color: " . self::COLORS['PRIMARY'] . ";
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid " . self::COLORS['PRIMARY'] . ";
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            text-transform: uppercase;
            color: " . self::COLORS['PRIMARY'] . ";
        }
        
        .header p {
            font-size: 10px;
            color: " . self::COLORS['SECONDARY'] . ";
        }
        
        .filters {
            background-color: " . self::COLORS['LIGHT_BG'] . ";
            padding: 10px;
            margin-bottom: 20px;
            border-left: 3px solid " . self::COLORS['PRIMARY'] . ";
        }
        
        .filters h3 {
            font-size: 12px;
            margin-bottom: 8px;
            color: " . self::COLORS['PRIMARY'] . ";
        }
        
        .filters ul {
            list-style: none;
            padding-left: 10px;
        }
        
        .filters li {
            font-size: 10px;
            margin-bottom: 3px;
            color: " . self::COLORS['SECONDARY'] . ";
        }
        
        .filters li:before {
            content: '• ';
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        thead {
            background-color: " . self::COLORS['PRIMARY'] . ";
            color: white;
        }
        
        th {
            padding: 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        
        td {
            padding: 6px 8px;
            border-bottom: 1px solid " . self::COLORS['BORDER'] . ";
            font-size: 10px;
        }
        
        tbody tr:nth-child(even) {
            background-color: #F9FAFB;
        }
        
        tbody tr:hover {
            background-color: #F3F4F6;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-left {
            text-align: left;
        }
        
        .badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
        }
        
        .badge-success {
            background-color: #D1FAE5;
            color: #065F46;
        }
        
        .badge-danger {
            background-color: #FEE2E2;
            color: #991B1B;
        }
        
        .badge-warning {
            background-color: #FEF3C7;
            color: #92400E;
        }
        
        .badge-info {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        
        .badge-gray {
            background-color: #F3F4F6;
            color: #374151;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: " . self::COLORS['SECONDARY'] . ";
            border-top: 1px solid " . self::COLORS['BORDER'] . ";
            padding-top: 10px;
        }
        
        .empty {
            text-align: center;
            padding: 40px;
            color: " . self::COLORS['SECONDARY'] . ";
            font-style: italic;
        }
        
        .summary {
            background-color: " . self::COLORS['LIGHT_BG'] . ";
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-weight: bold;
            font-size: 11px;
        }
        
        .summary-value {
            font-size: 11px;
            color: " . self::COLORS['SECONDARY'] . ";
        }
        ";
    }

    /**
     * Genera un reporte PDF con configuración base
     * 
     * @param string $view Vista blade a usar
     * @param array $data Datos para la vista
     * @param string $filename Nombre del archivo PDF
     * @param array $options Opciones adicionales (orientation, paper)
     * @return \Illuminate\Http\Response
     */
    public function generate(string $view, array $data, string $filename, array $options = [])
    {
        $orientation = $options['orientation'] ?? 'portrait'; // portrait o landscape
        $paper = $options['paper'] ?? 'a4';
        
        $pdf = Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation);
        
        return $pdf->download($filename);
    }

    /**
     * Genera el nombre de archivo con timestamp
     * 
     * @param string $prefix Prefijo del archivo
     * @return string Nombre del archivo con fecha
     */
    public function generateFilename(string $prefix): string
    {
        return $prefix . '_' . date('Y-m-d_His') . '.pdf';
    }

    /**
     * Formatea la fecha actual para mostrar en reportes
     * 
     * @return string Fecha formateada
     */
    public function getCurrentDate(): string
    {
        return date('d/m/Y H:i:s');
    }

    /**
     * Genera HTML para el encabezado del reporte
     * 
     * @param string $title Título del reporte
     * @param string|null $subtitle Subtítulo opcional
     * @return string HTML del encabezado
     */
    public function renderHeader(string $title, ?string $subtitle = null): string
    {
        $subtitleHtml = $subtitle ? "<p>{$subtitle}</p>" : '';
        
        return "
        <div class='header'>
            <h1>{$title}</h1>
            <p>Generado el {$this->getCurrentDate()}</p>
            {$subtitleHtml}
        </div>
        ";
    }

    /**
     * Genera HTML para mostrar filtros aplicados
     * 
     * @param array $filters Array de filtros aplicados
     * @return string HTML de filtros
     */
    public function renderFilters(array $filters): string
    {
        if (empty($filters)) {
            return '';
        }
        
        $items = '';
        foreach ($filters as $filter) {
            $items .= "<li>{$filter}</li>";
        }
        
        return "
        <div class='filters'>
            <h3>Filtros Aplicados:</h3>
            <ul>{$items}</ul>
        </div>
        ";
    }

    /**
     * Genera HTML para el pie de página
     * 
     * @param int $totalRecords Total de registros
     * @param string|null $additionalInfo Información adicional
     * @return string HTML del pie de página
     */
    public function renderFooter(int $totalRecords, ?string $additionalInfo = null): string
    {
        $additionalHtml = $additionalInfo ? "<p>{$additionalInfo}</p>" : '';
        
        return "
        <div class='footer'>
            <p>Total de registros: {$totalRecords}</p>
            {$additionalHtml}
            <p>Pan de Vida - Sistema de Control Interno</p>
        </div>
        ";
    }
}

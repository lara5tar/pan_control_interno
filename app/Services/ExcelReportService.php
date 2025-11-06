<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Servicio para generar reportes Excel reutilizables
 * 
 * Proporciona funcionalidades comunes para todos los reportes Excel:
 * - Encabezados con estilos consistentes
 * - Formatos de celdas
 * - Ajuste automático de columnas
 * - Exportación de datos tabulares
 */
class ExcelReportService
{
    /**
     * Colores del tema (RGB sin #)
     */
    private const COLORS = [
        'HEADER_BG' => '1F2937',      // gray-800
        'HEADER_TEXT' => 'FFFFFF',    // white
        'SUBHEADER_BG' => 'E5E7EB',   // gray-200
        'SUBHEADER_TEXT' => '1F2937', // gray-800
        'ACCENT' => '3B82F6',         // blue-500
        'SUCCESS' => '10B981',        // green-500
        'DANGER' => 'EF4444',         // red-500
        'WARNING' => 'F59E0B',        // amber-500
    ];

    /**
     * Crea un nuevo spreadsheet con configuración base
     * 
     * @return Spreadsheet
     */
    public function createSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Pan de Vida')
            ->setTitle('Reporte del Sistema')
            ->setSubject('Reporte Generado Automáticamente')
            ->setDescription('Reporte generado por el Sistema de Control Interno');
        
        return $spreadsheet;
    }

    /**
     * Establece el título principal del reporte
     * 
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param string $title Título del reporte
     * @param string $endColumn Última columna a fusionar (ej: 'E')
     * @param int $row Fila donde colocar el título
     * @return int Siguiente fila disponible
     */
    public function setTitle($sheet, string $title, string $endColumn = 'E', int $row = 1): int
    {
        $sheet->setCellValue('A' . $row, $title);
        $sheet->mergeCells('A' . $row . ':' . $endColumn . $row);
        
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => self::COLORS['HEADER_TEXT']],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLORS['HEADER_BG']],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        $sheet->getRowDimension($row)->setRowHeight(30);
        
        return $row + 1;
    }

    /**
     * Establece una sección de filtros aplicados
     * 
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $filters Array de filtros aplicados
     * @param int $startRow Fila inicial
     * @return int Siguiente fila disponible
     */
    public function setFilters($sheet, array $filters, int $startRow): int
    {
        if (empty($filters)) {
            return $startRow;
        }

        $row = $startRow;
        
        // Encabezado de filtros
        $sheet->setCellValue('A' . $row, 'FILTROS APLICADOS:');
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
        ]);
        $row++;
        
        // Listar filtros
        foreach ($filters as $filter) {
            $sheet->setCellValue('A' . $row, '• ' . $filter);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'font' => [
                    'size' => 10,
                    'color' => ['rgb' => '6B7280'], // gray-500
                ],
            ]);
            $row++;
        }
        
        return $row + 1; // Espacio extra
    }

    /**
     * Establece los encabezados de la tabla
     * 
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $headers Array de encabezados
     * @param int $row Fila donde colocar los encabezados
     * @return int Siguiente fila disponible
     */
    public function setTableHeaders($sheet, array $headers, int $row): int
    {
        $col = 'A';
        
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => self::COLORS['SUBHEADER_TEXT']],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::COLORS['SUBHEADER_BG']],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '9CA3AF'], // gray-400
                    ],
                ],
            ]);
            $col++;
        }
        
        $sheet->getRowDimension($row)->setRowHeight(20);
        
        return $row + 1;
    }

    /**
     * Llena las celdas de datos
     * 
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $data Array de arrays con los datos
     * @param int $startRow Fila inicial
     * @return int Última fila escrita
     */
    public function fillData($sheet, array $data, int $startRow): int
    {
        $row = $startRow;
        
        foreach ($data as $rowData) {
            $col = 'A';
            foreach ($rowData as $value) {
                $sheet->setCellValue($col . $row, $value);
                
                // Aplicar bordes sutiles
                $sheet->getStyle($col . $row)->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'], // gray-200
                        ],
                    ],
                ]);
                
                $col++;
            }
            $row++;
        }
        
        return $row;
    }

    /**
     * Ajusta automáticamente el ancho de las columnas
     * 
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $columns Array de columnas (ej: ['A', 'B', 'C'])
     */
    public function autoSizeColumns($sheet, array $columns): void
    {
        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /**
     * Aplica formato de moneda a una columna
     * 
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param string $column Columna a formatear
     * @param int $startRow Fila inicial
     * @param int $endRow Fila final
     */
    public function formatCurrency($sheet, string $column, int $startRow, int $endRow): void
    {
        $range = $column . $startRow . ':' . $column . $endRow;
        $sheet->getStyle($range)->getNumberFormat()
            ->setFormatCode('$#,##0.00');
    }

    /**
     * Aplica formato de número entero a una columna
     * 
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param string $column Columna a formatear
     * @param int $startRow Fila inicial
     * @param int $endRow Fila final
     */
    public function formatInteger($sheet, string $column, int $startRow, int $endRow): void
    {
        $range = $column . $startRow . ':' . $column . $endRow;
        $sheet->getStyle($range)->getNumberFormat()
            ->setFormatCode('#,##0');
    }

    /**
     * Genera y descarga el archivo Excel
     * 
     * @param Spreadsheet $spreadsheet
     * @param string $filename Nombre del archivo
     */
    public function download(Spreadsheet $spreadsheet, string $filename): void
    {
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Genera el nombre de archivo con timestamp
     * 
     * @param string $prefix Prefijo del archivo
     * @return string Nombre del archivo con fecha
     */
    public function generateFilename(string $prefix): string
    {
        return $prefix . '_' . date('Y-m-d_His') . '.xlsx';
    }

    /**
     * Agrega una fila de resumen al final
     * 
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $summaryData Datos del resumen ['label' => 'Total:', 'value' => 1000]
     * @param int $row Fila donde agregar el resumen
     * @param string $labelColumn Columna para la etiqueta
     * @param string $valueColumn Columna para el valor
     */
    public function addSummaryRow($sheet, array $summaryData, int $row, string $labelColumn = 'A', string $valueColumn = 'E'): void
    {
        $sheet->setCellValue($labelColumn . $row, $summaryData['label'] ?? 'Total:');
        $sheet->setCellValue($valueColumn . $row, $summaryData['value']);
        
        $sheet->getStyle($labelColumn . $row . ':' . $valueColumn . $row)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLORS['SUBHEADER_BG']],
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => self::COLORS['HEADER_BG']],
                ],
            ],
        ]);
    }
}

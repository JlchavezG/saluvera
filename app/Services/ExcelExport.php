<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

/**
 * Genera un archivo Excel (SpreadsheetML) con formato, sin librerias externas.
 */
class ExcelExport
{
    private array $rows = [];
    private array $columnWidths = [];
    private string $sheetName = 'Reporte';

    public function setSheetName(string $name): void
    {
        $this->sheetName = $name;
    }

    public function setColumnWidths(array $widths): void
    {
        $this->columnWidths = $widths;
    }

    public function titulo(string $texto): void
    {
        $this->rows[] = '<Row ss:Height="24"><Cell ss:StyleID="titulo"><Data ss:Type="String">' . self::esc($texto) . '</Data></Cell></Row>';
    }

    public function subtitulo(string $texto): void
    {
        $this->rows[] = '<Row><Cell ss:StyleID="subtitulo"><Data ss:Type="String">' . self::esc($texto) . '</Data></Cell></Row>';
    }

    public function seccion(string $texto): void
    {
        $this->rows[] = '<Row><Cell ss:StyleID="seccion"><Data ss:Type="String">' . self::esc($texto) . '</Data></Cell></Row>';
    }

    public function header(array $columnas): void
    {
        $xml = '<Row>';
        foreach ($columnas as $col) {
            $xml .= '<Cell ss:StyleID="header"><Data ss:Type="String">' . self::esc($col) . '</Data></Cell>';
        }
        $xml .= '</Row>';
        $this->rows[] = $xml;
    }

    public function row(array $cells): void
    {
        $xml = '<Row>';
        foreach ($cells as $c) {
            $valor = $c[0] ?? '';
            $tipo = $c[1] ?? 's';

            if ($tipo === 's') {
                $xml .= '<Cell><Data ss:Type="String">' . self::esc((string) $valor) . '</Data></Cell>';
            } else {
                $styleId = $tipo === 'm' ? 'moneda' : ($tipo === 'p' ? 'porcentaje' : 'numero');
                $xml .= '<Cell ss:StyleID="' . $styleId . '"><Data ss:Type="Number">' . (float) $valor . '</Data></Cell>';
            }
        }
        $xml .= '</Row>';
        $this->rows[] = $xml;
    }

    public function filaVacia(): void
    {
        $this->rows[] = '<Row/>';
    }

    public function build(): string
    {
        $cols = '';
        foreach ($this->columnWidths as $w) {
            $cols .= '<Column ss:Width="' . (int) $w . '"/>';
        }

        $rows = implode("\n", $this->rows);

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<?mso-application progid="Excel.Sheet"?>' . "\n"
            . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n"
            . ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n"
            . ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n"
            . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n"
            . '<Styles>' . "\n"
            . '<Style ss:ID="Default" ss:Name="Normal"><Font ss:FontName="Calibri" ss:Size="11"/><Alignment ss:Vertical="Center"/></Style>' . "\n"
            . '<Style ss:ID="titulo"><Font ss:FontName="Calibri" ss:Size="16" ss:Bold="1" ss:Color="#1C5345"/></Style>' . "\n"
            . '<Style ss:ID="subtitulo"><Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#6B857F"/></Style>' . "\n"
            . '<Style ss:ID="seccion"><Font ss:Bold="1" ss:Color="#1C5345"/><Interior ss:Color="#E8F4F1" ss:Pattern="Solid"/></Style>' . "\n"
            . '<Style ss:ID="header"><Font ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#1C5345" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>' . "\n"
            . '<Style ss:ID="numero"><NumberFormat ss:Format="0"/></Style>' . "\n"
            . '<Style ss:ID="moneda"><NumberFormat ss:Format="$#,##0.00"/></Style>' . "\n"
            . '<Style ss:ID="porcentaje"><NumberFormat ss:Format="0.0&quot;%&quot;"/></Style>' . "\n"
            . '</Styles>' . "\n"
            . '<Worksheet ss:Name="' . self::esc($this->sheetName) . '">' . "\n"
            . '<Table>' . "\n"
            . $cols . "\n"
            . $rows . "\n"
            . '</Table>' . "\n"
            . '</Worksheet>' . "\n"
            . '</Workbook>';
    }

    public function download(string $filename): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $this->build();
        exit;
    }

    public function getSpreadsheet()
    {
        return null;
    }

    private static function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}

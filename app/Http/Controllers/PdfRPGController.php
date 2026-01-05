<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;
use GuzzleHttp\Psr7\Header;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class PdfRPGController extends Controller
{
    protected $fpdf;

    public function __construct()
    {
        $this->fpdf = new Fpdf('L','mm','Legal');	// Formato Legal (Paisaje)
    }

/**
     * Dibuja la tabla de estadísticas (VERSIÓN MODULAR AVANZADA)
     * Acepta coordenadas X, Y, anchos (W), alturas (H) y textos de cabecera.
     *
     * @param Fpdf $fpdf La instancia de FPDF
     * @param array $stats Los datos estadísticos calculados
     * @param float $x La coordenada X (horizontal) para la esquina superior izquierda
     * @param float $y La coordenada Y (vertical) para la esquina superior izquierda
     * @param array $w (Opcional) Array con los anchos de las 6 columnas
     * @param float $h_title (Opcional) Alto de la fila del TÍTULO ("ESTADÍSTICA")
     * @param float $h_header (Opcional) Alto de CADA LÍNEA de la cabecera (ej: 5mm por línea)
     * @param float $h_row (Opcional) Alto de las filas de datos (Masculino, Femenino, Total)
     * @param array $header_texts (Opcional) Array con los textos de la cabecera. Usa "\n" para saltos de línea.
     */
    private function dibujarEstadisticas($fpdf, $stats, $x, $y, $w = null, $h_title = null, $h_header = null, $h_row = null, $header_texts = null)
    {
        // --- INDICADOR: Valores por Defecto ---
        // Puedes cambiar los valores por defecto aquí
        $w_default = [35, 25, 22, 25, 25, 25]; // Anchos de columna (Total: 157mm)
        $h_title_default = 8; // Alto para la celda "ESTADÍSTICA"
        $h_header_line_default = 5; // Alto de CADA línea en la cabecera (ej: "Matrícula\nInicial" usará 10mm total)
        $h_row_default = 6; // Alto para las filas de datos
        $header_texts_default = [
            "SEXO",
            "Matrícula\nInicial",
            "Retirados",
            "Matrícula\nFinal",
            "Promovidos",
            "Retenidos"
        ];
        // --- Fin de Indicadores ---

        // Asigna los valores (si el usuario no los pasa, usa los por defecto)
        $w = $w ?? $w_default;
        $h_title = $h_title ?? $h_title_default;
        $h_header_line = $h_header_line ?? $h_header_line_default;
        $h_row = $h_row ?? $h_row_default;
        $header_texts = $header_texts ?? $header_texts_default;

        // --- Posiciona el cursor en el X,Y que nos diste ---
        $fpdf->SetXY($x, $y);
        $fpdf->SetFont('Arial', 'B', 10);
        $fpdf->Cell(array_sum($w), $h_title, mb_convert_encoding('ESTADÍSTICA', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C'); // Título
        
        $fpdf->SetX($x); // Vuelve al inicio X
        
        $fpdf->SetFont('Arial', 'B', 7);
        $fpdf->SetFillColor(230, 230, 230);

        // --- Cabecera Multi-línea (El truco de FPDF) ---
        // Guardamos la Y actual y la X inicial
        $current_y = $fpdf->GetY();
        $current_x = $x;
        $max_y = $current_y; // Para rastrear la celda más alta
        
        for ($i = 0; $i < count($header_texts); $i++) {
            $fpdf->SetXY($current_x, $current_y); // Fija la posición de esta celda
            
            // Dibuja la MultiCell. El $h_header_line es el alto de CADA línea de texto
            $fpdf->MultiCell($w[$i], $h_header_line, convertirTexto($header_texts[$i]), 1, 'C', true);
            
            // Guarda la Y máxima (la celda más alta, ej: "Matrícula\nInicial")
            if ($fpdf->GetY() > $max_y) {
                $max_y = $fpdf->GetY();
            }
            // Mueve la X para la siguiente celda
            $current_x += $w[$i];
        }
        // Al final, establece la Y para la siguiente fila de datos
        $fpdf->SetY($max_y);
        // --- Fin Cabecera Multi-línea ---
        
        $this->fpdf->SetFont('Arial', '', 7);
        
        // --- Fila Masculino ---
        $fpdf->SetX($x); // Vuelve a la X inicial
        $fpdf->Cell($w[0], $h_row, 'MASCULINO', 1, 0, 'L');
        $fpdf->Cell($w[1], $h_row, $stats['M']['inicial'], 1, 0, 'C');
        $fpdf->Cell($w[2], $h_row, $stats['M']['retirados'], 1, 0, 'C');
        $fpdf->Cell($w[3], $h_row, $stats['M']['final'], 1, 0, 'C');
        $fpdf->Cell($w[4], $h_row, $stats['M']['promovidos'], 1, 0, 'C');
        $fpdf->Cell($w[5], $h_row, $stats['M']['retenidos'], 1, 1, 'C'); // Salto de línea
        
        // --- Fila Femenino ---
        $fpdf->SetX($x); // Vuelve a la X inicial
        $fpdf->Cell($w[0], $h_row, 'FEMENINO', 1, 0, 'L');
        $fpdf->Cell($w[1], $h_row, $stats['F']['inicial'], 1, 0, 'C');
        $fpdf->Cell($w[2], $h_row, $stats['F']['retirados'], 1, 0, 'C');
        $fpdf->Cell($w[3], $h_row, $stats['F']['final'], 1, 0, 'C');
        $fpdf->Cell($w[4], $h_row, $stats['F']['promovidos'], 1, 0, 'C');
        $fpdf->Cell($w[5], $h_row, $stats['F']['retenidos'], 1, 1, 'C'); // Salto de línea
        
        // --- Fila Total ---
        $fpdf->SetFont('Arial', 'B', 7);
        $fpdf->SetX($x); // Vuelve a la X inicial
        $fpdf->Cell($w[0], $h_row, 'TOTAL', 1, 0, 'L', true);
        $fpdf->Cell($w[1], $h_row, $stats['Total']['inicial'], 1, 0, 'C', true);
        $fpdf->Cell($w[2], $h_row, $stats['Total']['retirados'], 1, 0, 'C', true);
        $fpdf->Cell($w[3], $h_row, $stats['Total']['final'], 1, 0, 'C', true);
        $fpdf->Cell($w[4], $h_row, $stats['Total']['promovidos'], 1, 0, 'C', true);
        $fpdf->Cell($w[5], $h_row, $stats['Total']['retenidos'], 1, 1, 'C', true); // Salto de línea

        // --- Fecha y Hora ---
        $fpdf->SetFont('Arial', 'I', 7);
        $fpdf->SetX($x); // Vuelve a la X inicial
        $fpdf->Cell(array_sum($w), 5, mb_convert_encoding('Generado el: ' . date('d/m/Y H:i:s'), 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
    }

/**
     * Dibuja la tabla de Escala de Valoración (VERSIÓN MODULAR)
     * Acepta coordenadas X, Y, anchos (W), alturas (H) y textos.
     *
     * @param Fpdf $fpdf La instancia de FPDF
     * @param float $x La coordenada X (horizontal) para la esquina superior izquierda
     * @param float $y La coordenada Y (vertical) para la esquina superior izquierda
     * @param array $w (Opcional) Array con los anchos de las 3 columnas
     * @param float $h_title (Opcional) Alto de la fila del TÍTULO
     * @param float $h_row_1 (Opcional) Alto de la fila (E, MB, B)
     * @param float $h_line_row_2 (Opcional) Alto de CADA LÍNEA en la fila (Dominio...)
     * @param array $texts (Opcional) Array con los textos de la tabla
     */
    private function dibujarEscalaValoracion($fpdf, $x, $y, $w = null, $h_title = null, $h_row_1 = null, $h_line_row_2 = null, $texts = null)
    {
// --- INDICADOR: Valores por Defecto ---
        $w_default = [40, 40, 40]; // 3 columnas (Total 120mm)
        $h_title_line_default = 5; // Alto de CADA LÍNEA del título
        $h_row_1_default = 6;
        $h_line_row_2_default = 4; // Interlineado para la fila "Dominio..."

        $texts_default = [
            'title' => "ESCALA DE VALORACIÓN PARA\nLAS COMPETENCIAS CIUDADANAS",
            'row_1' => ["E: Excelente", "MB: Muy Bueno", "B: Bueno"],
            'row_2' => ["Dominio alto de la competencia", "Dominio medio de la competencia", "Dominio bajo de la competencia"]
        ];
        // --- Fin de Indicadores ---

// Asigna los valores (si el usuario no los pasa, usa los por defecto)
        $w = $w ?? $w_default;
        $h_title_line = $h_title_line ?? $h_title_line_default;
        $h_row_1 = $h_row_1 ?? $h_row_1_default;
        $h_line_row_2 = $h_line_row_2 ?? $h_line_row_2_default;
        $texts = $texts ?? $texts_default;

        // --- Dibuja Título ---
        $fpdf->SetXY($x, $y);
        $fpdf->SetFont('Arial', 'B', 9);
        $fpdf->SetFillColor(230, 230, 230);
        $fpdf->MultiCell(array_sum($w), $h_title_line, mb_convert_encoding($texts['title'], 'ISO-8859-1', 'UTF-8'), 1, 'C', true);

        // --- Dibuja Fila 1 (E, MB, B) ---
        $fpdf->SetX($x);
        $fpdf->SetFont('Arial', 'B', 8);
        $fpdf->Cell($w[0], $h_row_1, mb_convert_encoding($texts['row_1'][0], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $fpdf->Cell($w[1], $h_row_1, mb_convert_encoding($texts['row_1'][1], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $fpdf->Cell($w[2], $h_row_1, mb_convert_encoding($texts['row_1'][2], 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');

        // --- Dibuja Fila 2 (Dominio...) usando MultiCell ---
        $fpdf->SetX($x);
        $fpdf->SetFont('Arial', '', 8);
        $current_x = $x;
        $current_y = $fpdf->GetY();
        $max_y = $current_y; // Para rastrear la celda más alta
        
        for ($i = 0; $i < 3; $i++) {
            $fpdf->SetXY($current_x, $current_y);
            // Dibuja la MultiCell. $h_line_row_2 es el interlineado.
            $fpdf->MultiCell($w[$i], $h_line_row_2, mb_convert_encoding($texts['row_2'][$i], 'ISO-8859-1', 'UTF-8'), 1, 'C');
            
            // Guarda la Y máxima (la celda que crezca más)
            if ($fpdf->GetY() > $max_y) {
                $max_y = $fpdf->GetY();
            }
            // Mueve la X para la siguiente celda
            $current_x += $w[$i];
            // Restaura la Y para que la siguiente MultiCell empiece arriba
            $fpdf->SetY($current_y);
        }
        // Al final, establece la Y para lo que venga después
        $fpdf->SetY($max_y);
    }

/**
     * Dibuja el bloque de Promovidos y Retenidos con números en palabras.
     * Acepta coordenadas X, Y, anchos (W), alto (H) y tamaño de fuente.
     *
     * @param Fpdf $fpdf La instancia de FPDF
     * @param array $stats Los datos estadísticos calculados
     * @param float $x La coordenada X (horizontal) para la esquina superior izquierda
     * @param float $y La coordenada Y (vertical) para la esquina superior izquierda
     * @param float $w_label (Opcional) Ancho de la etiqueta (ej: "PROMOVIDOS:")
     * @param float $w_line (Opcional) Ancho de la línea para el texto
     * @param float $h_line (Opcional) Alto de cada fila
     * @param float $font_size (Opcional) Tamaño de la fuente
     */
    private function dibujarPromovidosRetenidos($fpdf, $stats, $x, $y, $w_label = null, $w_line = null, $h_line = null, $font_size = null)
    {
        // --- INDICADOR: Valores por Defecto ---
        $w_label_default = 30;    // Ancho para "PROMOVIDOS:"
        $w_line_default = 80;     // Ancho de la línea
        $h_line_default = 8;      // Alto de cada fila
        $font_size_default = 10;  // Tamaño de fuente
        $gap_default = 2;         // Espacio entre etiqueta y línea
        // --- Fin de Indicadores ---

        // Asigna los valores (si el usuario no los pasa, usa los por defecto)
        $w_label = $w_label ?? $w_label_default;
        $w_line = $w_line ?? $w_line_default;
        $h_line = $h_line ?? $h_line_default;
        $font_size = $font_size ?? $font_size_default;
        $gap = $gap_default;

        // 1. Obtener números del array de estadísticas
        $promovidos_num = $stats['Total']['promovidos'];
        $retenidos_num = $stats['Total']['retenidos'];

        // 2. Convertir a palabras (con fallback a número si 'intl' no está)
        $promovidos_texto = $promovidos_num; // Fallback
        $retenidos_texto = $retenidos_num; // Fallback
        
        if (class_exists('NumberFormatter')) {
            try {
                $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
                $promovidos_texto = mb_convert_encoding(ucfirst($formatter->format($promovidos_num)), 'ISO-8859-1', 'UTF-8');
                $retenidos_texto = mb_convert_encoding(ucfirst($formatter->format($retenidos_num)), 'ISO-8859-1', 'UTF-8');
            } catch (\Exception $e) {
                // Si falla (ej: 'es' no está), usa el número
                $promovidos_texto = $promovidos_num;
                $retenidos_texto = $retenidos_num;
            }
        }

        // --- 3. Dibujar ---
        $fpdf->SetFont('Arial', 'B', $font_size);

        // --- Fila PROMOVIDOS ---
        $fpdf->SetXY($x, $y);
        $fpdf->Cell($w_label, $h_line, 'PROMOVIDOS:', 0, 0, 'L');
        
        $text_x = $x + $w_label + $gap;
        $fpdf->SetXY($text_x, $y);
        $fpdf->Cell($w_line, $h_line, $promovidos_texto, 0, 0, 'C'); // Dibuja el texto
        
        $line_y = $y + $h_line - 1; // 1mm por encima del fondo
        $fpdf->Line($text_x, $line_y, $text_x + $w_line, $line_y); // Dibuja la línea

        // --- Fila RETENIDOS ---
        $y_retenidos = $y + $h_line + 2; // Siguiente línea + 2mm de espacio
        $fpdf->SetXY($x, $y_retenidos);
        $fpdf->Cell($w_label, $h_line, 'RETENIDOS:', 0, 0, 'L');
        
        $text_x_retenidos = $x + $w_label + $gap;
        $fpdf->SetXY($text_x_retenidos, $y_retenidos);
        $fpdf->Cell($w_line, $h_line, $retenidos_texto, 0, 0, 'C'); // Dibuja el texto
        
        $line_y_retenidos = $y_retenidos + $h_line - 1;
        $fpdf->Line($text_x_retenidos, $line_y_retenidos, $text_x_retenidos + $w_line, $line_y_retenidos);
    }


/**
     * Dibuja el bloque de Lugar y Fecha.
     *
     * @param Fpdf $fpdf La instancia de FPDF
     * @param float $x La coordenada X (horizontal)
     * @param float $y La coordenada Y (vertical)
     * @param array $texts Array con ['lugar' => '...', 'fecha' => '...']
     * @param array $layout Array con ['w_label', 'w_line', 'h_line', 'font_size']
     */
    private function dibujarLugarFecha($fpdf, $x, $y, $texts, $layout = [])
    {
        // --- INDICADOR: Valores por Defecto ---
        $w_label = $layout['w_label'] ?? 15; // Ancho para "Lugar:"
        $w_line = $layout['w_line'] ?? 80;   // Ancho de la línea
        $h_line = $layout['h_line'] ?? 7;    // Alto de cada fila
        $font_size = $layout['font_size'] ?? 10;
        $gap = 2; // Espacio entre etiqueta y línea
        // --- Fin de Indicadores ---

        $fpdf->SetFont('Arial', 'B', $font_size);

        // --- Fila Lugar ---
        $fpdf->SetXY($x, $y);
        $fpdf->Cell($w_label, $h_line, mb_convert_encoding('Lugar:', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        
        $text_x = $x + $w_label + $gap;
        $fpdf->SetXY($text_x, $y);
        $fpdf->Cell($w_line, $h_line, mb_convert_encoding($texts['lugar'], 'ISO-8859-1', 'UTF-8'), 0, 0, 'C'); // Dibuja el texto
        
        $line_y = $y + $h_line - 1; // 1mm por encima del fondo
        $fpdf->Line($text_x, $line_y, $text_x + $w_line, $line_y); // Dibuja la línea

        // --- Fila Fecha ---
        $y_fecha = $y + $h_line; // Siguiente línea
        $fpdf->SetXY($x, $y_fecha);
        $fpdf->Cell($w_label, $h_line, mb_convert_encoding('Fecha:', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        
        $text_x_fecha = $x + $w_label + $gap;
        $fpdf->SetXY($text_x_fecha, $y_fecha);
        $fpdf->SetFont('Arial', '', $font_size); // Fuente normal para la fecha
        $fpdf->Cell($w_line, $h_line, $texts['fecha'], 0, 0, 'C'); // Dibuja el texto
        
        $line_y_fecha = $y_fecha + $h_line - 1;
        $fpdf->Line($text_x_fecha, $line_y_fecha, $text_x_fecha + $w_line, $line_y_fecha);
    }

    /**
     * Dibuja un bloque de Firma (línea, nombre, título).
     *
     * @param Fpdf $fpdf La instancia de FPDF
     * @param float $x La coordenada X (horizontal) del bloque
     * @param float $y La coordenada Y (vertical) del bloque
     * @param array $texts Array con ['nombre' => '...', 'titulo' => '...']
     * @param array $layout Array con ['w_linea', 'h_gap', 'font_size_nombre', 'font_size_titulo']
     */
    private function dibujarFirma($fpdf, $x, $y, $texts, $layout = [])
    {
        // --- INDICADOR: Valores por Defecto ---
        $w_linea = $layout['w_linea'] ?? 80;   // Ancho de la línea de firma
        $h_gap = $layout['h_gap'] ?? 5;      // Alto de cada línea de texto
        $font_size_nombre = $layout['font_size_nombre'] ?? 9;
        $font_size_titulo = $layout['font_size_titulo'] ?? 8;
        // --- Fin de Indicadores ---

        // 1. Dibuja la Línea
        $fpdf->SetXY($x, $y);
        $fpdf->Line($x, $y, $x + $w_linea, $y);

        // 2. Dibuja el Nombre
        $y_nombre = $y + 2; // 2mm debajo de la línea
        $fpdf->SetXY($x, $y_nombre);
        $fpdf->SetFont('Arial', 'B', $font_size_nombre);
        $fpdf->Cell($w_linea, $h_gap, $texts['nombre'], 0, 1, 'C'); // Salto de línea

        // 3. Dibuja el Título
        $fpdf->SetX($x); // Vuelve a la X de la firma
        $fpdf->SetFont('Arial', '', $font_size_titulo);
        $fpdf->Cell($w_linea, $h_gap, $texts['titulo'], 0, 1, 'C');
    }

    /**
     * Dibuja un rectángulo para el sello.
     *
     * @param Fpdf $fpdf La instancia de FPDF
     * @param float $x La coordenada X (horizontal)
     * @param float $y La coordenada Y (vertical)
     * @param float $w (Opcional) Ancho del rectángulo
     * @param float $h (Opcional) Alto del rectángulo
     */
    private function dibujarSello($fpdf, $x, $y, $w = null, $h = null)
    {
        // --- INDICADOR: Valores por Defecto ---
        $w_default = 40;
        $h_default = 40;
        // --- Fin de Indicadores ---
        $w = $w ?? $w_default;
        $h = $h ?? $h_default;

        $fpdf->Rect($x, $y, $w, $h);
    }


/**
     * Dibuja el encabezado oficial del MINED (Escudo y Títulos).
     * Acepta coordenadas separadas para el logo (x,y) y el texto (layout[text_x, text_y]).
     *
     * @param Fpdf $fpdf La instancia de FPDF
     * @param float $logo_x La coordenada X (horizontal) del LOGO
     * @param float $logo_y La coordenada Y (vertical) del LOGO
     * @param string $codigo_modalidad El código para determinar la Dirección Nacional
     * @param array $layout (Opcional) Array con layout personalizado
     */
    private function dibujarEncabezadoMined($fpdf, $logo_x, $logo_y, $codigo_modalidad, $layout = [])
    {
        // --- INDICADOR: Valores por Defecto ---
        $logo_w = $layout['logo_w'] ?? 15; // Ancho del logo
        $logo_h = $layout['logo_h'] ?? 20; // Alto del logo

        // --- Nuevos controles para el TEXTO ---
        $text_x = $layout['text_x'] ?? ($logo_x + $logo_w + 2); // X del texto (default: 2mm al lado del logo)
        $text_y = $layout['text_y'] ?? ($logo_y + 2);           // Y del texto (default: 2mm abajo del Y del logo)
        $text_align = $layout['text_align'] ?? 'C'; // Alineación: 'L', 'C', 'R'
        $text_width = $layout['text_width'] ?? 150; // Ancho del bloque de texto
        // --- Fin de nuevos controles ---
        
        // Tamaños de fuente
        $font_size_1 = $layout['font_size_1'] ?? 10; // "República de El Salvador"
        $font_size_2 = $layout['font_size_2'] ?? 10; // "Ministerio de Educación"
        $font_size_3 = $layout['font_size_3'] ?? 9;  // "Dirección Nacional..."
        
        // Interlineado (alto de cada línea de texto)
        $line_height = $layout['line_height'] ?? 5; 
        // --- Fin de Indicadores ---

        // 1. Dibuja el Logo/Escudo (en sus propias coordenadas)
        $logo_path = public_path('img/escudo-sv.png');
        $fpdf->SetXY($logo_x, $logo_y);
        if (file_exists($logo_path)) {
            $fpdf->Image($logo_path, $logo_x, $logo_y, $logo_w, $logo_h);
        }

        // 2. Determina el texto de la Línea 3 (con el switch que pediste)
        $linea_3_texto = ""; // Default
        switch ($codigo_modalidad) {
            case '17':
                $linea_3_texto = "Dirección Nacional de Educación Básica III Ciclo y Media";
                break;
            case '18':
                $linea_3_texto = "Dirección Nacional de Educación Básica";
                break;
            case '19':
                $linea_3_texto = "Dirección Nacional de Educación Básica";
                break;
            // AÑADE MÁS 'case' AQUÍ SI ES NECESARIO
            default:
                $linea_3_texto = "Dirección Nacional de Educación"; // Un default genérico
                break;
        }

        // 3. Dibuja el Bloque de Texto (en sus propias coordenadas X,Y)
        // Ya no se centra automáticamente, usa los valores del layout
        $fpdf->SetXY($text_x, $text_y); 

        $fpdf->SetFont('Arial', 'B', $font_size_1);
        $fpdf->Cell($text_width, $line_height, mb_convert_encoding("REPÚBLICA DE EL SALVADOR", 'ISO-8859-1', 'UTF-8'), 0, 1, $text_align); // 1 = Salto de línea
        
        $fpdf->SetX($text_x); // Vuelve al X del texto
        $fpdf->SetFont('Arial', 'B', $font_size_2);
        $fpdf->Cell($text_width, $line_height, mb_convert_encoding("MINISTERIO DE EDUCACIÓN", 'ISO-8859-1', 'UTF-8'), 0, 1, $text_align);

        $fpdf->SetX($text_x); // Vuelve al X del texto
        $fpdf->SetFont('Arial', 'B', $font_size_3);
        $fpdf->Cell($text_width, $line_height, mb_convert_encoding($linea_3_texto, 'ISO-8859-1', 'UTF-8'), 0, 1, $text_align);
    }
/**
     * Dibuja el bloque de Información General (Cuadro Final, CE, Dirección, etc.)
     *
     * @param Fpdf $fpdf La instancia de FPDF
     * @param float $x La coordenada X (horizontal)
     * @param float $y La coordenada Y (vertical)
     * @param array $data Array con todos los textos necesarios
     * @param array $layout (Opcional) Array con layout personalizado
     */
    private function dibujarInformacionGeneral($fpdf, $x, $y, $data, $layout = [])
    {
        // --- INDICADOR: Valores por Defecto ---
        $w_label_1 = $layout['w_label_1'] ?? 60; // Ancho para "NOMBRE DEL CENTRO EDUCATIVO:"
        $w_label_2 = $layout['w_label_2'] ?? 25; // Ancho para "DEPARTAMENTO:"
        $w_full = $layout['w_full'] ?? 175;  // Ancho total del bloque
        $h_line = $layout['h_line'] ?? 5;    // Interlineado
        
        $font_size_1 = $layout['font_size_1'] ?? 10; // "CUADRO FINAL..."
        $font_size_2 = $layout['font_size_2'] ?? 9;  // "NOMBRE DEL CE..."
        // --- Fin de Indicadores ---

        $fpdf->SetXY($x, $y);
        
        // --- Línea 1: CUADRO FINAL DE EVALUACIÓN DE: [Grado] [Sección] ---
        $fpdf->SetFont('Arial', 'B', $font_size_1);
        $texto_l1 = "CUADRO FINAL DE EVALUACIÓN DE: " . $data['grado'] . " " . $data['seccion'];
        $fpdf->Cell($w_full, $h_line, mb_convert_encoding($texto_l1, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L'); // 1 = Salto de línea

        // --- Línea 2: NOMBRE DEL CENTRO EDUCATIVO: [Nombre] ---
        $fpdf->SetX($x); // Vuelve al X inicial
        $fpdf->SetFont('Arial', '', $font_size_2);
        $fpdf->Cell($w_label_1, $h_line, mb_convert_encoding("NOMBRE DEL CENTRO EDUCATIVO:", 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $fpdf->SetFont('Arial', 'B', $font_size_2);
        $fpdf->Cell($w_full - $w_label_1, $h_line, $data['institucion'], 0, 1, 'L');

        // --- Línea 3: DIRECCIÓN: [Dirección] ---
        $fpdf->SetX($x);
        $fpdf->SetFont('Arial', '', $font_size_2);
        $fpdf->Cell($w_label_1, $h_line, mb_convert_encoding("DIRECCIÓN:", 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $fpdf->SetFont('Arial', 'B', $font_size_2);
        $fpdf->Cell($w_full - $w_label_1, $h_line, $data['direccion'], 0, 1, 'L');

        // --- Línea 4: DEPARTAMENTO: [Depto] MUNICIPIO: [Municipio] ---
        $fpdf->SetX($x);
        $fpdf->SetFont('Arial', '', $font_size_2);
        $fpdf->Cell($w_label_2, $h_line, mb_convert_encoding("DEPARTAMENTO:", 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $fpdf->SetFont('Arial', 'B', $font_size_2);
        $fpdf->Cell(60, $h_line, $data['departamento'], 0, 0, 'L'); // Ancho fijo para depto
        
        $fpdf->SetFont('Arial', '', $font_size_2);
        $fpdf->Cell(25, $h_line, mb_convert_encoding("MUNICIPIO:", 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $fpdf->SetFont('Arial', 'B', $font_size_2);
        $fpdf->Cell(0, $h_line, $data['municipio'], 0, 1, 'L'); // El resto

        // --- Línea 5: DISTRITO: [Distrito] N.º ACUERDO: [Acuerdo] ---
        $fpdf->SetX($x);
        $fpdf->SetFont('Arial', '', $font_size_2);
        $fpdf->Cell($w_label_2, $h_line, mb_convert_encoding("DISTRITO:", 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $fpdf->SetFont('Arial', 'B', $font_size_2);
        $fpdf->Cell(60, $h_line, $data['distrito'], 0, 0, 'L'); // Ancho fijo para distrito
        
        $fpdf->SetFont('Arial', '', $font_size_2);
        $fpdf->Cell(25, $h_line, mb_convert_encoding("Nº ACUERDO:", 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $fpdf->SetFont('Arial', 'B', $font_size_2);
        $fpdf->Cell(0, $h_line, $data['acuerdo'], 0, 1, 'L'); // El resto
    }

    public function index($id) 
    {
        // ... (Tu código de configuración, parseo de $id, cálculo de $stats, y consultas de BD
        //      $catalogo_area_asignatura, $AsignacionAsignatura, $EncargadoGrado, $EncargadoAsignatura
        //      ...no cambian. Pégalos aquí tal como los tenías) ...
        
        // Configurar PDF.
            $this->fpdf->SetFont('Arial', 'B', 9);
            $this->fpdf->AddPage(); // Añadimos la página al inicio
            $this->fpdf->SetMargins(5, 5, 5);
            $this->fpdf->SetAutoPageBreak(true,5);
        // --- INICIO: TÍTULO PRINCIPAL ---
            // 1. Define la posición Y inicial para el título
            $current_Y = 10; // 10mm desde arriba
            $this->fpdf->SetXY(10, $current_Y); 
            $this->fpdf->SetFont('Arial', 'B', 18);
            
            // 2. Dibuja el título centrado
            // Ancho usable = 355.6mm (Legal) - 5mm (Izq) - 5mm (Der) = 345.6
            $this->fpdf->Cell(345.6, 8, mb_convert_encoding('REGISTRO DE EVALUACIÓN DEL RENDIMIENTO ESCOLAR DE EDUCACIÓN', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
            
            // 3. Actualiza $current_Y para que sea la posición
            //    donde el siguiente bloque debe empezar (con un espacio de 2mm)
            $current_Y = $this->fpdf->GetY() + 2; 
            
            // Restablece la fuente para el resto del documento
            $this->fpdf->SetFont('Arial', 'B', 9); 
        // --- FIN: TÍTULO PRINCIPAL ---

        // ... (pega aquí tu parseo de $id) ...
            $EstudianteMatricula = explode("-",$id);
            if($EstudianteMatricula[0] == "Tablero"){
                $codigo_gradoseccionturnomodalidad = $EstudianteMatricula[1]; $codigo_modalidad = substr($codigo_gradoseccionturnomodalidad,6,2); $codigo_turno = substr($codigo_gradoseccionturnomodalidad,4,2); $codigo_seccion = substr($codigo_gradoseccionturnomodalidad,2,2); $codigo_grado = substr($codigo_gradoseccionturnomodalidad,0,2);
                $codigo_annlectivo = $EstudianteMatricula[2]; $codigo_personal = $EstudianteMatricula[3]; $codigo_institucion = $EstudianteMatricula[4];
            }else{
                $codigo_gradoseccionturnomodalidad = $EstudianteMatricula[0]; $codigo_modalidad = substr($codigo_gradoseccionturnomodalidad,6,2); $codigo_turno = substr($codigo_gradoseccionturnomodalidad,4,2); $codigo_seccion = substr($codigo_gradoseccionturnomodalidad,2,2); $codigo_grado = substr($codigo_gradoseccionturnomodalidad,0,2);
                $codigo_annlectivo = $EstudianteMatricula[1]; $codigo_institucion = $EstudianteMatricula[2]; $codigo_asignatura = $EstudianteMatricula[3]; $codigo_area_asignatura = $EstudianteMatricula[4]; $codigo_personal = $EstudianteMatricula[5];
            }

        // ... (pega aquí tu cálculo de $stats) ...
            $stats = [ 'M' => ['inicial' => 0, 'retirados' => 0, 'final' => 0, 'promovidos' => 0, 'retenidos' => 0], 'F' => ['inicial' => 0, 'retirados' => 0, 'final' => 0, 'promovidos' => 0, 'retenidos' => 0], 'Total' => ['inicial' => 0, 'retirados' => 0, 'final' => 0, 'promovidos' => 0, 'retenidos' => 0], ];
            $statsData = DB::table('alumno_matricula as am')->join('alumno as a', 'a.id_alumno', '=', 'am.codigo_alumno')->select( 'a.codigo_genero', DB::raw('COUNT(*) as total_inicial'), DB::raw("SUM(CASE WHEN am.retirado = true THEN 1 ELSE 0 END) as total_retirados"), DB::raw("SUM(CASE WHEN am.retirado = false THEN 1 ELSE 0 END) as total_final"))->where('am.codigo_bach_o_ciclo', $codigo_modalidad)->where('am.codigo_grado', $codigo_grado)->where('am.codigo_seccion', $codigo_seccion)->where('am.codigo_turno', $codigo_turno)->where('am.codigo_ann_lectivo', $codigo_annlectivo)->groupBy('a.codigo_genero')->get();
            foreach ($statsData as $row) { $gender = (trim($row->codigo_genero) == '01') ? 'M' : 'F'; $stats[$gender]['inicial'] = $row->total_inicial; $stats[$gender]['retirados'] = $row->total_retirados; $stats[$gender]['final'] = $row->total_final; $stats['Total']['inicial'] += $row->total_inicial; $stats['Total']['retirados'] += $row->total_retirados; $stats['Total']['final'] += $row->total_final; }
            $studentsForPromotion = DB::table('alumno_matricula as am')->join('alumno as a', 'a.id_alumno', '=', 'am.codigo_alumno')->join('nota as n', 'n.codigo_matricula', '=', 'am.id_alumno_matricula')->join('asignatura as asig', 'n.codigo_asignatura', '=', 'asig.codigo')->select('am.id_alumno_matricula', 'a.codigo_genero', 'n.recuperacion', 'n.nota_recuperacion_2', 'n.nota_final', 'asig.codigo_area')->where('am.codigo_bach_o_ciclo', $codigo_modalidad)->where('am.codigo_grado', $codigo_grado)->where('am.codigo_seccion', $codigo_seccion)->where('am.codigo_turno', $codigo_turno)->where('am.codigo_ann_lectivo', $codigo_annlectivo)->where('am.retirado', false)->get();
            $studentsGrades = [];

        // --- INICIO: Definir grados de promoción masiva ---
        // Estos códigos de grado se considerarán 100% promovidos (si están activos)
        $grados_promocion_masiva = ['4P', '5P', '6P', '01'];
        // --- FIN: Definir grados ---
            foreach ($studentsForPromotion as $grade) { $studentsGrades[$grade->id_alumno_matricula]['gender'] = (trim($grade->codigo_genero) == '01') ? 'M' : 'F'; $studentsGrades[$grade->id_alumno_matricula]['grades'][] = [ 'rec_1' => $grade->recuperacion, 'rec_2' => $grade->nota_recuperacion_2, 'final' => $grade->nota_final, 'area' => $grade->codigo_area ]; }

            foreach ($studentsGrades as $matricula_id => $student) { 
                $isRetenido = false; 
                $gender = $student['gender']; 
                if (in_array($codigo_grado, $grados_promocion_masiva)) {
                
                // ¡PROMOCIÓN MASIVA!
                // Como la consulta $studentsForPromotion ya filtró por `retirado = false`,
                // sabemos que este estudiante está activo y, por lo tanto, debe ser promovido.
                $isRetenido = false;
                }
                else { foreach ($student['grades'] as $grade) {
                        $result = resultado_final( $codigo_modalidad, $grade['rec_1'], $grade['rec_2'], $grade['final'], $grade['area'] ); 
                        if ($result[0] == 'R') 
                            { $isRetenido = true; break; }
                         }
                         } 
                        if ($isRetenido) 
                            { $stats[$gender]['retenidos']++; $stats['Total']['retenidos']++; } 
                    else 
                        { $stats[$gender]['promovidos']++; $stats['Total']['promovidos']++;
                 }
                    // Esta lógica final ahora funciona para ambos casos:
                        /*if ($isRetenido) { 
                            $stats[$gender]['retenidos']++; 
                            $stats['Total']['retenidos']++; 
                        } else { 
                            $stats[$gender]['promovidos']++; 
                            $stats['Total']['promovidos']++; 
                        }*/
                }   // for each del $studentsGrades

        // ... (pega aquí tus consultas de $catalogo_area_asignatura, $AsignacionAsignatura, $EncargadoGrado, $EncargadoAsignatura) ...
            $catalogo_area_asignatura_codigo = array();	$catalogo_area_asignatura_area = array();
            $catalogo_area_basica = true; $catalogo_area_formativa = true; $catalogo_area_tecnica = true; $catalogo_area_edps = true; $catalogo_area_edecr = true; $catalogo_area_edre = true; $catalogo_area_complementaria = true; $catalogo_area_cc = true; $catalogo_area_alertas = true;
            $alto_cell = array('5','40'); $ancho_cell = array('60','6','24','30','12');        
            $CatalogoAreaAsignatura = DB::table('catalogo_area_asignatura')->select('codigo','descripcion')->get();
                foreach($CatalogoAreaAsignatura as $response_area){  $catalogo_area_asignatura_codigo[] = (trim($response_area->codigo)); $catalogo_area_asignatura_area[] = (trim($response_area->descripcion)); }
            $AsignacionAsignatura = DB::table('a_a_a_bach_o_ciclo as aaa')->join('asignatura as a','a.codigo','=','aaa.codigo_asignatura')->join('catalogo_area_asignatura AS cat_area','cat_area.codigo','=','a.codigo_area')
                    ->select('aaa.orden','a.nombre as nombre_asignatura','a.codigo as codigo_asignatura','a.codigo_cc as concepto_calificacion','a.codigo_area','cat_area.descripcion as nombre_area')
                ->where([['aaa.codigo_bach_o_ciclo', '=', $codigo_modalidad],['aaa.codigo_grado', '=', $codigo_grado],['aaa.codigo_ann_lectivo', '=', $codigo_annlectivo],])->orderBy('aaa.orden','asc')->orderBy('a.codigo_area','asc')->get();
                $datos_asignatura = array(); $fila_array_asignatura = 0; $count_asignaturas = array();
                $datos_asignatura = [ "codigo" => [""], "nombre" => [""], "concepto" => [""], "codigo_area" => [""], "nombre_area" => [""] ];       
                foreach($AsignacionAsignatura as $response_i){  
                    $nombre_asignatura_a = mb_convert_encoding(trim($response_i->nombre_asignatura),"ISO-8859-1","UTF-8"); $codigo_asignatura_a = mb_convert_encoding(trim($response_i->codigo_asignatura),"ISO-8859-1","UTF-8");
                    $concepto_calificacion_a = mb_convert_encoding(trim($response_i->concepto_calificacion),"ISO-8859-1","UTF-8"); $codigo_area_a = mb_convert_encoding(trim($response_i->codigo_area),"ISO-8859-1","UTF-8");
                    $nombre_area_a = mb_convert_encoding(trim($response_i->nombre_area),"ISO-8859-1","UTF-8"); $count_asignaturas[] = $codigo_area_a;
                    $datos_asignatura["codigo"][$fila_array_asignatura] = $codigo_asignatura_a; $datos_asignatura["nombre"][$fila_array_asignatura] = $nombre_asignatura_a;
                    $datos_asignatura["concepto"][$fila_array_asignatura] = $concepto_calificacion_a; $datos_asignatura["codigo_area"][$fila_array_asignatura] = $codigo_area_a; $datos_asignatura["nombre_area"][$fila_array_asignatura] = $nombre_area_a;                        
                    $total_asignaturas = count($count_asignaturas); $fila_array_asignatura++; }
                
                // --- INICIO: CÁLCULO DE ANCHO DINÁMICO ---
                // Se define el ancho máximo para el bloque de asignaturas
                $max_ancho_asignaturas_total = 170; 
                $ancho_col_asignatura_dinamico = 12; // Ancho por defecto si no hay asignaturas
                
                if ($total_asignaturas > 0) {
                    // Se calcula el ancho de CADA columna de asignatura
                    $ancho_col_asignatura_dinamico = $max_ancho_asignaturas_total / $total_asignaturas;
                }
                // --- FIN: CÁLCULO DE ANCHO DINÁMICO ---

                $codigo_area_existentes = array(); $counter = array_count_values($datos_asignatura['codigo_area']);
                foreach($counter as $key => $cantidad) { $ancho_area[] = $cantidad; $codigo_area_existentes[] = $key; }
            $EncargadoGrado = DB::table('encargado_grado as eg')->join('personal as p','p.id_personal','=','eg.codigo_docente')
                ->select('p.id_personal', 'p.firma', DB::raw("TRIM(CONCAT(BTRIM(p.nombres), CAST(' ' AS VARCHAR), BTRIM(p.apellidos))) as full_name"))
                ->where([ ['codigo_bachillerato', '=', $codigo_modalidad], ['codigo_grado', '=', $codigo_grado], ['codigo_ann_lectivo', '=', $codigo_annlectivo], ['codigo_seccion', '=', $codigo_seccion], ['codigo_turno', '=', $codigo_turno], ['encargado', '=', 'true'], ])
                ->orderBy('p.id_personal','asc')->get();
                $nombre_personal_ = ''; $firma_docente = '';
                foreach($EncargadoGrado as $response_eg){ $codigo_personal_ = mb_convert_encoding(trim($response_eg->id_personal),"ISO-8859-1","UTF-8"); $nombre_personal_ = mb_convert_encoding(trim($response_eg->full_name),"ISO-8859-1","UTF-8"); $firma_docente = mb_convert_encoding(trim($response_eg->firma),"ISO-8859-1","UTF-8"); }
            $EncargadoAsignatura = DB::table('personal as p')->select('p.id_personal', DB::raw("TRIM(CONCAT(BTRIM(p.nombres), CAST(' ' AS VARCHAR), BTRIM(p.apellidos))) as full_name"))->where([['p.id_personal', '=', $codigo_personal],])->orderBy('p.id_personal','asc')->get();
                $nombre_personal_ea = '';
                foreach($EncargadoAsignatura as $response_eg){ $codigo_personal_ = mb_convert_encoding(trim($response_eg->id_personal),"ISO-8859-1","UTF-8"); $nombre_personal_ea = mb_convert_encoding(trim($response_eg->full_name),"ISO-8859-1","UTF-8"); }

                // =================================================================
        // ====== INICIO: GUARDAR/ACTUALIZAR ESTADÍSTICAS EN BD (NUEVO) ======
        // =================================================================
        
           // =================================================================
        // ====== INICIO: GUARDAR/ACTUALIZAR ESTADÍSTICAS EN BD (NUEVO) ======
        // =================================================================
        // --- 1. HABILITAMOS EL LOG DE QUERIES ---
        DB::enableQueryLog();

        try {
            DB::commit();
            // --- Registro Masculino ---
            DB::table('estadistica_grados')->updateOrInsert(
                [
                    // --- Atributos (Cláusula WHERE para buscar) ---
                    'codigo_bachillerato_ciclo' => $codigo_modalidad,
                    'codigo_ann_lectivo'        => $codigo_annlectivo,
                    'codigo_grado'              => $codigo_grado,
                    'codigo_seccion'            => $codigo_seccion,
                    'genero'                    => 'Masculino'
                ],
                [
                    // --- Valores (Campos para INSERT o UPDATE) ---
                    'codigo_docente'      => $codigo_personal_,
                    'matricula_inicial'   => $stats['M']['inicial'],
                    'retirados'           => $stats['M']['retirados'],
                    'matricula_final'     => $stats['M']['final'],
                    'promovidos'          => $stats['M']['promovidos'],
                    'retenidos'           => $stats['M']['retenidos']
                ]
            );

            // --- Registro Femenino ---
            DB::table('estadistica_grados')->updateOrInsert(
                [
                    'codigo_bachillerato_ciclo' => $codigo_modalidad,
                    'codigo_ann_lectivo'        => $codigo_annlectivo,
                    'codigo_grado'              => $codigo_grado,
                    'codigo_seccion'            => $codigo_seccion,
                    'genero'                    => 'Femenino'
                ],
                [
                    'codigo_docente'      => $codigo_personal_,
                    'matricula_inicial'   => $stats['F']['inicial'],
                    'retirados'           => $stats['F']['retirados'],
                    'matricula_final'     => $stats['F']['final'],
                    'promovidos'          => $stats['F']['promovidos'],
                    'retenidos'           => $stats['F']['retenidos']
                ]
            );

        } catch (\Exception $e) {
            // Si AHORA falla, lo registraremos
         
        }

        // =================================================================
        // ====== INICIO: DIBUJAR ENCABEZADOS Y ESTADÍSTICAS ======
        // =================================================================

         // --- Consulta de Información de la Institución (MODIFICADA CON JOINS) ---
         $EstudianteInformacionInstitucion = DB::table('informacion_institucion as inf')
         ->leftjoin('personal as p','p.id_personal','=',DB::raw("CAST(inf.nombre_director AS INTEGER)"))
         // --- INICIO: JOINS NUEVOS ---
         ->leftjoin('catalogo_departamentos as cat_dep', 'cat_dep.codigo', '=', 'inf.codigo_departamento')
         ->leftjoin('catalogo_municipios as mun', function($join) {
             $join->on('mun.codigo', '=', 'inf.codigo_municipio')
                  ->on('mun.codigo_departamento', '=', 'inf.codigo_departamento');
         })
         ->leftjoin('catalogo_distritos as dis', function($join) {
             $join->on('dis.codigo', '=', 'inf.codigo_distrito')
                  ->on('dis.codigo_municipio', '=', 'inf.codigo_municipio')
                  ->on('dis.codigo_departamento', '=', 'inf.codigo_departamento');
         })
         // --- FIN: JOINS NUEVOS ---
         ->select(
             'inf.id_institucion','inf.codigo_institucion','inf.nombre_institucion','inf.telefono_uno',
             'inf.logo_uno','inf.direccion_institucion','inf.nombre_director', 'inf.logo_dos','inf.logo_tres', 
             'inf.numero_acuerdo', // <-- AÑADIDO
             DB::raw("TRIM(CONCAT(BTRIM(p.nombres), CAST(' ' AS VARCHAR), BTRIM(p.apellidos))) as full_name"),
             // --- INICIO: NUEVOS SELECTS ---
             'cat_dep.descripcion as nombre_departamento',
             'mun.descripcion as nombre_municipio',
             'dis.descripcion as nombre_distrito'
             // --- FIN: NUEVOS SELECTS ---
         ) 
         ->where('id_institucion', '=', $codigo_institucion)->orderBy('id_institucion','asc')->limit(1)->get();
     
     // --- Extracción de variables (MODIFICADA) ---
     $logo_uno_path = ''; $firma_director_path = ''; $sello_direccion_path = ''; $nombre_director = '';
     $nombre_institucion = ''; $codigo_institucion_infra = '';
     $direccion_institucion = ''; $nombre_municipio = ''; $nombre_distrito = ''; 
     $nombre_departamento = ''; $numero_acuerdo = ''; // <-- AÑADIDAS

     foreach($EstudianteInformacionInstitucion as $response_i){  
         $nombre_institucion = mb_convert_encoding(trim($response_i->nombre_institucion),"ISO-8859-1","UTF-8");
         $nombre_director = mb_convert_encoding(trim($response_i->full_name),"ISO-8859-1","UTF-8");
         $codigo_institucion_infra = mb_convert_encoding(trim($response_i->codigo_institucion),"ISO-8859-1","UTF-8");
         
         // --- NUEVAS VARIABLES ---
         $direccion_institucion = mb_convert_encoding(trim($response_i->direccion_institucion),"ISO-8859-1","UTF-8");
         $nombre_departamento = mb_convert_encoding(trim($response_i->nombre_departamento),"ISO-8859-1","UTF-8"); // <-- AÑADIDA
         $nombre_municipio = mb_convert_encoding(trim($response_i->nombre_municipio),"ISO-8859-1","UTF-8");
         $nombre_distrito = mb_convert_encoding(trim($response_i->nombre_distrito),"ISO-8859-1","UTF-8");
         $numero_acuerdo = mb_convert_encoding(trim($response_i->numero_acuerdo),"ISO-8859-1","UTF-8"); // <-- AÑADIDA
         // --- FIN NUEVAS VARIABLES ---

         $logo_uno_path = public_path('img/' . trim($response_i->logo_uno));
         $firma_director_path = public_path('img/' . trim($response_i->logo_dos));
         $sello_direccion_path = public_path('img/' . trim($response_i->logo_tres));
     }

           // --- INDICADOR 1: POSICIÓN DEL NUEVO ENCABEZADO MINED ---
            
            // Coordenadas del LOGO (escudo-sv.png)
            $logo_X = 25;
            $logo_Y = 10;
            
            // (Opcional) Layout personalizado
            $mined_layout = [
                'logo_w' => 25,
                'logo_h' => 22,
                
                // --- ¡Control Manual del Texto! ---
                // Mueve estas X/Y para mover las 3 líneas de texto
                'text_x' => 5, // Distancia del texto desde la izquierda
                'text_y' => 32, // Distancia del texto desde arriba
                
                'text_align' => 'C', // 'L' (Izquierda), 'C' (Centro), 'R' (Derecha)
                'text_width' => 65, // Ancho de la caja de texto
                'line_height' => 4,  // Interlineado
                'font_size_1' => 9,
                'font_size_2' => 9,
                'font_size_3' => 7
            ];
            
            // Llamamos a la función con las coordenadas del LOGO
            $this->dibujarEncabezadoMined($this->fpdf, $logo_X, $logo_Y, $codigo_modalidad, $mined_layout);

                    // --- INDICADOR 2: POSICIÓN DEL BLOQUE "CENTRO ESCOLAR" ---
                    // (Lo movemos debajo del logo del MINED)
                    //$ce_X = 10;
                    //$ce_Y = 35; // Debajo del logo/texto MINED (Y=10 + H=20 + 5mm padding)
                $ce_X = 43;
                $ce_Y = $current_Y;
                $this->fpdf->SetXY($ce_X, $ce_Y); 
                $this->fpdf->SetFont('Arial', 'B', 10); 
                if (file_exists($logo_uno_path)) {
                  //  $this->fpdf->image($logo_uno_path, $this->fpdf->GetX(), $this->fpdf->GetY(), 15, 20);
                }
                
               // $this->fpdf->SetXY($ce_X + 17, $ce_Y); // 10 (margen) + 15 (logo) + 2 (espacio)    
               // $this->fpdf->Cell(40, $alto_cell[0],"CENTRO ESCOLAR:",1,0,'L');       
               // $this->fpdf->Cell(135, $alto_cell[0],$codigo_institucion_infra . " - " .$nombre_institucion,1,1,'L');       
            
            // --- Dibujar Encabezado Derecho (Estadísticas) ---
            
            // --- INDICADOR: CONTROL MANUAL DE ESTADÍSTICAS ---
            
            // 1. Mueve la tabla completa cambiando X (horizontal) e Y (vertical)
            $stats_X = 260; // Distancia desde la izquierda
            $stats_Y = 90;  // Distancia desde arriba

            // 2. Cambia los anchos de las 6 columnas
            $w_stats = [17, 14, 14, 15, 18, 14]; 
            
            // 3. Cambia las alturas
            $h_titulo_stats = 8;  // Alto de la celda "ESTADÍSTICA"
            $h_linea_header_stats = 5; // Alto de CADA línea de la cabecera (5mm * 2 líneas = 10mm)
            $h_fila_datos_stats = 6;  // Alto de las filas (Masculino, Femenino, Total)

            // 4. Cambia los textos. Usa \n para un salto de línea
            $textos_header_stats = [
                "SEXO\n  ",
                "Matrícula\nInicial",
                "Retirados\n ",
                "Matrícula\nFinal",
                "Promovidos\n ",
                "Retenidos\n "
            ];

            // Esta es la función que dibuja la tabla.
            // Pasa tus variables personalizadas aquí.
            $this->dibujarEstadisticas(
                $this->fpdf, 
                $stats, 
                $stats_X, 
                $stats_Y, 
                $w_stats, 
                $h_titulo_stats, 
                $h_linea_header_stats, 
                $h_fila_datos_stats, 
                $textos_header_stats
            );
            
            // --- FIN DEL INDICADOR ---
            
            $y_stats_fin = $this->fpdf->GetY(); // Guarda dónde terminó la tabla de stats

            // =================================================================
        // ====== INICIO: DIBUJAR ESCALA DE VALORACIÓN (NUEVO BLOQUE) ======
        // =================================================================

            // --- INDICADOR: POSICIÓN DEL BLOQUE "Escala de Valoración" ---
            
            // 1. Mueve la tabla completa cambiando X (horizontal) e Y (vertical)
            //    (Por defecto, la pongo debajo de la tabla de estadísticas)
            $escala_X = $stats_X; // Misma X que las estadísticas
            $escala_Y = $y_stats_fin - 90; // 5mm debajo de las estadísticas
            
            // 2. (Opcional) Cambia los anchos de las 3 columnas
            $w_escala = [30, 30, 30]; // Total 120mm
            
            // 3. (Opcional) Cambia las alturas
            $h_titulo_escala = 10;
            $h_fila_1_escala = 6;
            $h_linea_fila_2_escala = 4; // Interlineado
            
            // 4. (Opcional) Cambia los textos
            $textos_escala = [
                'title' => "ESCALA DE VALORACIÓN PARA LAS\nCOMPETENCIAS CIUDADANAS",
                'row_1' => ["E: Excelente", "MB: Muy Bueno", "B: Bueno"],
                'row_2' => ["Dominio alto de la competencia", "Dominio medio de la competencia", "Dominio bajo de la competencia"]
            ];

            // Esta es la función que dibuja la tabla.
            $this->dibujarEscalaValoracion(
                $this->fpdf, 
                $escala_X, 
                $escala_Y, 
                $w_escala, 
                $h_titulo_escala, 
                $h_fila_1_escala, 
                $h_linea_fila_2_escala,
                $textos_escala
            );
            // --- FIN DEL INDICADOR ---
            // Guarda la Y final de la escala de valoración
                $y_escala_fin = $this->fpdf->GetY();
        // =================================================================
        // ====== INICIO: DIBUJAR PROMOVIDOS/RETENIDOS (NUEVO BLOQUE) ======
        // =================================================================
            
            // --- INDICADOR: POSICIÓN DEL BLOQUE "Promovidos/Retenidos" ---
            
            // 1. Mueve el bloque
            $promo_X = $stats_X; // Misma X que las estadísticas
            $promo_Y = $y_escala_fin + 85; // 5mm debajo de la escala
            
            // 2. (Opcional) Cambia los anchos y fuentes
            $w_label_promo = 30;
            $w_linea_promo = 50;
            $h_linea_promo = 8;
            $font_size_promo = 10;

            $this->dibujarPromovidosRetenidos(
                $this->fpdf, 
                $stats, 
                $promo_X, 
                $promo_Y, 
                $w_label_promo, 
                $w_linea_promo, 
                $h_linea_promo, 
                $font_size_promo
            );
            // --- FIN DEL INDICADOR ---






            
        // =================================================================
        // ====== INICIO: BUCLE PRINCIPAL DE NOTAS ======
        // =================================================================

        // --- Consulta principal de Estudiantes y Notas ---
            $EstudianteBoleta = DB::table('alumno as a')
                // ... (El resto de tu consulta $EstudianteBoleta no cambia) ...
                ->join('alumno_matricula AS am','a.id_alumno','=','am.codigo_alumno')
                ->join('nota AS n','am.id_alumno_matricula','=','n.codigo_matricula')
                ->join('bachillerato_ciclo AS bach', 'bach.codigo','=','am.codigo_bach_o_ciclo')
                ->join('grado_ano AS gr', 'gr.codigo','=','am.codigo_grado')
                ->join('seccion AS sec', 'sec.codigo','=','am.codigo_seccion')
                ->join('turno AS tur', 'tur.codigo','=','am.codigo_turno')
                ->join('asignatura AS asig','asig.codigo','=','n.codigo_asignatura')
                ->select('a.id_alumno as codigo_alumno','a.codigo_nie','a.nombre_completo',"a.apellido_paterno",'a.apellido_materno', 'a.foto', 'a.codigo_genero',
                        'am.id_alumno_matricula as codigo_matricula','am.codigo_bach_o_ciclo as codigo_modalidad','am.codigo_grado','am.codigo_seccion','am.codigo_turno','am.codigo_ann_lectivo',
                        'n.id_notas','n.codigo_asignatura', 'n.orden',
                        'bach.nombre AS nombre_modalidad', 'gr.nombre as nombre_grado', 'sec.nombre as nombre_seccion','tur.nombre as nombre_turno',
                        'n.nota_a1_1', 'n.nota_a2_1', 'n.nota_a3_1', 'n.nota_p_p_1', 'n.nota_a1_2', 'n.nota_a2_2', 'n.nota_a3_2', 'n.nota_p_p_2',
                        'n.nota_a1_3', 'n.nota_a2_3', 'n.nota_a3_3', 'n.nota_p_p_3', 'n.nota_a1_4', 'n.nota_a2_4', 'n.nota_a3_4', 'n.nota_p_p_4',
                        'n.nota_a1_5', 'n.nota_a2_5', 'n.nota_a3_5', 'n.nota_p_p_5', 'n.nota_final', 'n.recuperacion', 'n.nota_recuperacion_2',
                        'asig.codigo_area','asig.codigo as codigo_asignatura','asig.nombre as nombre_asignatura',
                    DB::raw("TRIM(CONCAT(BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno), CAST(' ' AS VARCHAR), BTRIM(a.nombre_completo))) as full_name"),
                    DB::raw("TRIM(CONCAT(BTRIM(a.nombre_completo), CAST(' ' AS VARCHAR), BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno))) as full_nombres_apellidos")
                    )
                        ->where([
                            ['am.codigo_bach_o_ciclo', '=', $codigo_modalidad],
                            ['am.codigo_grado', '=', $codigo_grado],
                            ['am.codigo_seccion', '=', $codigo_seccion],
                            ['am.codigo_turno', '=', $codigo_turno],
                            ['am.codigo_ann_lectivo', '=', $codigo_annlectivo],
                            ['am.retirado', '=', 'f'],
                            ])
                        ->orderBy('full_name','asc')
                        ->orderBy('n.orden','asc')
                        ->get();

            $fila = 1; $fila_asignatura = 0; $fila_numero = 1; $fill = true; $ultima_columna = 250;
            
            // --- INDICADOR: POSICIÓN DEL BLOQUE DE NOTAS (ENCABEZADO Y TABLA) ---
            // Y = Distancia desde arriba. Lo calculamos para que empiece
            //     debajo del bloque más alto (Info C.E. o Estadísticas)
            $y_header_izquierdo_fin = $this->fpdf->GetY(); // Dónde terminó el C.E.
            $y_stats_fin = $this->fpdf->GetY();        // Dónde terminó Stats (¡Cuidado! Esta Y es de la última celda de Stats)
            
            // Queremos que la tabla de notas empiece debajo del bloque que haya quedado más abajo
            //$table_Y_start = max($y_header_izquierdo_fin, $y_stats_fin) + 5; // +5mm de padding
            $table_Y_start = 15;
            // X = Distancia desde la izquierda.
            $table_X_start = 27; // Margen izquierdo
            // --- FIN DEL INDICADOR ---
            
            $header_dibujado = false; // Flag para dibujar el header de notas solo una vez
            $posicion_Y_texto_rotado = 0; // Guardará la Y para el texto de asignaturas

                // --- INICIO: Inicialización de Acumuladores (NUEVO) ---
                $subjectTotals = []; // Array para guardar la suma de cada asignatura
                $subjectCounts = []; // Array para contar cuántos estudiantes por asignatura
                // Define las áreas que SÍ se deben sumar y promediar
                $relevantAreas = ['01', '03', '08']; // 01=Básica, 03=Técnica, 08=Complementaria
                // --- FIN: Inicialización de Acumuladores ---
                // --- NUEVO: Inicializar array para el Ranking ---
                    $ranking_estudiantes = [];
            foreach($EstudianteBoleta as $response){
                // ... (toda tu lógica de variables $nombre_completo, $codigo_nie, $nota_actividades_0, etc.) ...
                    $nombre_completo = mb_convert_encoding(trim($response->full_nombres_apellidos),"ISO-8859-1","UTF-8");
                    $nombre_estudiante = mb_convert_encoding(trim($response->full_name),"ISO-8859-1","UTF-8");
                    $codigo_nie = mb_convert_encoding(trim($response->codigo_nie),"ISO-8859-1","UTF-8");
                    $nombre_modalidad = mb_convert_encoding(trim($response->nombre_modalidad),"ISO-8859-1","UTF-8");  
                    $nombre_grado = mb_convert_encoding(trim($response->nombre_grado),"ISO-8859-1","UTF-8");  
                    $nombre_seccion = mb_convert_encoding(trim($response->nombre_seccion),"ISO-8859-1","UTF-8");  
                    $nombre_turno = mb_convert_encoding(trim($response->nombre_turno),"ISO-8859-1","UTF-8");                
                    $codigo_asignatura = mb_convert_encoding(trim($response->codigo_asignatura),"ISO-8859-1","UTF-8");
                    $codigo_area = mb_convert_encoding(trim($response->codigo_area),"ISO-8859-1","UTF-8");
                    $nota_final = mb_convert_encoding(trim($response->nota_final),"ISO-8859-1","UTF-8");
                    $nota_recuperacion_1 = mb_convert_encoding(trim($response->recuperacion),"ISO-8859-1","UTF-8");
                    $nota_recuperacion_2 = mb_convert_encoding(trim($response->nota_recuperacion_2),"ISO-8859-1","UTF-8");
                    $nombre_foto = (trim($response->foto)); $codigo_genero = (trim($response->codigo_genero));
                    $nota_actividades_0 = array('', $response->recuperacion, $response->nota_recuperacion_2, $response->nota_final);
                    $periodos_a = array('PERIODO 1', 'PERIODO 2', 'PERIODO 3', 'PERIODO 4', 'PERIODO 5', 'PROMEDIO FINAL', 'R');
                    $actividad_periodo = array('NF');
                    if($codigo_modalidad >= '03' && $codigo_modalidad <= '05'){ $valor_periodo = 2; $valor_actividades = 12; $ancho_area_asignatura = 162; }
                    else if($codigo_modalidad >= '06' && $codigo_modalidad <= '09'){ $valor_periodo = 3; $valor_actividades = 16; $ancho_area_asignatura = 186; }
                    else if($codigo_modalidad >= '10' && $codigo_modalidad <= '12'){ $valor_periodo = 4; $valor_actividades = 20; $ancho_area_asignatura = 210; }
                    else{ $valor_periodo = 2; $valor_actividades = 12; $ancho_area_asignatura = 162; }
                    // --- INICIO: Cálculo y Acumulación de Nota (NUEVO) ---
                        
                        // 1. Calculamos la nota final (Numérica)
                        $result = resultado_final($codigo_modalidad, $nota_recuperacion_1, $nota_recuperacion_2, $nota_final, $codigo_area);
                        $finalGrade = $result[1]; // El número (ej: 7 o 4)
                        $finalResultLetter = $result[0]; // 'A' o 'R'

                        // --- ACUMULAR PARA RANKING ---
                    // 1. Asegurar que el estudiante existe en el array
                    if (!isset($ranking_estudiantes[$codigo_nie])) {
                        $ranking_estudiantes[$codigo_nie] = [
                            'nie' => $codigo_nie,
                            'nombre' => $nombre_estudiante,
                            'total_puntos' => 0
                        ];
                    }

                    // 2. Sumar puntos si el área es relevante
                    // (Asegúrate que $relevantAreas esté definido arriba en el Paso 1)
                    if(in_array($codigo_area, $relevantAreas)){
                        $ranking_estudiantes[$codigo_nie]['total_puntos'] += $finalGrade;
                        
                        // Acumuladores por asignatura (si los usas)
                        if (!isset($subjectTotals[$codigo_asignatura])) {
                            $subjectTotals[$codigo_asignatura] = 0;
                        }
                        $subjectTotals[$codigo_asignatura] += $finalGrade;
                    }
                    // --- FIN ACUMULACIÓN ---
                    
                        // --- FIN: Cálculo y Acumulación de Nota ---
                // --- Dibujar cabecera de la tabla de notas (solo la primera vez) ---
                if(!$header_dibujado){
                    // Guardamos la Y actual para el texto rotado
                    $posicion_Y_texto_rotado = $this->fpdf->GetY() + $alto_cell[1]; // Y actual + alto de la cabecera
                    // --- Obtenemos los datos del encabezado (Nivel, Grado) del PRIMER estudiante ---
                        $firstStudent = $EstudianteBoleta[0];
                        $nombre_modalidad_header = mb_convert_encoding(trim($firstStudent->nombre_modalidad),"ISO-8859-1","UTF-8");  
                        $nombre_grado_header = (trim($firstStudent->nombre_grado));  
                        $nombre_seccion_header = mb_convert_encoding(trim($firstStudent->nombre_seccion),"ISO-8859-1","UTF-8");  
                        $nombre_turno_header = mb_convert_encoding(trim($firstStudent->nombre_turno),"ISO-8859-1","UTF-8");
                    // =================================================================
                    // ====== INICIO: DIBUJAR ENCABEZADO DE GRADO (NUEVO BLOQUE) ======
                    // =================================================================

                 // --- INDICADOR 5: POSICIÓN DEL BLOQUE "Información General" ---
            // (Este bloque reemplaza al de "Nivel, Grado, Encargado")
            $info_X = 70;  // Distancia desde la izquierda
            $info_Y = 30;  // Distancia desde arriba (puedes ajustar esto)

            // 1. Prepara los datos para la función
            $data_info_general = [
                'grado' => $nombre_grado_header,
                'seccion' => $nombre_seccion_header,
                'institucion' => $nombre_institucion,
                'direccion' => $direccion_institucion,
                'departamento' => $nombre_departamento, // <-- NUEVO
                'municipio' => $nombre_municipio,
                'distrito' => $nombre_distrito,
                'acuerdo' => $numero_acuerdo // <-- NUEVO
            ];
            
            // 2. (Opcional) Define un layout personalizado
            $layout_info_general = [
                'w_label_1' => 60, // Ancho para "NOMBRE DEL CE" y "DIRECCIÓN"
                'w_label_2' => 30, // Ancho para "DEPARTAMENTO" y "DISTRITO"
                'w_full' => 185,   // Ancho total del bloque
                'h_line' => 4,     // Interlineado
                'font_size_1' => 10,
                'font_size_2' => 9
            ];

            // 3. Llama a la nueva función
            $this->dibujarInformacionGeneral($this->fpdf, $info_X, $info_Y, $data_info_general, $layout_info_general);

                        // 4. Guarda la Y donde terminó este bloque
                        $y_info_fin = $this->fpdf->GetY();
                   
                   // Guardamos la Y actual para el texto rotado
                   $posicion_Y_texto_rotado = $this->fpdf->GetY() + $alto_cell[1]; // Y actual + alto de la cabecera

                    // --- INDICADOR: POSICIÓN DEL BLOQUE "Tabla de Notas" (Componentes, Nómina) ---
                    // Y se calcula automático, +2mm debajo del bloque anterior
                    $table_Y_start = $this->fpdf->GetY() + 2; 
                    // X es la misma del bloque anterior
                    //$table_X_start = $header_info_X; 
                    $table_X_start = 10; 
                    $this->fpdf->SetXY($table_X_start, $table_Y_start);
                    
                    $posicion_Y_texto_rotado = $this->fpdf->GetY() + $alto_cell[1]; // Y actual + alto de la cabecera
                    $this->fpdf->SetFont('Arial', 'B', '7');
                   $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'','LT',0,'L',false);            
                   $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],'','T',0,'L',false); // Columna NIE (estática 12)          
                   $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],'','T',0,'L',false);            
                   
                   // --- MODIFICADO ---
                   // El ancho total del título es ahora el máximo definido
                   $espacio = 0; 
                   $ancho_titulo_asignatura = $max_ancho_asignaturas_total;
                   // --- FIN MODIFICADO ---

                   $this->fpdf->SetFont('Arial', 'B', '10');
                   $this->fpdf->Cell($ancho_titulo_asignatura,$alto_cell[0],'COMPONENTES DEL PLAN DE ESTUDIO',1,1,'C');
                   
                   $this->fpdf->SetX($table_X_start); // Vuelve al X inicial
                   $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'','L',0,'L',false);            
                   $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],'',0,0,'L',false); // Columna NIE (estática 12)             
                   $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],'',0,0,'C');       
                   $this->fpdf->SetFont('Arial', 'B', '10');
                   for ($oi=0; $oi < count($codigo_area_existentes); $oi++) { 
                       $buscar = array_search($codigo_area_existentes[$oi], $datos_asignatura['codigo_area']);
                       $Nombre = $datos_asignatura['nombre_area'][$buscar];
                       
                       // --- MODIFICADO ---
                       // El ancho del área ahora usa la variable dinámica
                       $this->fpdf->Cell($ancho_col_asignatura_dinamico * $ancho_area[$oi],$alto_cell[0],$Nombre,1,0,'C');     
                       // --- FIN MODIFICADO ---
                   }
                   $this->fpdf->ln();
                   
               // --- Encabezado de Nómina y Asignaturas Rotadas (AQUÍ ESTÁ LA CORRECCIÓN) ---
                  
                 // 1. Guarda la posición Y ANTES de dibujar la fila de cabecera
                  $y_header_row_start = $this->fpdf->GetY();
                  $y_header_row_start_2 = $this->fpdf->GetY();
                  $this->fpdf->SetX($table_X_start); // Vuelve al X inicial
                  $this->fpdf->Cell($ancho_cell[1],$alto_cell[1],mb_convert_encoding('N.º',"ISO-8859-1","UTF-8"),1,0,'C',false);            
                  $this->fpdf->Cell($ancho_cell[4],$alto_cell[1],'NIE',1,0,'C',false); // Columna NIE (estática 12)             
                  $this->fpdf->Cell($ancho_cell[0],$alto_cell[1],"NOMINA DE ESTUDIANTES",1,0,'C');       
                  
                  // 2. Calcula la Y final (la parte de abajo de la cabecera)
                  $y_header_row_end = $y_header_row_start + $alto_cell[1];

                  $x_rotado_inicio = $table_X_start + $ancho_cell[1] + $ancho_cell[4] + $ancho_cell[0];
                  
                  // --- MODIFICADO ---
                  // Se comenta el ancho fijo
                  // $ancho_col_asignatura = 12; // $mas_ancho
                  // --- FIN MODIFICADO ---
                  
                  // --- INDICADOR: CONTROL DE INTERLINEADO DE ASIGNATURAS ---
                  // Este es el "interlineado" (alto de línea) del texto rotado.
                  // La fuente es 7. Un alto de 4mm es justo.
                  // Prueba con 3.5 o 3 para que esté más "junto".
                  $alto_linea_asignatura = 3; // (en mm)
                  // -----------------------------------------------------

                  // Ajuste X/Y del texto DENTRO de la caja rotada:
                    // (Estos son los valores que querías controlar)
                    
                    // Padding Vertical (X): Distancia desde el borde superior (que ahora es la izquierda)
                    // (Valores más altos mueven el texto hacia abajo/derecha)
                    $rotado_padding_X = -40; 
                    
                    // Padding Horizontal (Y): Distancia desde el borde derecho (que ahora es arriba)
                    // (Valores más altos mueven el texto hacia la izquierda/arriba)
                // --- BLOQUE 2: AJUSTE DINÁMICO DE PADDING Y ---
                    // Padding Horizontal (Y): Distancia desde el borde derecho (que ahora es arriba)
                    // (Valores más altos mueven el texto hacia abajo)
                    // (Valores más pequeños mueven el texto hacia arriba)
                    
                    // $total_asignaturas ya se calculó anteriormente
                    
                    switch ($total_asignaturas) {
                        case 42:
                            $rotado_padding_Y = 2;
                            break;
                        case 43:
                            $rotado_padding_Y = 0;
                            break;
                        case 9:
                            $rotado_padding_Y = 25;
                            break;
                        case 14:
                            $rotado_padding_Y = 15;
                            break;
                        
                        // --- INICIO: Casos Manuales ---
                        // Agrega más 'case' aquí según necesites
                        case 10:
                            $rotado_padding_Y = 22;
                            break;
                         case 20:
                             $rotado_padding_Y = 9;
                             break;
                         case 6:
                             $rotado_padding_Y = 40;
                             break;
                        
                        // --- FIN: Casos Manuales ---
                        
                        default:
                            // Este es el valor por defecto si no coincide ningún 'case'
                            $rotado_padding_Y = 15; 
                            break;
                    }
                    // --- FIN DEL BLOQUE 2 ---


                    for ($ij=0; $ij < count($datos_asignatura['nombre']); $ij++) { 
                        
                        // --- MODIFICADO ---
                        // El ancho de la columna ahora es dinámico
                        $ancho_col = $ancho_col_asignatura_dinamico;
                        // --- FIN MODIFICADO ---

                        $x_actual = $x_rotado_inicio + ($ij * $ancho_col);

                        // 1. Dibuja el RECTÁNGULO (caja)
                        $this->fpdf->Rect($x_actual, $y_header_row_start_2, $ancho_col, $alto_cell[1]);
                        $this->fpdf->SetFont('Arial', '', '7');
                        
                        // 2. Inicia la rotación (gira 90 grados)
                        $this->fpdf->Rotate(90, $x_actual, $y_header_row_start_2);
                        
                        // 3. Calcula la posición X,Y del MultiCell usando tus indicadores
                        $pos_X_rotada = $x_actual + $rotado_padding_X;
                        // --- MODIFICADO ---
                        // El cálculo de Y usa el ancho dinámico
                        $pos_Y_rotada = $y_header_row_start_2 - $ancho_col + $rotado_padding_Y;
                        // --- FIN MODIFICADO ---
                        
                        $this->fpdf->SetXY($pos_X_rotada, $pos_Y_rotada); 
                        
                        // 4. Dibuja el MultiCell con el interlineado
                        // Ancho de MultiCell = Alto de la caja | Alto de Línea = Interlineado
                        $this->fpdf->MultiCell($alto_cell[1] - 2, $alto_linea_asignatura, $datos_asignatura['nombre'][$ij], 0, 'C');
                        
                        // 5. Detiene la rotación
                        $this->fpdf->Rotate(0);
                    }
              
                  // 7. Establece la posición Y para la primera fila de datos
                  $this->fpdf->SetXY($table_X_start, $y_header_row_end); 
                  $header_dibujado = true; // Marcar como dibujado
              }
                
              // --- Dibujar fila de notas del estudiante (BLOQUE CORREGIDO) ---
                
                $this->fpdf->SetTextColor(0,0,0);
                $this->fpdf->SetFont('Arial', '', 7);

                // 1. Lógica de fin de fila (se mueve al INICIO)
                // Si la fila anterior de notas se completó...
                if($fila_asignatura == $total_asignaturas){
                    $this->fpdf->ln(); // Salta la línea
                    $fila_asignatura = 0; // Reinicia el contador de notas
                    $fila_numero++; // Siguiente estudiante
                    $fill = !$fill; // Alterna el color para ESTA nueva fila
                }
                
                // 2. Establece el color de fondo basado en $fill
                if ($fill) {
                    $this->fpdf->SetFillColor(255, 255, 255); // Blanco
                } else {
                    $this->fpdf->SetFillColor(212, 230, 252); // Azul claro
                }

                // 3. Dibuja N, NIE, Nombre (si es la primera celda)
                if($fila_asignatura == 0){
                    $this->fpdf->SetX($table_X_start);
                    // Dibuja las celdas usando 'true' para el parámetro de fondo
                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$fila_numero,1,0,'L',true); 
                    $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],$codigo_nie,1,0,'L',true); 
                    $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],$nombre_estudiante,1,0,'L',true); 
                }
                
                // 4. Dibuja la celda de nota (siempre usando 'true' para el fondo)
                if($codigo_area == '07'){
                    $result_concepto = resultado_concepto($codigo_modalidad, $nota_final); 
                    if($result_concepto == "R"){ $this->fpdf->SetTextColor(255,0,0); } 
                    $this->fpdf->Cell($ancho_col_asignatura_dinamico,$alto_cell[0],$result_concepto,1,0,'C', true);
                } else { 
                    $result = resultado_final($codigo_modalidad, $nota_recuperacion_1, $nota_recuperacion_2, $nota_final,$codigo_area); 
                    if($result[0] == "R"){ $this->fpdf->SetTextColor(255,0,0); } 
                    $this->fpdf->Cell($ancho_col_asignatura_dinamico,$alto_cell[0],round($result[1],0),1,0,'C', true); 
                }
                
                $fila_asignatura++; $fila++; 
                $this->fpdf->SetTextColor(0,0,0); // Resetea el color del texto para la siguiente celda
                // --- FIN DEL BLOQUE CORREGIDO ---

            } // FIN DEL FOREACH
            $this->fpdf->ln();
        // ... (Tu lógica para $linea_faltante, $fill=!$fill, etc.) ...
            // --- BLOQUE 1: MODIFICACIÓN DE FILAS DE RELLENO ---
            
            // $fila_numero ya tiene el siguiente número (ej: si hay 22 estudiantes, $fila_numero es 23)
            $numero_maximo_filas = 50; 

            // El bucle ahora va desde el siguiente estudiante ($fila_numero) hasta 50
            for ($i = $fila_numero+1; $i <= $numero_maximo_filas; $i++) {
                $this->fpdf->SetX($table_X_start); // Asegura que empiece en el X de la tabla
                
                // 1. Dibuja el número de fila
                $this->fpdf->Cell($ancho_cell[1],$alto_cell[0], $i, 1, 0, 'L', $fill); // Dibuja $i (el número)
                
                // 2. Celdas vacías (NIE y Nombre)
                $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],'',1,0,'L',$fill);            
                $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],'',1,0,'L',$fill); 
                
                // 3. Celdas de notas (dinámicas)
                for($j=1;$j<=$total_asignaturas;$j++){
                    $this->fpdf->Cell($ancho_col_asignatura_dinamico,$alto_cell[0],'',1,0,'L',$fill);            
                }
                
                $this->fpdf->ln();
                $fill=!$fill; // Alterna el color para la siguiente fila
            }
            // --- FIN DEL BLOQUE 1 ---


// =================================================================
        // ====== INICIO: DIBUJAR FILAS DE TOTALES Y PROMEDIO (NUEVO) ======
        // =================================================================
        // Capturamos la Y después de la última fila de relleno
        $Y_despues_de_relleno = $this->fpdf->GetY();
        $this->fpdf->SetFont('Arial', 'B', 7);
        $this->fpdf->SetFillColor(230, 230, 230); // Gris claro
        
        // --- MODIFICADO ---
        // Se comenta el ancho fijo
        // $ancho_col_asignatura = 12; // $mas_ancho (Asegúrate que sea 12)
        // --- FIN MODIFICADO ---


        // --- Fila TOTAL DE PUNTOS ---
        $this->fpdf->SetY($Y_despues_de_relleno);
        $this->fpdf->SetX($table_X_start);
        $this->fpdf->Cell($ancho_cell[1], $alto_cell[0], '', 1, 0, 'L', true); // Celda vacía para N.º
        $this->fpdf->Cell($ancho_cell[4], $alto_cell[0], '', 1, 0, 'L', true); // Celda vacía para NIE (estática 12)
        $this->fpdf->Cell($ancho_cell[0], $alto_cell[0], 'TOTAL DE PUNTOS', 1, 0, 'R', true); // Título
        
        // Iteramos sobre las asignaturas en el orden del encabezado
        foreach ($datos_asignatura['codigo'] as $index => $codigo_asig_header) {
            if (empty($codigo_asig_header)) continue; // Omite el [0]=>"" del array
            
            $codigo_area_header = $datos_asignatura['codigo_area'][$index];

            // Comprueba si el área de esta asignatura es una de las relevantes
            if (in_array($codigo_area_header, $relevantAreas)) {
                $total = $subjectTotals[$codigo_asig_header] ?? 0;
                
                // --- MODIFICADO ---
                // Se usa el ancho dinámico
                $this->fpdf->Cell($ancho_col_asignatura_dinamico, $alto_cell[0], $total, 1, 0, 'C', true);
                // --- FIN MODIFICADO ---
            } else {
                // Si no es relevante (ej. Competencias), deja la celda vacía
                
                // --- MODIFICADO ---
                // Se usa el ancho dinámico
                $this->fpdf->Cell($ancho_col_asignatura_dinamico, $alto_cell[0], '', 1, 0, 'C', true);
                // --- FIN MODIFICADO ---
            }
        }
        $this->fpdf->ln(); // Nueva línea

        // --- Fila PROMEDIO ---
        $this->fpdf->SetX($table_X_start);
        $this->fpdf->Cell($ancho_cell[1], $alto_cell[0], '', 1, 0, 'L', true); // Celda vacía para N.º
        $this->fpdf->Cell($ancho_cell[4], $alto_cell[0], '', 1, 0, 'L', true); // Celda vacía para NIE (estática 12)
        $this->fpdf->Cell($ancho_cell[0], $alto_cell[0], 'PROMEDIO', 1, 0, 'R', true); // Título
        
        // $fila_numero es 1 más que el total de estudiantes, así que restamos 1
        $total_estudiantes = $fila_numero - 1; 
        if ($total_estudiantes == 0) $total_estudiantes = 1; // Evitar división por cero

        foreach ($datos_asignatura['codigo'] as $index => $codigo_asig_header) {
            if (empty($codigo_asig_header)) continue;
            
            $codigo_area_header = $datos_asignatura['codigo_area'][$index];

            if (in_array($codigo_area_header, $relevantAreas)) {
                $total = $subjectTotals[$codigo_asig_header] ?? 0;
                // Usamos el total de estudiantes de la nómina para promediar
                $average = round($total / $total_estudiantes, 1); 
                
                // --- MODIFICADO ---
                // Se usa el ancho dinámico
                $this->fpdf->Cell($ancho_col_asignatura_dinamico, $alto_cell[0], $average, 1, 0, 'C', true);
                // --- FIN MODIFICADO ---
            } else {
                
                // --- MODIFICADO ---
                // Se usa el ancho dinámico
                $this->fpdf->Cell($ancho_col_asignatura_dinamico, $alto_cell[0], '', 1, 0, 'C', true);
                // --- FIN MODIFICADO ---
            }
        }
        $this->fpdf->ln();
        
        // =================================================================
        // ====== FIN: DIBUJAR FILAS DE TOTALES ======
        // =================================================================


        // =================================================================
        // ====== INICIO: BLOQUE FINAL (LUGAR, FECHA, FIRMAS, SELLO) ======
        // =================================================================
        
            // Comprobar si hay espacio, si no, añadir nueva página
            if($this->fpdf->GetY() > 150) {
                //$this->fpdf->AddPage();
                $this->fpdf->SetY(20);
            } else {
                $this->fpdf->SetY($this->fpdf->GetY()); // Espacio después de la tabla
            }

            // --- Prepara los textos ---
            // 1. Formateador para números (Día y Año)
            $fmt_numero = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
                        
            // 2. Formateador para el Mes (ej: " de octubre de ")
            $fmt_mes = new \IntlDateFormatter('es_ES', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE, null, null, " 'de' MMMM 'de' ");

            // 3. Obtener las partes
            $dia_palabras = $fmt_numero->format(date('d')); // ej: "veintinueve"
            $mes_palabras = $fmt_mes->format(time());      // ej: " de octubre de "
            $ano_palabras = $fmt_numero->format(date('Y')); // ej: "dos mil veinticinco"

            // 4. Concatenar todo
            $fecha_en_palabras = $dia_palabras . $mes_palabras . $ano_palabras;

            // 5. Convertir para FPDF
            $fecha_en_palabras = mb_convert_encoding(ucfirst($fecha_en_palabras), 'ISO-8859-1', 'UTF-8');
            $textos_lugar_fecha = [
                'lugar' => mb_convert_encoding('Santa Ana', 'ISO-8859-1', 'UTF-8'),
                'fecha' => $fecha_en_palabras
            ];
            $textos_director = [
                'nombre' => $nombre_director, // Ya la tenías de una consulta anterior
                'titulo' => mb_convert_encoding('Director(a)', 'ISO-8859-1', 'UTF-8')
            ];
            $textos_docente = [
                'nombre' => $nombre_personal_, // Ya la tenías (Encargado de Grado)
                'titulo' => mb_convert_encoding('Docente responsable', 'ISO-8859-1', 'UTF-8')
            ];

            // --- INDICADOR: POSICIÓN DEL BLOQUE "Lugar y Fecha" ---
            $lugar_fecha_X = 260;
            $lugar_fecha_Y = $this->fpdf->GetY();
            $layout_lugar_fecha = [
                'w_label' => 10, 'w_line' => 70, 'h_line' => 10, 'font_size' => 10
            ];
            $this->dibujarLugarFecha($this->fpdf, $lugar_fecha_X, $lugar_fecha_Y, $textos_lugar_fecha, $layout_lugar_fecha);
            
            // --- INDICADOR: POSICIÓN DEL BLOQUE "Firma Director" ---
            // (Alineado a la derecha)
            $firma_director_X = 260;
            $firma_director_Y = $this->fpdf->GetY() + 50; // 15mm debajo de la fecha
            $layout_firma_director = [
                'w_linea' => 80, 'h_gap' => 5, 'font_size_nombre' => 9, 'font_size_titulo' => 8
            ];
            $this->dibujarFirma($this->fpdf, $firma_director_X, $firma_director_Y, $textos_director, $layout_firma_director);

            // --- INDICADOR: POSICIÓN DEL BLOQUE "Firma Docente" ---
            // (Alineado a la derecha)
            $firma_docente_X = 260;
            $firma_docente_Y = $firma_director_Y - 25; // 25mm debajo del Director
            $layout_firma_docente = [
                'w_linea' => 80, 'h_gap' => 5, 'font_size_nombre' => 9, 'font_size_titulo' => 8
            ];
            $this->dibujarFirma($this->fpdf, $firma_docente_X, $firma_docente_Y, $textos_docente, $layout_firma_docente);

            // --- INDICADOR: POSICIÓN DEL BLOQUE "Sello" ---
            $sello_X = 283; // A la izquierda de las firmas
            $sello_Y = $firma_director_Y + 25; // 5mm debajo de la línea del director
            $sello_W = 40; // Ancho del sello
            $sello_H = 40; // Alto del sello
            $this->dibujarSello($this->fpdf, $sello_X, $sello_Y, $sello_W, $sello_H);

            // Imágenes (Las posicionamos manualmente cerca de las firmas)
            if (file_exists($firma_director_path)) {
               // $this->fpdf->Image($firma_director_path, $firma_director_X + 20, $firma_director_Y - 15, 40, 15);
            }
            if (file_exists($sello_direccion_path)) {
                //$this->fpdf->Image($sello_direccion_path, $sello_X + 7.5, $sello_Y + 7.5, 25, 25);
            }
            if(!empty($firma_docente)){
                $firma_docente_path_abs = public_path('img/firmas/'.$codigo_institucion_infra.'/'.$firma_docente);
                if (file_exists($firma_docente_path_abs)) {
                //    $this->fpdf->Image($firma_docente_path_abs, $firma_docente_X + 27.5, $firma_docente_Y - 20, 25, 30);
                }
            }

        // --- Fecha y Hora Final ---
            // (Esta parte ya estaba bien)
            $this->fpdf->SetY($this->fpdf->GetPageHeight() - 15);
            $this->fpdf->SetFont('Arial', 'I', 8);
            $this->fpdf->Cell(0, 10, mb_convert_encoding('Generado el: ' . date('d/m/Y H:i:s'), 'ISO-8859-1', 'UTF-8'), 0, 0, 'R');

// =================================================================
        // ====== INICIO: PÁGINA DE CUADRO DE HONOR (RANKING) ======
        // =================================================================
        
        if (!empty($ranking_estudiantes)) {
            // 1. Ordenar el array por 'total_puntos' de mayor a menor (Descendente)
            usort($ranking_estudiantes, function($a, $b) {
                return $b['total_puntos'] <=> $a['total_puntos'];
            });

            // 2. Nueva Página
            $this->fpdf->AddPage();
            $this->fpdf->SetMargins(20, 20, 20);
            $this->fpdf->SetY(20);

            // 3. Título de la Página
            $this->fpdf->SetFont('Arial', 'B', 14);
            $this->fpdf->Cell(0, 10, mb_convert_encoding('CUADRO DE HONOR - PRIMEROS LUGARES', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
            $this->fpdf->SetFont('Arial', '', 10);
            $this->fpdf->Cell(0, 6, mb_convert_encoding("Grado: $nombre_grado_header   Sección: $nombre_seccion_header", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
            $this->fpdf->Ln(10);

            // 4. Configuración de la Tabla
            $w_rank = 20;
            $w_nie = 30;
            $w_nom = 120; // Más espacio para el nombre
            $w_pts = 40;
            $h_row_rank = 8;

            // Cabecera de Tabla
            $this->fpdf->SetFont('Arial', 'B', 10);
            $this->fpdf->SetFillColor(200, 200, 200);
            $this->fpdf->Cell($w_rank, $h_row_rank, mb_convert_encoding('N.º', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
            $this->fpdf->Cell($w_nie, $h_row_rank, 'NIE', 1, 0, 'C', true);
            $this->fpdf->Cell($w_nom, $h_row_rank, 'NOMBRE DEL ESTUDIANTE', 1, 0, 'C', true);
            $this->fpdf->Cell($w_pts, $h_row_rank, 'TOTAL PUNTOS', 1, 1, 'C', true);

            // 5. Dibujar Filas
            $this->fpdf->SetFont('Arial', '', 10);
            $posicion = 1;
            
            foreach ($ranking_estudiantes as $estudiante) {
                // Destacar los primeros 3 lugares
                if ($posicion <= 3) {
                    $this->fpdf->SetFont('Arial', 'B', 10); // Negrita para el Top 3
                } else {
                    $this->fpdf->SetFont('Arial', '', 10);
                }

                $this->fpdf->Cell($w_rank, $h_row_rank, $posicion, 1, 0, 'C');
                $this->fpdf->Cell($w_nie, $h_row_rank, $estudiante['nie'], 1, 0, 'C');
                $this->fpdf->Cell($w_nom, $h_row_rank, $estudiante['nombre'], 1, 0, 'L');
                
                // Puntos con 1 decimal
                $this->fpdf->Cell($w_pts, $h_row_rank, number_format($estudiante['total_puntos'], 0), 1, 1, 'C');

                $posicion++;
            }
            
            // Pie de página simple para esta hoja
            $this->fpdf->Ln(5);
            $this->fpdf->SetFont('Arial', 'I', 8);
            $this->fpdf->Cell(0, 5, mb_convert_encoding('* Total de puntos calculado en base a asignaturas básicas/relevantes.', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        }
        // =================================================================
        // ====== FIN: PÁGINA DE CUADRO DE HONOR ======
        // =================================================================

        // Construir el nombre del archivo.
            $nombre_archivo = $nombre_modalidad.' '.$nombre_grado . ' ' . $nombre_seccion . ' ' . $nombre_turno . '.pdf';
        // Salida del pdf.
            $modo = 'I'; // Envia al navegador (I)
            $this->fpdf->Output($nombre_archivo,$modo);
                exit;
    }
}

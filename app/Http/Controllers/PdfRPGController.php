<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;
use GuzzleHttp\Psr7\Header;
use Illuminate\Support\Facades\DB;
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
            foreach ($studentsForPromotion as $grade) { $studentsGrades[$grade->id_alumno_matricula]['gender'] = (trim($grade->codigo_genero) == '01') ? 'M' : 'F'; $studentsGrades[$grade->id_alumno_matricula]['grades'][] = [ 'rec_1' => $grade->recuperacion, 'rec_2' => $grade->nota_recuperacion_2, 'final' => $grade->nota_final, 'area' => $grade->codigo_area ]; }
            foreach ($studentsGrades as $matricula_id => $student) { $isRetenido = false; $gender = $student['gender']; if (empty($student['grades'])) { $isRetenido = true; } else { foreach ($student['grades'] as $grade) { $result = resultado_final( $codigo_modalidad, $grade['rec_1'], $grade['rec_2'], $grade['final'], $grade['area'] ); if ($result[0] == 'R') { $isRetenido = true; break; } } } if ($isRetenido) { $stats[$gender]['retenidos']++; $stats['Total']['retenidos']++; } else { $stats[$gender]['promovidos']++; $stats['Total']['promovidos']++; } }

        // ... (pega aquí tus consultas de $catalogo_area_asignatura, $AsignacionAsignatura, $EncargadoGrado, $EncargadoAsignatura) ...
            $catalogo_area_asignatura_codigo = array();	$catalogo_area_asignatura_area = array();
            $catalogo_area_basica = true; $catalogo_area_formativa = true; $catalogo_area_tecnica = true; $catalogo_area_edps = true; $catalogo_area_edecr = true; $catalogo_area_edre = true; $catalogo_area_complementaria = true; $catalogo_area_cc = true; $catalogo_area_alertas = true;
            $alto_cell = array('5','40'); $ancho_cell = array('60','6','24','30','12');        
            $CatalogoAreaAsignatura = DB::table('catalogo_area_asignatura')->select('codigo','descripcion')->get();
                foreach($CatalogoAreaAsignatura as $response_area){  $catalogo_area_asignatura_codigo[] = (trim($response_area->codigo)); $catalogo_area_asignatura_area[] = (trim($response_area->descripcion)); }
            $AsignacionAsignatura = DB::table('a_a_a_bach_o_ciclo as aaa')->join('asignatura as a','a.codigo','=','aaa.codigo_asignatura')->join('catalogo_area_asignatura AS cat_area','cat_area.codigo','=','a.codigo_area')
                    ->select('aaa.orden','a.nombre as nombre_asignatura','a.codigo as codigo_asignatura','a.codigo_cc as concepto_calificacion','a.codigo_area','cat_area.descripcion as nombre_area')
                ->where([['codigo_bach_o_ciclo', '=', $codigo_modalidad],['codigo_grado', '=', $codigo_grado],['codigo_ann_lectivo', '=', $codigo_annlectivo],])->orderBy('aaa.orden','asc')->orderBy('a.codigo_area','asc')->get();
                $datos_asignatura = array(); $fila_array_asignatura = 0; $count_asignaturas = array();
                $datos_asignatura = [ "codigo" => [""], "nombre" => [""], "concepto" => [""], "codigo_area" => [""], "nombre_area" => [""] ];       
                foreach($AsignacionAsignatura as $response_i){  
                    $nombre_asignatura_a = mb_convert_encoding(trim($response_i->nombre_asignatura),"ISO-8859-1","UTF-8"); $codigo_asignatura_a = mb_convert_encoding(trim($response_i->codigo_asignatura),"ISO-8859-1","UTF-8");
                    $concepto_calificacion_a = mb_convert_encoding(trim($response_i->concepto_calificacion),"ISO-8859-1","UTF-8"); $codigo_area_a = mb_convert_encoding(trim($response_i->codigo_area),"ISO-8859-1","UTF-8");
                    $nombre_area_a = mb_convert_encoding(trim($response_i->nombre_area),"ISO-8859-1","UTF-8"); $count_asignaturas[] = $codigo_area_a;
                    $datos_asignatura["codigo"][$fila_array_asignatura] = $codigo_asignatura_a; $datos_asignatura["nombre"][$fila_array_asignatura] = $nombre_asignatura_a;
                    $datos_asignatura["concepto"][$fila_array_asignatura] = $concepto_calificacion_a; $datos_asignatura["codigo_area"][$fila_array_asignatura] = $codigo_area_a; $datos_asignatura["nombre_area"][$fila_array_asignatura] = $nombre_area_a;                        
                    $total_asignaturas = count($count_asignaturas); $fila_array_asignatura++; }
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
        // ====== INICIO: DIBUJAR ENCABEZADOS Y ESTADÍSTICAS ======
        // =================================================================

        // --- Consulta de Información de la Institución ---
            $EstudianteInformacionInstitucion = DB::table('informacion_institucion as inf')
                ->leftjoin('personal as p','p.codigo_cargo','=','inf.nombre_director')
                ->select('inf.id_institucion','inf.codigo_institucion','inf.nombre_institucion','inf.telefono_uno','inf.logo_uno','inf.direccion_institucion','inf.nombre_director',
                            'inf.logo_dos','inf.logo_tres',
                        DB::raw("TRIM(CONCAT(BTRIM(p.nombres), CAST(' ' AS VARCHAR), BTRIM(p.apellidos))) as full_name"),
                        )
                ->where('id_institucion', '=', $codigo_institucion)->orderBy('id_institucion','asc')->limit(1)->get();
            
            $logo_uno_path = ''; $firma_director_path = ''; $sello_direccion_path = '';
            $nombre_modalidad_header = ''; $nombre_grado_header = ''; $nombre_seccion_header = ''; $nombre_turno_header = '';

            // --- Dibujar Encabezado Izquierdo (Institución) ---
            foreach($EstudianteInformacionInstitucion as $response_i){  
                $nombre_institucion = mb_convert_encoding(trim($response_i->nombre_institucion),"ISO-8859-1","UTF-8");
                $nombre_director = mb_convert_encoding(trim($response_i->full_name),"ISO-8859-1","UTF-8");
                $codigo_institucion_infra = mb_convert_encoding(trim($response_i->codigo_institucion),"ISO-8859-1","UTF-8");
                $logo_uno_path = public_path('img/' . trim($response_i->logo_uno));
                $firma_director_path = public_path('img/' . trim($response_i->logo_dos));
                $sello_direccion_path = public_path('img/' . trim($response_i->logo_tres));
                
                // --- INDICADOR: POSICIÓN DEL BLOQUE DE INFO INSTITUCIÓN ---
                // X = Distancia desde la izquierda | Y = Distancia desde arriba
                $ce_X = 10;
                $ce_Y = 10;
                $this->fpdf->SetXY($ce_X, $ce_Y); 
                if (file_exists($logo_uno_path)) {
                    $this->fpdf->image($logo_uno_path, $this->fpdf->GetX(), $this->fpdf->GetY(), 15, 20);
                }
                
                $this->fpdf->SetXY($ce_X + 17, $ce_Y); // 10 (margen) + 15 (logo) + 2 (espacio)
                $this->fpdf->Cell(40, $alto_cell[0],"CENTRO ESCOLAR:",1,0,'L');       
                $this->fpdf->Cell(135, $alto_cell[0],$codigo_institucion_infra . " - " .$nombre_institucion,1,1,'L');       
            }
// --- Dibujar Encabezado Derecho (Estadísticas) ---
            
            // --- INDICADOR: CONTROL MANUAL DE ESTADÍSTICAS ---
            
            // 1. Mueve la tabla completa cambiando X (horizontal) e Y (vertical)
            $stats_X = 260; // Distancia desde la izquierda
            $stats_Y = 10;  // Distancia desde arriba

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
            
            //$y_stats_fin = $this->fpdf->GetY(); // Guarda dónde terminó la tabla de stats

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
                            ['codigo_grado', '=', $codigo_grado],
                            ['codigo_seccion', '=', $codigo_seccion],
                            ['codigo_turno', '=', $codigo_turno],
                            ['codigo_ann_lectivo', '=', $codigo_annlectivo],
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

                // --- Dibujar cabecera de la tabla de notas (solo la primera vez) ---
                if(!$header_dibujado){
                    // Guardamos la Y actual para el texto rotado
                    $posicion_Y_texto_rotado = $this->fpdf->GetY() + $alto_cell[1]; // Y actual + alto de la cabecera
                    // --- Obtenemos los datos del encabezado (Nivel, Grado) del PRIMER estudiante ---
                        $firstStudent = $EstudianteBoleta[0];
                        $nombre_modalidad_header = mb_convert_encoding(trim($firstStudent->nombre_modalidad),"ISO-8859-1","UTF-8");  
                        $nombre_grado_header = mb_convert_encoding(trim($firstStudent->nombre_grado),"ISO-8859-1","UTF-8");  
                        $nombre_seccion_header = mb_convert_encoding(trim($firstStudent->nombre_seccion),"ISO-8859-1","UTF-8");  
                        $nombre_turno_header = mb_convert_encoding(trim($firstStudent->nombre_turno),"ISO-8859-1","UTF-8");
                    // =================================================================
                    // ====== INICIO: DIBUJAR ENCABEZADO DE GRADO (NUEVO BLOQUE) ======
                    // =================================================================

                    // --- INDICADOR: POSICIÓN DEL BLOQUE "Nivel, Grado, Encargado" ---
                    // Modifica estos X/Y para mover este bloque independientemente
                    $header_info_X = 27;  // Distancia desde la izquierda
                    $header_info_Y = 10; // Distancia desde arriba (debajo del logo)
                    $this->fpdf->SetXY($header_info_X, $header_info_Y);
                    
                    $this->fpdf->SetFont('Arial', 'B', 9);
                    $this->fpdf->Cell(40,$alto_cell[0],mb_convert_encoding("Nivel","ISO-8859-1","UTF-8"),1,0,'L'); 
                    $this->fpdf->Cell(135,$alto_cell[0],$nombre_modalidad_header,1,1,'L'); 
                    $this->fpdf->SetX($header_info_X); // Vuelve al X inicial
                    $this->fpdf->Cell(15,$alto_cell[0],"Grado",1,0,'L'); 
                    $this->fpdf->Cell(70,$alto_cell[0],$nombre_grado_header,1,0,'L'); 
                    $this->fpdf->Cell(15,$alto_cell[0],mb_convert_encoding("Sección","ISO-8859-1","UTF-8"),1,0,'L'); 
                    $this->fpdf->Cell(10,$alto_cell[0],$nombre_seccion_header,1,0,'C'); 
                    $this->fpdf->Cell(20,$alto_cell[0],"Turno",1,0,'L'); 
                    $this->fpdf->Cell(45,$alto_cell[0],$nombre_turno_header,1,1,'C'); 
                    $this->fpdf->SetX($header_info_X); // Vuelve al X inicial
                    $this->fpdf->Cell(55,$alto_cell[0],"Encargado de Grado: ",1,0,'L'); 
                    $this->fpdf->Cell(120,$alto_cell[0],$nombre_personal_,1,1,'L');
                   $this->fpdf->ln(); 
                   
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
                   $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],'','T',0,'L',false);            
                   $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],'','T',0,'L',false);            
                   $mas_ancho = 12; $espacio = 0; 
                   $ancho_titulo_asignatura = count($datos_asignatura['nombre']) * $mas_ancho;
                   $this->fpdf->SetFont('Arial', 'B', '10');
                   $this->fpdf->Cell($ancho_titulo_asignatura,$alto_cell[0],'COMPONENTES DEL PLAN DE ESTUDIO',1,1,'C');
                   
                   $this->fpdf->SetX($table_X_start); // Vuelve al X inicial
                   $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'','L',0,'L',false);            
                   $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],'',0,0,'L',false);            
                   $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],'',0,0,'C');       
                   $this->fpdf->SetFont('Arial', 'B', '10');
                   for ($oi=0; $oi < count($codigo_area_existentes); $oi++) { 
                       $buscar = array_search($codigo_area_existentes[$oi], $datos_asignatura['codigo_area']);
                       $Nombre = $datos_asignatura['nombre_area'][$buscar];
                       $this->fpdf->Cell($mas_ancho*$ancho_area[$oi],$alto_cell[0],$Nombre,1,0,'C');     
                   }
                   $this->fpdf->ln();
                   
                  // --- Encabezado de Nómina y Asignaturas Rotadas ---
                  $this->fpdf->SetX($table_X_start); // Vuelve al X inicial
                  $this->fpdf->Cell($ancho_cell[1],$alto_cell[1],mb_convert_encoding('N.º',"ISO-8859-1","UTF-8"),1,0,'C',false);            
                  $this->fpdf->Cell($ancho_cell[4],$alto_cell[1],'NIE',1,0,'C',false);            
                  $this->fpdf->Cell($ancho_cell[0],$alto_cell[1],"NOMINA DE ESTUDIANTES",1,0,'C');       
                  
                  $x_rotado_inicio = $table_X_start + $ancho_cell[1] + $ancho_cell[4] + $ancho_cell[0];
                  $ancho_col_asignatura = 12;
                  
                  for ($ij=0; $ij < count($datos_asignatura['nombre']); $ij++) { 
                      $ancho_col = $ancho_col_asignatura;
                      $x_actual = $x_rotado_inicio + ($ij * $ancho_col);
                      $this->fpdf->Rect($x_actual, $this->fpdf->GetY(), $ancho_col, $alto_cell[1]);
                      $this->fpdf->SetFont('Arial', '', '9');
                      $this->fpdf->RotatedTextMultiCell($x_actual + ($ancho_col/2), $posicion_Y_texto_rotado - 1, $datos_asignatura['nombre'][$ij], 90);
                  }
              
                  $this->fpdf->SetXY($table_X_start, $posicion_Y_texto_rotado); // Posiciona el cursor al inicio de la lista
                  $header_dibujado = true; // Marcar como dibujado
              }
                
                // --- Dibujar fila de notas del estudiante ---
                // ... (toda tu lógica de $fill, $fila_asignatura, $fila_numero, etc.) ...
                // (Esta parte es idéntica a tu código original)
                $this->fpdf->SetFillColor(212, 230, 252); $this->fpdf->SetTextColor(0,0,0); $this->fpdf->SetFont('Arial', '', 7);
                if($fila_asignatura == $total_asignaturas){ $this->fpdf->ln(); $fila_asignatura = 0; $fila_numero++; $fill=!$fill; }
                if($fila_asignatura == 0){
                    $this->fpdf->SetX($table_X_start); // Asegura que empiece en el X de la tabla
                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$fila_numero,1,0,'L',$fill);            
                    $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],$codigo_nie,1,0,'L',$fill);            
                    $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],$nombre_estudiante,1,0,'L',$fill); 
                }
                if($codigo_area == '07'){ $result_concepto = resultado_concepto($codigo_modalidad, $nota_final); if($result_concepto == "R"){ $this->fpdf->SetTextColor(255,0,0); } $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],$result_concepto,1,0,'C', $fill);
                } else { $result = resultado_final($codigo_modalidad, $nota_recuperacion_1, $nota_recuperacion_2, $nota_final,$codigo_area); if($result[0] == "R"){ $this->fpdf->SetTextColor(255,0,0); } $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],round($result[1],0),1,0,'C', $fill); }
                $fila_asignatura++; $fila++; 
                $this->fpdf->SetTextColor(0,0,0); $this->fpdf->SetFillColor(212,230,252);

            } // FIN DEL FOREACH

        // ... (Tu lógica para $linea_faltante, $fill=!$fill, etc.) ...
            $numero = $fila_numero; $linea_faltante = 0;
            $this->fpdf->Ln();
            if($numero > 25){ $linea_faltante = 50 - $numero; } else { $linea_faltante = 25 - $numero; } // Ajuste
            for($i=0;$i<=$linea_faltante;$i++) {
                $this->fpdf->SetX($table_X_start); // Asegura que empiece en el X de la tabla
                $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'',1,0,'L',$fill);            
                $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],'',1,0,'L',$fill);            
                $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],'',1,0,'L',$fill); 
                $fill=!$fill;
                for($j=1;$j<=$total_asignaturas;$j++){
                    $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],'',1,0,'L',$fill);            
                }
                $this->fpdf->ln();
            }

        // =================================================================
        // ====== INICIO: FIRMAS ALINEADAS A LA DERECHA (MODIFICADO) ======
        // =================================================================
        
            // Comprobar si hay espacio, si no, añadir nueva página
            if($this->fpdf->GetY() > 150) { // 150mm es un umbral, ajústalo si es necesario
                $this->fpdf->AddPage();
                $this->fpdf->SetY(20); // Posición Y en la nueva página
            } else {
                $this->fpdf->SetY($this->fpdf->GetY() + 30); // Espacio después de la tabla
            }
            
            // --- INDICADOR: POSICIÓN DE FIRMAS ---
            // Ajusta estos valores X (distancia desde la izquierda) para mover
            // los bloques de firma. (El ancho de página Legal es 355.6 mm)
            
            $firma_director_X = 220; // Bloque izquierdo de firmas
            $firma_docente_X = 290;  // Bloque derecho de firmas
            
            // Y (altura) se define automáticamente después de la tabla
            $firmas_Y = $this->fpdf->GetY();
            
            // --- Dibujar Firma Director ---
            $this->fpdf->SetFont('Arial', '', 9);
            // Mueve el bloque de texto 20mm hacia abajo para dejar espacio a las imágenes
            $this->fpdf->SetXY($firma_director_X, $firmas_Y + 20); 
            $this->fpdf->Cell(60, 5, $nombre_director, 0, 1, 'C'); // Ancho de 60mm
            $this->fpdf->SetX($firma_director_X);
            $this->fpdf->Cell(60, 5, 'Director', 0, 1, 'C');
            // Imágenes de firma y sello del Director
            if (file_exists($firma_director_path)) {
                $this->fpdf->Image($firma_director_path, $firma_director_X + 10, $firmas_Y, 40, 15);
            }
            if (file_exists($sello_direccion_path)) {
                $this->fpdf->Image($sello_direccion_path, $firma_director_X + 17.5, $firmas_Y - 5, 25, 25);
            }

            // --- Dibujar Firma Docente ---
            $this->fpdf->SetFont('Arial', '', 9);
            $this->fpdf->SetXY($firma_docente_X, $firmas_Y + 20); // Y + 20
            $this->fpdf->Cell(60, 5, $nombre_personal_, 0, 1, 'C'); // Ancho de 60mm
            $this->fpdf->SetX($firma_docente_X);
            $this->fpdf->Cell(60, 5, 'Docente responsable', 0, 1, 'C');
            // Imagen de firma del Docente
            if(!empty($firma_docente)){
                $firma_docente_path_abs = public_path('img/firmas/'.$codigo_institucion_infra.'/'.$firma_docente);
                if (file_exists($firma_docente_path_abs)) {
                    $this->fpdf->Image($firma_docente_path_abs, $firma_docente_X + 17.5, $firmas_Y - 5, 25, 30);
                }
            }

        // --- Fecha y Hora Final ---
            // INDICADOR: Posición Y de la fecha/hora final
            $this->fpdf->SetY($this->fpdf->GetPageHeight() - 15); // 15mm desde el fondo
            $this->fpdf->SetFont('Arial', 'I', 8);
            // INDICADOR: Posición X de la fecha/hora final (Alineado a la derecha)
            // Celda de ancho 0 = ancho completo, 'R' = alinear derecha
            $this->fpdf->Cell(0, 10, mb_convert_encoding('Generado el: ' . date('d/m/Y H:i:s'), 'ISO-8859-1', 'UTF-8'), 0, 0, 'R');

        // Construir el nombre del archivo.
            $nombre_archivo = $nombre_modalidad.' '.$nombre_grado . ' ' . $nombre_seccion . ' ' . $nombre_turno . '.pdf';
        // Salida del pdf.
            $modo = 'I'; // Envia al navegador (I)
            $this->fpdf->Output($nombre_archivo,$modo);
                exit;
    }
}
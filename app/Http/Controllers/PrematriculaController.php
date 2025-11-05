<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;
use GuzzleHttp\Psr7\Header;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class PrematriculaController extends Controller
{
    protected $fpdf;

    public function __construct()
    {
        $this->fpdf = new Fpdf('L','mm','Legal');	// Formato Legal (Paisaje)
    }

    public function index($id) 
    {
        // --- INDICADOR MANUAL: Alto de Fila ---
        // Modifica este valor para cambiar el alto de todas las filas de datos
        $alto_fila_manual = 9.5; // 8mm de alto (puedes cambiarlo a 10, 12, etc.)
        // --- FIN DE INDICADOR MANUAL ---
        
        // Configurar PDF.
            $this->fpdf->SetFont('Arial', 'B', 9);
            $this->fpdf->AddPage(); // Añadimos la página al inicio
            $this->fpdf->SetMargins(5, 5, 5);
            $this->fpdf->SetAutoPageBreak(true,5);
        
        // ... (parseo de $id) ...
            $EstudianteMatricula = explode("-",$id);
            if($EstudianteMatricula[0] == "Tablero"){
                $codigo_gradoseccionturnomodalidad = $EstudianteMatricula[1]; $codigo_modalidad = substr($codigo_gradoseccionturnomodalidad,6,2); $codigo_turno = substr($codigo_gradoseccionturnomodalidad,4,2); $codigo_seccion = substr($codigo_gradoseccionturnomodalidad,2,2); $codigo_grado = substr($codigo_gradoseccionturnomodalidad,0,2);
                $codigo_annlectivo = $EstudianteMatricula[2]; $codigo_personal = $EstudianteMatricula[3]; $codigo_institucion = $EstudianteMatricula[4];
            }else{
                $codigo_gradoseccionturnomodalidad = $EstudianteMatricula[0]; $codigo_modalidad = substr($codigo_gradoseccionturnomodalidad,6,2); $codigo_turno = substr($codigo_gradoseccionturnomodalidad,4,2); $codigo_seccion = substr($codigo_gradoseccionturnomodalidad,2,2); $codigo_grado = substr($codigo_gradoseccionturnomodalidad,0,2);
                $codigo_annlectivo = $EstudianteMatricula[1]; $codigo_institucion = $EstudianteMatricula[2]; $codigo_asignatura = $EstudianteMatricula[3]; $codigo_area_asignatura = $EstudianteMatricula[4]; $codigo_personal = $EstudianteMatricula[5];
            }

        // --- INICIO: NUEVA CONSULTA PARA NOMBRES (TÍTULO) ---
        // Se obtiene el nombre del grado y sección para el título principal
        $infoGrado = DB::table('grado_ano AS gr')
            ->join('seccion AS sec', 'sec.codigo', '=', DB::raw("'$codigo_seccion'"))
            ->select('gr.nombre as nombre_grado', 'sec.nombre as nombre_seccion')
            ->where('gr.codigo', $codigo_grado)
            ->first();

        $nombre_grado_titulo = $infoGrado ? mb_convert_encoding(trim($infoGrado->nombre_grado), "ISO-8859-1", "UTF-8") : '______';
        $nombre_seccion_titulo = $infoGrado ? mb_convert_encoding(trim($infoGrado->nombre_seccion), "ISO-8859-1", "UTF-8") : '______';
        // --- FIN: NUEVA CONSULTA ---

        // --- INICIO: TÍTULO PRINCIPAL ---
            // 1. Define la posición Y inicial para el título
            $current_Y = 10; // 10mm desde arriba
            $this->fpdf->SetXY(10, $current_Y); 
            $this->fpdf->SetFont('Arial', 'B', 12);
            
            // --- TÍTULO MODIFICADO (CORREGIDO PARA USAR VARIABLES) ---
            $titulo_principal = "PREMATRICULA 2026, GRADO: $nombre_grado_titulo SECCIÓN: $nombre_seccion_titulo -> PARA SER MATRICULADOS EN EL AÑO LECTIVO 2026: GRADO:_______ SECCIÓN:_____";
            $this->fpdf->Cell(345.6, 8, mb_convert_encoding($titulo_principal, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
            
            // 3. Actualiza $current_Y
            $current_Y = $this->fpdf->GetY() + 2; 
            
            // Restablece la fuente
            $this->fpdf->SetFont('Arial', 'B', 9); 
        // --- FIN: TÍTULO PRINCIPAL ---
        
        // --- INICIO: LÓGICA DE RESULTADOS (PROMOVIDO/RETENIDO) ---
        // (Este bloque se mantiene para calcular el "RESULTADO FINAL")
            $stats = [ 'M' => ['promovidos' => 0, 'retenidos' => 0], 'F' => ['promovidos' => 0, 'retenidos' => 0], 'Total' => ['promovidos' => 0, 'retenidos' => 0], ];
            $studentsForPromotion = DB::table('alumno_matricula as am')
                ->join('alumno as a', 'a.id_alumno', '=', 'am.codigo_alumno')
                ->join('nota as n', 'n.codigo_matricula', '=', 'am.id_alumno_matricula')
                ->join('asignatura as asig', 'n.codigo_asignatura', '=', 'asig.codigo')
                ->select('am.id_alumno_matricula', 'a.codigo_genero', 'n.recuperacion', 'n.nota_recuperacion_2', 'n.nota_final', 'asig.codigo_area')
                ->where('am.codigo_bach_o_ciclo', $codigo_modalidad)
                ->where('am.codigo_grado', $codigo_grado)
                ->where('am.codigo_seccion', $codigo_seccion)
                ->where('am.codigo_turno', $codigo_turno)
                ->where('am.codigo_ann_lectivo', $codigo_annlectivo)
                ->where('am.retirado', false) // Solo evalúa a los no retirados
                ->get();
            
            $studentsGrades = [];
            foreach ($studentsForPromotion as $grade) {
                $studentsGrades[$grade->id_alumno_matricula]['gender'] = (trim($grade->codigo_genero) == '01') ? 'M' : 'F';
                $studentsGrades[$grade->id_alumno_matricula]['grades'][] = [ 'rec_1' => $grade->recuperacion, 'rec_2' => $grade->nota_recuperacion_2, 'final' => $grade->nota_final, 'area' => $grade->codigo_area ];
            }
            
            $mapa_resultados = []; // <-- MAPA CLAVE
            foreach ($studentsGrades as $matricula_id => $student) { 
                $isRetenido = false; 
                $gender = $student['gender']; 
                
                // ======================================================
                // --- INICIO: LÓGICA DE PROMOCIÓN DIRECTA ---
                // ======================================================
                $esPromocionDirecta = false;
                
                // Condición 1: Modalidad '19' Y Grado '01'
                if ($codigo_modalidad == '19' && $codigo_grado == '01') {
                    $esPromocionDirecta = true;
                }
                
                // Condición 2: Modalidad '20' (cualquier grado)
                if ($codigo_modalidad == '20') {
                    $esPromocionDirecta = true;
                }

                if ($esPromocionDirecta) {
                    // Si es promoción directa, forzamos el resultado y saltamos el cálculo.
                    $stats[$gender]['promovidos']++; 
                    $stats['Total']['promovidos']++; 
                    $mapa_resultados[$matricula_id] = 'Promovido';
                    continue; // Pasa al siguiente estudiante
                }
                // ======================================================
                // --- FIN: LÓGICA DE PROMOCIÓN DIRECTA ---
                // ======================================================


                // --- INICIO: CÁLCULO ESTÁNDAR (SI NO FUE PROMOCIÓN DIRECTA) ---
                if (empty($student['grades'])) { 
                    $isRetenido = true; 
                } else { 
                    foreach ($student['grades'] as $grade) { 
                        // Llama a la función helper externa
                        $result = resultado_final( $codigo_modalidad, $grade['rec_1'], $grade['rec_2'], $grade['final'], $grade['area'] ); 
                        if ($result[0] == 'R') { 
                            $isRetenido = true; 
                            break; 
                        } 
                    } 
                } 
                
                if ($isRetenido) { 
                    $stats[$gender]['retenidos']++; $stats['Total']['retenidos']++; 
                    $mapa_resultados[$matricula_id] = 'Retenido'; // <-- Guarda "Retenido"
                } else { 
                    $stats[$gender]['promovidos']++; $stats['Total']['promovidos']++; 
                    $mapa_resultados[$matricula_id] = 'Promovido'; // <-- Guarda "Promovido"
                }
                // --- FIN: CÁLCULO ESTÁNDAR ---
            }
        // --- FIN: LÓGICA DE RESULTADOS ---

        // ... (Consulta de Encargado de Grado) ...
            $EncargadoGrado = DB::table('encargado_grado as eg')->join('personal as p','p.id_personal','=','eg.codigo_docente')
                ->select('p.id_personal', 'p.firma', DB::raw("TRIM(CONCAT(BTRIM(p.nombres), CAST(' ' AS VARCHAR), BTRIM(p.apellidos))) as full_name"))
                ->where([ ['codigo_bachillerato', '=', $codigo_modalidad], ['codigo_grado', '=', $codigo_grado], ['codigo_ann_lectivo', '=', $codigo_annlectivo], ['codigo_seccion', '=', $codigo_seccion], ['codigo_turno', '=', $codigo_turno], ['encargado', '=', 'true'], ])
                ->orderBy('p.id_personal','asc')->get();
                $nombre_personal_ = ''; $firma_docente = '';
                foreach($EncargadoGrado as $response_eg){ $codigo_personal_ = mb_convert_encoding(trim($response_eg->id_personal),"ISO-8859-1","UTF-8"); $nombre_personal_ = mb_convert_encoding(trim($response_eg->full_name),"ISO-8859-1","UTF-8"); $firma_docente = mb_convert_encoding(trim($response_eg->firma),"ISO-8859-1","UTF-8"); }


        // =================================================================
        // ====== INICIO: DIBUJAR ENCABEZADOS (INSTITUCIÓN Y GRADO) ======
        // =================================================================
        $alto_cell_header = 5; // Alto para las celdas de encabezado

        // --- Consulta de Información de la Institución ---
            $EstudianteInformacionInstitucion = DB::table('informacion_institucion as inf')
                ->leftjoin('personal as p','p.id_personal','=',DB::raw("CAST(inf.nombre_director AS INTEGER)"))
                ->select('inf.codigo_institucion','inf.nombre_institucion',
                        DB::raw("TRIM(CONCAT(BTRIM(p.nombres), CAST(' ' AS VARCHAR), BTRIM(p.apellidos))) as full_name"),
                        )
                ->where('id_institucion', '=', $codigo_institucion)
                ->orderBy('id_institucion','asc')
                ->limit(1)
                ->get();
            $nombre_modalidad_header = ''; $nombre_grado_header = ''; $nombre_seccion_header = ''; $nombre_turno_header = '';

            // --- Dibujar Encabezado Izquierdo (Institución) ---
            foreach($EstudianteInformacionInstitucion as $response_i){  
                $nombre_institucion = mb_convert_encoding(trim($response_i->nombre_institucion),"ISO-8859-1","UTF-8");
                $nombre_director = mb_convert_encoding(trim($response_i->full_name),"ISO-8859-1","UTF-8");
                $codigo_institucion_infra = mb_convert_encoding(trim($response_i->codigo_institucion),"ISO-8859-1","UTF-8");
                
                $ce_X = 10;
                $ce_Y = $current_Y; // Usa la Y global calculada después del título
                $this->fpdf->SetXY($ce_X, $ce_Y); 
                
                $this->fpdf->SetXY($ce_X + 17, $ce_Y); // 10 (margen) + 15 (logo) + 2 (espacio)
                $this->fpdf->Cell(40, $alto_cell_header,"CENTRO ESCOLAR:",1,0,'L');       
                $this->fpdf->Cell(135, $alto_cell_header,$codigo_institucion_infra . " - " .$nombre_institucion,1,1,'L');       
            }

            
        // =================================================================
        // ====== INICIO: BUCLE PRINCIPAL DE ESTUDIANTES ======
        // =================================================================

        // --- Consulta principal de Estudiantes ---
            $EstudianteBoleta = DB::table('alumno_matricula AS am')
                ->join('alumno AS a', 'a.id_alumno', '=', 'am.codigo_alumno')
                ->join('bachillerato_ciclo AS bach', 'bach.codigo','=','am.codigo_bach_o_ciclo')
                ->join('grado_ano AS gr', 'gr.codigo','=','am.codigo_grado')
                ->join('seccion AS sec', 'sec.codigo','=','am.codigo_seccion')
                ->join('turno AS tur', 'tur.codigo','=','am.codigo_turno')
                ->select(
                    'a.codigo_nie', 'a.codigo_genero',
                    'am.id_alumno_matricula', // CLAVE para "RESULTADO"
                    'am.retirado',
                    'am.nuevo_ingreso', // Columna 'nuevo_ingreso'
                    'bach.nombre AS nombre_modalidad', 'gr.nombre as nombre_grado', 'sec.nombre as nombre_seccion','tur.nombre as nombre_turno',
                    DB::raw("TRIM(CONCAT(BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno), CAST(' ' AS VARCHAR), BTRIM(a.nombre_completo))) as full_name"),
                    DB::raw("TRIM(CONCAT(BTRIM(a.nombre_completo), CAST(' ' AS VARCHAR), BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno))) as full_nombres_apellidos")
                )
                ->where([
                    ['am.codigo_bach_o_ciclo', '=', $codigo_modalidad],
                    ['am.codigo_grado', '=', $codigo_grado],
                    ['am.codigo_seccion', '=', $codigo_seccion],
                    ['am.codigo_turno', '=', $codigo_turno],
                    ['am.codigo_ann_lectivo', '=', $codigo_annlectivo],
                ])
                ->orderBy('full_name','asc')
                ->get();

            $fila_numero = 1; $fill = false;
            $table_X_start = 10; // Empezar en el margen izquierdo
            
            $header_dibujado = false; // Flag para dibujar el header de notas solo una vez
            
            // --- INICIO: INICIALIZAR CONTADORES PARA CONSOLIDADO ---
            $total_m = 0;
            $total_f = 0;
            $total_ni_m = 0;
            $total_ni_f = 0;
            // --- FIN: INICIALIZAR CONTADORES ---


            // --- Definición de Anchos de Columna ---
            $ancho_cols = [
                8,   // N.º
                15,  // NIE
                60,  // NOMBRE DEL ESTUDIANTE
                8,   // SEXO
                12,  // RETRADO SÍ/NO (Ret. S/N)
                12,  // NUEVO INGRESO SÍ/NO (N.I. S/N)
                18,  // RESULTADO
                25,  // Nº DUI
                60,  // NOMBRE COMPLETO (Encargado)
                20,  // FECHA DE NACIMIENTO
                40,  // DIRECCION
                20,  // TELEFONO
                20,  // PARENTESCO
                27.6 // FIRMA (Total: 345.6mm)
            ];

            foreach($EstudianteBoleta as $response){
                // ... (variables del estudiante) ...
                    $nombre_estudiante = mb_convert_encoding(trim($response->full_name),"ISO-8859-1","UTF-8");
                    $codigo_nie = mb_convert_encoding(trim($response->codigo_nie),"ISO-8859-1","UTF-8");
                    $genero = (trim($response->codigo_genero) == '01') ? 'M' : 'F';
                    
                    $id_matricula = $response->id_alumno_matricula;
                    $es_retirado = $response->retirado == true;
                    $es_nuevo = $response->nuevo_ingreso == true; // Asume booleano
                    
                    $retirado_SN = $es_retirado ? mb_convert_encoding('SÍ', 'ISO-8859-1', 'UTF-8') : 'NO';
                    $nuevo_SN = $es_nuevo ? mb_convert_encoding('SÍ', 'ISO-8859-1', 'UTF-8') : 'NO'; // Se mantiene para el contador

                    // --- Lógica para obtener el RESULTADO ---
                    $resultado_final_str = '';
                    if ($es_retirado) {
                        $resultado_final_str = 'Retirado';
                    } else if (isset($mapa_resultados[$id_matricula])) {
                        $resultado_final_str = $mapa_resultados[$matricula_id]; // "Promovido" o "Retenido"
                    } else {
                        // Esto cubre a estudiantes sin notas o que no cayeron en la lógica
                        $resultado_final_str = 'Retenido'; 
                    }
                    $resultado_final_str_iso = mb_convert_encoding($resultado_final_str, 'ISO-8859-1', 'UTF-8');

                    // --- INICIO: LÓGICA DE CONTEO PARA CONSOLIDADO ---
                    if ($genero == 'M') {
                        $total_m++;
                        if ($es_nuevo) {
                            $total_ni_m++;
                        }
                    } else {
                        $total_f++;
                        if ($es_nuevo) {
                            $total_ni_f++;
                        }
                    }
                    // --- FIN: LÓGICA DE CONTEO ---


                // --- Dibujar cabecera de la tabla de notas (solo la primera vez) ---
                if(!$header_dibujado){
                    // --- Obtenemos los datos del encabezado (Nivel, Grado) del PRIMER estudiante ---
                        $nombre_modalidad_header = mb_convert_encoding(trim($response->nombre_modalidad),"ISO-8859-1","UTF-8");  
                        $nombre_grado_header = mb_convert_encoding(trim($response->nombre_grado),"ISO-8859-1","UTF-8");  
                        $nombre_seccion_header = mb_convert_encoding(trim($response->nombre_seccion),"ISO-8859-1","UTF-8");  
                        $nombre_turno_header = mb_convert_encoding(trim($response->nombre_turno),"ISO-8859-1","UTF-8");

                    // --- DIBUJAR ENCABEZADO DE GRADO (Nivel, Grado, Encargado) ---
                    $header_info_X = 27;  // Distancia desde la izquierda
                    $this->fpdf->SetXY($header_info_X, $current_Y + 5); // Usa $current_Y + 5 (Tu ajuste)
                    
                    $this->fpdf->SetFont('Arial', 'B', 9);
                    $this->fpdf->Cell(40,$alto_cell_header,mb_convert_encoding("Nivel","ISO-8859-1","UTF-8"),1,0,'L'); 
                    $this->fpdf->Cell(135,$alto_cell_header,$nombre_modalidad_header,1,1,'L'); 
                    $this->fpdf->SetX($header_info_X); // Vuelve al X inicial
                    $this->fpdf->Cell(15,$alto_cell_header,"Grado",1,0,'L'); 
                    $this->fpdf->Cell(70,$alto_cell_header,$nombre_grado_header,1,0,'L'); 
                    $this->fpdf->Cell(15,$alto_cell_header,mb_convert_encoding("Sección","ISO-8859-1","UTF-8"),1,0,'L'); 
                    $this->fpdf->Cell(10,$alto_cell_header,$nombre_seccion_header,1,0,'C'); 
                    $this->fpdf->Cell(20,$alto_cell_header,"Turno",1,0,'L'); 
                    $this->fpdf->Cell(45,$alto_cell_header,$nombre_turno_header,1,1,'C'); 
                    $this->fpdf->SetX($header_info_X); // Vuelve al X inicial
                    $this->fpdf->Cell(55,$alto_cell_header,"Encargado de Grado: ",1,0,'L'); 
                    $this->fpdf->Cell(120,$alto_cell_header,$nombre_personal_,1,1,'L');
                   $this->fpdf->ln(2); // Espacio antes de la tabla
                   
                   
                   // --- DIBUJAR ENCABEZADO DE LA TABLA PRINCIPAL ---
                   $this->fpdf->SetFont('Arial', 'B', '7');
                   $this->fpdf->SetFillColor(230, 230, 230);
                   $this->fpdf->SetX($table_X_start);
                   
                   // Encabezado "DATOS DEL ENCARGADO" (fusionado)
                   $ancho_datos_personales = $ancho_cols[0] + $ancho_cols[1] + $ancho_cols[2] + $ancho_cols[3] + $ancho_cols[4] + $ancho_cols[5] + $ancho_cols[6];
                   $ancho_datos_encargado = array_sum(array_slice($ancho_cols, 7));
                   
                   $this->fpdf->Cell($ancho_datos_personales, $alto_cell_header, '', 1, 0, 'C', true); // Espacio vacío
                   $this->fpdf->Cell($ancho_datos_encargado, $alto_cell_header, 'DATOS DEL ENCARGADO', 1, 1, 'C', true);

                   // Encabezados de columnas individuales
                   $this->fpdf->SetX($table_X_start);
                   $this->fpdf->Cell($ancho_cols[0], $alto_cell_header, mb_convert_encoding('N.º', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
                   $this->fpdf->Cell($ancho_cols[1], $alto_cell_header, 'NIE', 1, 0, 'C', true);
                   $this->fpdf->Cell($ancho_cols[2], $alto_cell_header, 'NOMBRE DEL ESTUDIANTE', 1, 0, 'C', true);
                   $this->fpdf->Cell($ancho_cols[3], $alto_cell_header, 'SEXO', 1, 0, 'C', true);
                   
                   // --- TÍTULOS DE COLUMNA MODIFICADOS ---
                   $this->fpdf->Cell($ancho_cols[4], $alto_cell_header, 'Ret. S/N', 1, 0, 'C', true);
                   $this->fpdf->Cell($ancho_cols[5], $alto_cell_header, 'N.I. S/N', 1, 0, 'C', true);
                   // --- FIN TÍTULOS MODIFICADOS ---

                   $this->fpdf->Cell($ancho_cols[6], $alto_cell_header, 'RESULTADO', 1, 0, 'C', true);
                   $this->fpdf->Cell($ancho_cols[7], $alto_cell_header, mb_convert_encoding('Nº DUI', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
                   $this->fpdf->Cell($ancho_cols[8], $alto_cell_header, 'NOMBRE COMPLETO', 1, 0, 'C', true);
                   $this->fpdf->Cell($ancho_cols[9], $alto_cell_header, 'FECHA N.', 1, 0, 'C', true);
                   $this->fpdf->Cell($ancho_cols[10], $alto_cell_header, 'DIRECCION', 1, 0, 'C', true);
                   $this->fpdf->Cell($ancho_cols[11], $alto_cell_header, 'TELEFONO', 1, 0, 'C', true);
                   $this->fpdf->Cell($ancho_cols[12], $alto_cell_header, 'PARENTESCO', 1, 0, 'C', true);
                   $this->fpdf->Cell($ancho_cols[13], $alto_cell_header, 'FIRMA', 1, 1, 'C', true); // Salto de línea
                   
                   $header_dibujado = true; // Marcar como dibujado
              }
                
                // --- Dibujar fila de datos del estudiante ---
                $this->fpdf->SetFillColor(255, 255, 255); $this->fpdf->SetTextColor(0,0,0); $this->fpdf->SetFont('Arial', '', 7);
                
                $this->fpdf->SetX($table_X_start); // Asegura que empiece en el X de la tabla
                // Usamos $alto_fila_manual
                $this->fpdf->Cell($ancho_cols[0], $alto_fila_manual, $fila_numero, 1, 0, 'C', $fill);
                $this->fpdf->Cell($ancho_cols[1], $alto_fila_manual, $codigo_nie, 1, 0, 'L', $fill);
                $this->fpdf->Cell($ancho_cols[2], $alto_fila_manual, $nombre_estudiante, 1, 0, 'L', $fill);
                $this->fpdf->Cell($ancho_cols[3], $alto_fila_manual, $genero, 1, 0, 'C', $fill);
                
                // --- INICIO: LÓGICA PARA "Ret. S/N" (Negrita si es SÍ) ---
                if ($es_retirado) {
                    $this->fpdf->SetFont('Arial', 'B', 7); // BOLD
                }
                $this->fpdf->Cell($ancho_cols[4], $alto_fila_manual, $retirado_SN, 1, 0, 'C', $fill);
                $this->fpdf->SetFont('Arial', '', 7); // Reset to REGULAR
                // --- FIN: LÓGICA "Ret. S/N" ---

                // --- MODIFICADO: Columna "N.I. S/N" vacía ---
                $this->fpdf->Cell($ancho_cols[5], $alto_fila_manual, '', 1, 0, 'C', $fill);
                
                // --- INICIO: LÓGICA DE COLOR/NEGRILLA PARA "RESULTADO" ---
                $this->fpdf->SetFont('Arial', 'B', 7); // Todos son BOLD
                
                switch ($resultado_final_str) {
                    case 'Retenido':
                        $this->fpdf->SetTextColor(255, 0, 0); // Rojo
                        break;
                    case 'Promovido':
                        $this->fpdf->SetTextColor(0, 0, 255); // Azul
                        break;
                    case 'Retirado':
                    default:
                        $this->fpdf->SetTextColor(0, 0, 0); // Negro
                        break;
                }
                
                $this->fpdf->Cell($ancho_cols[6], $alto_fila_manual, $resultado_final_str_iso, 1, 0, 'L', $fill);
                
                // Resetear todo a la normalidad
                $this->fpdf->SetFont('Arial', '', 7);
                $this->fpdf->SetTextColor(0, 0, 0);
                // --- FIN: LÓGICA "RESULTADO" ---


                // Celdas vacías para "DATOS DEL ENCARGADO"
                $this->fpdf->Cell($ancho_cols[7], $alto_fila_manual, '', 1, 0, 'L', $fill);
                $this->fpdf->Cell($ancho_cols[8], $alto_fila_manual, '', 1, 0, 'L', $fill);
                $this->fpdf->Cell($ancho_cols[9], $alto_fila_manual, '', 1, 0, 'L', $fill);
                $this->fpdf->Cell($ancho_cols[10], $alto_fila_manual, '', 1, 0, 'L', $fill);
                $this->fpdf->Cell($ancho_cols[11], $alto_fila_manual, '', 1, 0, 'L', $fill);
                $this->fpdf->Cell($ancho_cols[12], $alto_fila_manual, '', 1, 0, 'L', $fill);
                $this->fpdf->Cell($ancho_cols[13], $alto_fila_manual, '', 1, 1, 'L', $fill); // Salto de línea
                
                $fila_numero++; 
                $fill=!$fill;
                $this->fpdf->SetTextColor(0,0,0); $this->fpdf->SetFillColor(212,230,252);

            } // FIN DEL FOREACH

        // =================================================================
        // ====== INICIO: DIBUJAR FILAS FALTANTES HASTA 50 ======
        // =================================================================
        
            $numero_maximo_filas = 50; 
            if($fila_numero > 50) {
                 $numero_maximo_filas = ceil($fila_numero / 50) * 50;
            }

            for ($i = $fila_numero; $i <= $numero_maximo_filas; $i++) {
                $this->fpdf->SetX($table_X_start);
                $this->fpdf->Cell($ancho_cols[0], $alto_fila_manual, $i, 1, 0, 'C', $fill); // Dibuja $i
                
                for ($j = 1; $j < count($ancho_cols); $j++) {
                    $this->fpdf->Cell($ancho_cols[$j], $alto_fila_manual, '', 1, 0, 'L', $fill);
                }
                
                $this->fpdf->ln();
                $fill=!$fill;
            }

        // =================================================================
        // ====== INICIO: CONSOLIDADO Y FIRMA (NUEVO BLOQUE) ======
        // =================================================================
        
            // Dejar un espacio
            if ($this->fpdf->GetY() > 180) { // Si está muy abajo, añade página
                 $this->fpdf->AddPage();
                 $y_consolidado = 20;
            } else {
                 $y_consolidado = $this->fpdf->GetY() + 10; // 10mm de espacio
            }
           
            $this->fpdf->SetY($y_consolidado);
            $x_consolidado = 10; // X inicial
            $h_consolidado = 6; // Alto de fila
            $w_col1 = 40; // Ancho Etiqueta
            $w_col2 = 25; // Ancho Masculino
            $w_col3 = 25; // Ancho Femenino
            $w_col4 = 30; // Ancho Total
            $w_total_tabla = $w_col1 + $w_col2 + $w_col3 + $w_col4;

            $this->fpdf->SetFont('Arial', 'B', 9);
            $this->fpdf->SetFillColor(230, 230, 230);
            
            // Título
            $this->fpdf->SetX($x_consolidado);
            $this->fpdf->Cell($w_total_tabla, $h_consolidado, 'CONSOLIDADO DE PREMATRICULA', 1, 1, 'C', true);

            // Encabezados
            $this->fpdf->SetX($x_consolidado);
            $this->fpdf->Cell($w_col1, $h_consolidado, '', 1, 0, 'C', true); // Vacío
            $this->fpdf->Cell($w_col2, $h_consolidado, 'MASCULINO', 1, 0, 'C', true);
            $this->fpdf->Cell($w_col3, $h_consolidado, 'FEMENINO', 1, 0, 'C', true);
            $this->fpdf->Cell($w_col4, $h_consolidado, 'TOTAL', 1, 1, 'C', true);

            // Fila: Total Estudiantes (CORREGIDO PARA MOSTRAR TOTALES)
            $this->fpdf->SetFont('Arial', '', 9);
            $this->fpdf->SetX($x_consolidado);
            $this->fpdf->Cell($w_col1, $h_consolidado, 'Total Estudiantes', 1, 0, 'L');
            $this->fpdf->Cell($w_col2, $h_consolidado, $total_m, 1, 0, 'C');
            $this->fpdf->Cell($w_col3, $h_consolidado, $total_f, 1, 0, 'C');
            $this->fpdf->Cell($w_col4, $h_consolidado, ($total_m + $total_f), 1, 1, 'C');
            
            // Fila: Nuevo Ingreso (CORREGIDO PARA MOSTRAR TOTALES)
            $this->fpdf->SetX($x_consolidado);
            $this->fpdf->Cell($w_col1, $h_consolidado, 'Nuevo Ingreso', 1, 0, 'L');
            $this->fpdf->Cell($w_col2, $h_consolidado, $total_ni_m, 1, 0, 'C');
            $this->fpdf->Cell($w_col3, $h_consolidado, $total_ni_f, 1, 0, 'C');
            $this->fpdf->Cell($w_col4, $h_consolidado, ($total_ni_m + $total_ni_f), 1, 1, 'C');

            // --- Firma ---
            $y_firma = $this->fpdf->GetY() + 20; // 20mm debajo de la tabla
            $x_firma_start = $x_consolidado + 10;
            $x_firma_end = $x_firma_start + 100; // 100mm de línea
            
            $this->fpdf->Line($x_firma_start, $y_firma, $x_firma_end, $y_firma);
            $this->fpdf->SetXY($x_firma_start, $y_firma + 1); // 1mm debajo de la línea
            $this->fpdf->SetFont('Arial', '', 9);
            // (Se mantiene tu función 'convertirTexto' por si la tienes en un helper)
            $this->fpdf->Cell(100, 5, convertirTexto('Firma de quien realizó la matrícula'), 0, 1, 'C');

        // =================================================================
        // ====== FIN: CONSOLIDADO Y FIRMA ======
        // =================================================================


        // --- Fecha y Hora Final ---
            $this->fpdf->SetY($this->fpdf->GetPageHeight() - 15);
            $this->fpdf->SetFont('Arial', 'I', 8);
            $this->fpdf->Cell(0, 10, mb_convert_encoding('Generado el: ' . date('d/m/Y H:i:s'), 'ISO-8859-1', 'UTF-8'), 0, 0, 'R');

        // Construir el nombre del archivo.
            $nombre_archivo = 'PREMATRICULA ' . $nombre_grado_header . ' ' . $nombre_seccion_header . ' ' . $nombre_turno_header . '.pdf';
        // Salida del pdf.
            $modo = 'I'; // Envia al navegador (I)
            $this->fpdf->Output($nombre_archivo,$modo);
                exit;
    }
}
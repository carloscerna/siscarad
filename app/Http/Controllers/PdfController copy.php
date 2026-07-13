<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;
use GuzzleHttp\Psr7\Header;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Mail\BoletaEstudiantes;
use Illuminate\Support\Facades\Auth;

$nombre_personal = "";

/**
 * Clase extendida para manejar el Pie de Página personalizado
 */
class PDF_ConFooter extends Fpdf {
    // Agregamos p5 por soporte técnico para las modalidades de 5 periodos
    public $promedios = ['p1' => '0.0', 'p2' => '0.0', 'p3' => '0.0', 'p4' => '0.0', 'p5' => '0.0', 'final' => '0.0'];
    public $num_periodos = 3; // Este valor cambiará dinámicamente

    function Footer() {
        // Posición un poco más arriba para que luzca el diseño
        $this->SetY(-15);
        
        // --- COLORES DE DISEÑO ---
        $colorFondoGris = [248, 249, 250];
        $colorFondoAzul = [0, 51, 102]; // Azul institucional
        $colorTextoAzul = [0, 51, 102];
        $colorBorde = [180, 180, 180];

        // Calcular anchos basándose dinámicamente en el número de periodos + 1 (la celda Final)
        $columnas = $this->num_periodos + 1;
        $ancho_pagina = 250; // Aproximado para Letter Paisaje
        $ancho_celda = $ancho_pagina / $columnas;

        $this->SetX(15);
        $this->SetLineWidth(0.3); // Borde un poco más firme

        // --- DIBUJAR PROMEDIOS PARCIALES ---
        $this->SetFillColor($colorFondoGris[0], $colorFondoGris[1], $colorFondoGris[2]);
        $this->SetTextColor($colorTextoAzul[0], $colorTextoAzul[1], $colorTextoAzul[2]);
        $this->SetDrawColor($colorBorde[0], $colorBorde[1], $colorBorde[2]);
        $this->SetFont('Arial', 'B', 9);

        // Este bucle ya hace todo el trabajo según el número que le asignemos
        for ($i = 1; $i <= $this->num_periodos; $i++) {
            $key = "p$i";
            $valor = isset($this->promedios[$key]) ? $this->promedios[$key] : '0.0';
            
            // Título pequeño arriba del valor
            $xActual = $this->GetX();
            $yActual = $this->GetY();
            
            $this->Cell($ancho_celda, 10, "", 1, 0, 'C', true); // Celda de fondo
            $this->SetXY($xActual, $yActual + 1);
            $this->SetFont('Arial', '', 7);
            $this->Cell($ancho_celda, 3, "PROMEDIO P$i", 0, 0, 'C');
            $this->SetXY($xActual, $yActual + 4);
            $this->SetFont('Arial', 'B', 11); // Valor más grande
            $this->Cell($ancho_celda, 5, $valor, 0, 0, 'C');
            
            $this->SetXY($xActual + $ancho_celda, $yActual); // Mover a la siguiente posición
        }

        // --- DIBUJAR PROMEDIO FINAL (RESALTADO) ---
        $valor_final = isset($this->promedios['final']) ? $this->promedios['final'] : '0.0';
        
        $this->SetFillColor($colorFondoAzul[0], $colorFondoAzul[1], $colorFondoAzul[2]);
        $this->SetTextColor(255, 255, 255); // Texto blanco
        $this->SetDrawColor(0, 30, 60);
        
        $xActual = $this->GetX();
        $yActual = $this->GetY();
        
        $this->Cell($ancho_celda, 10, "", 1, 1, 'C', true); // Celda fondo azul
        
        $this->SetXY($xActual, $yActual + 1);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell($ancho_celda, 3, "PROMEDIO FINAL GLOBAL", 0, 0, 'C');
        $this->SetXY($xActual, $yActual + 4);
        $this->SetFont('Arial', 'B', 12); // El más grande de todos
        $this->Cell($ancho_celda, 5, $valor_final, 0, 0, 'C');

        // --- PIE DE PÁGINA (DATOS TÉCNICOS) ---
        $this->SetY(-5);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(100, 100, 100);
        date_default_timezone_set('America/El_Salvador');
        $fecha = date('d/m/Y h:i:s A');
        $this->Cell(0, 5, mb_convert_encoding("Generado el: $fecha | Sistema Académico | Página " . $this->PageNo() . "/{nb}", 'ISO-8859-1', 'UTF-8'), 0, 0, 'R');
    }
}

class PdfController extends Controller
{
    protected $fpdf;

// PHP 8.x permite tipar las propiedades. 
// Esto evita el error de "variable no definida" en el bloque de firmas.
//  public string $nombre_personal = "";
    public function __construct()
    {
        $this->fpdf = new Fpdf('L','mm','Letter');	// Formato Letter;
            // Cambiar la instancia a la nueva clase con Footer
        // Se utiliza la nueva clase que contiene el pie de página
        $this->fpdf = new PDF_ConFooter('L','mm','Letter');
        // Alias para el total de páginas ({nb})
        $this->fpdf->AliasNbPages();
    }

public function index($id, $accion = "ver", $codigo_matricula = null) 
{
    // 1. Creamos la instancia limpia de Request de Laravel
    $request = new \Illuminate\Http\Request();

    // 2. Desglosamos el ID recibido
    $EstudianteMatricula = explode("-", $id);
    
    if ($EstudianteMatricula[0] == "Tablero") {
        // Origen: Impresión Masiva desde el Tablero
        // Extraemos los componentes posicionales del string del Tablero
        $codigo_gradoseccionturnomodalidad = $EstudianteMatricula[1] ?? '';
        
        // Desglose de subcadenas según la estructura fija de tus códigos
        $codigo_grado       = substr($codigo_gradoseccionturnomodalidad, 0, 2);
        $codigo_seccion     = substr($codigo_gradoseccionturnomodalidad, 2, 2);
        $codigo_turno       = substr($codigo_gradoseccionturnomodalidad, 4, 2);
        $codigo_modalidad   = substr($codigo_gradoseccionturnomodalidad, 6, 2);
        
        $codigo_annlectivo  = $EstudianteMatricula[2] ?? '';

        // Consultamos la base de datos para capturar las matrículas de este grupo específico
        $matriculasNomina = DB::table('alumno as a')
            ->join('alumno_matricula AS am', 'a.id_alumno', '=', 'am.codigo_alumno')
            ->where([
                ['am.codigo_bach_o_ciclo', '=', $codigo_modalidad],
                ['am.codigo_grado', '=', $codigo_grado],
                ['am.codigo_seccion', '=', $codigo_seccion],
                ['am.codigo_ann_lectivo', '=', $codigo_annlectivo],
                ['am.retirado', '=', 'f'], // Excluir alumnos retirados
            ])
            ->orderBy(DB::raw("TRIM(CONCAT(BTRIM(a.apellido_paterno), ' ', BTRIM(a.apellido_materno), ' ', BTRIM(a.nombre_completo)))"), 'asc')
            ->pluck('am.id_alumno_matricula') // Extrae solo los IDs de matrícula
            ->toArray();

        if (empty($matriculasNomina)) {
            // Si la sección no tiene alumnos matriculados, evitamos que falle retornando un mensaje descritivo
            return response("No se encontraron estudiantes matriculados activos para esta sección.", 404);
        }

        // Convertimos el array de IDs [28304, 28305, 28306] en una cadena "28304,28305,28306"
        $matriculasString = implode(',', $matriculasNomina);

        // Empaquetamos todo en el Request simulando una petición AJAX masiva nativa
        $request->merge([
            'matriculas' => $matriculasString, 
            'accion' => $accion
        ]);

    } else {
        // Origen: Impresión Individual desde el Expediente del Alumno
        $matricula_individual = $EstudianteMatricula[2] ?? $codigo_matricula;

        $request->merge([
            'matriculas' => $matricula_individual, 
            'accion' => $accion
        ]);
    }

    // 3. Enviamos el request construido con éxito a gobernar en BoletaMasiva
    return $this->boletaMasiva($request);
}

    ////////// BBOLETA MASIVA QUE ES E EXTIENDE DESDE EL INGRESO DE LAS CALIFICACIONES ////
    public function boletaMasiva(Request $request)
    {
        // Elevar temporalmente los límites para procesamientos masivos pesados
        ini_set('max_execution_time', '300'); // 5 minutos de tiempo límite
        ini_set('memory_limit', '512M');     // Ampliar la memoria disponible
        // 1. Validar que el usuario esté autenticado (opcional pero recomendado)
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Obtener datos del docente logueado
        $codigo_institucion = Auth::user()->codigo_institucion;
        
        // 3. Obtener matrículas enviadas por el parámetro del JS
        $matriculasStr = $request->input('matriculas');
        if (!$matriculasStr) {
            return "No se han seleccionado estudiantes.";
        }
        $arrayMatriculas = explode(',', $matriculasStr);

    // Obtener el nombre para ponerlo en la firma
        //$nombre_personal = Auth::user()->name;

    // 1. Recolectar parámetros de la URL
        $matriculasStr = $request->input('matriculas'); // Viene como "28304,28305"
        $periodoActivo = $request->input('periodo');    // Viene como "1", "2", etc.
        $accion = $request->input('accion', 'ver');     // 'ver' o 'descargar'

        
        // 2. Configurar el PDF (Letter Horizontal) tal como lo tienes
        $this->fpdf->SetFont('Arial', 'B', 9);
        $this->fpdf->SetMargins(15, 5, 5);
        $this->fpdf->SetAutoPageBreak(false, 5);

        // --------------------------------------------------------
        // 3. OBTENER INFORMACIÓN INSTITUCIONAL (SÓLO UNA VEZ)
        // --------------------------------------------------------
        // Extraemos la institución basándonos en la primera matrícula de la lista
        $primeraMatricula = DB::table('alumno_matricula')->where('id_alumno_matricula', $arrayMatriculas[0])->first();
        //$codigo_institucion = $primeraMatricula->codigo_institucion ?? '00000'; // Default por seguridad
        $codigo_annlectivo = $primeraMatricula->codigo_ann_lectivo;
        $codigo_modalidad = $primeraMatricula->codigo_bach_o_ciclo;
        $codigo_grado = $primeraMatricula->codigo_grado;

    // =========================================================================
    // NUEVO: CONSULTAR EL CATÁLOGO DE PERIODOS PARA MODIFICAR EL FOOTER
    // =========================================================================
    $cantidad_periodos = 4; // Valor por defecto en caso de fallo
    if (!empty($codigo_modalidad)) {
        $catalogoPeriodo = DB::table('catalogo_periodos')
            ->where('codigo_modalidad', $codigo_modalidad)
            ->first();
            
        if ($catalogoPeriodo) {
            $cantidad_periodos = $catalogoPeriodo->cantidad_periodos;
        }
    }
    // Asignamos la cantidad de periodos dinámicos directamente a la instancia FPDF
    $this->fpdf->num_periodos = $cantidad_periodos;
    // =========================================================================

        $EstudianteInformacionInstitucion = DB::table('informacion_institucion as inf')
                ->leftjoin('personal as p','p.id_personal','=',DB::raw("CAST(inf.nombre_director AS INTEGER)")) 
                ->select('inf.id_institucion','inf.codigo_institucion','inf.nombre_institucion','inf.telefono_uno','inf.logo_uno','inf.direccion_institucion','inf.nombre_director',
                            'inf.logo_dos','inf.logo_tres',
                        DB::raw("TRIM(CONCAT(BTRIM(p.nombres), CAST(' ' AS VARCHAR), BTRIM(p.apellidos))) as full_name"),
                        )
                ->where([
                    ['id_institucion', '=', $codigo_institucion],
                    ])
                ->orderBy('id_institucion','asc')
                ->first();

//    dd($EstudianteInformacionInstitucion);

        if($EstudianteInformacionInstitucion){
            $nombre_institucion = mb_convert_encoding(trim($EstudianteInformacionInstitucion->nombre_institucion),'ISO-8859-1','UTF-8');
            $logo_uno = "/img/".mb_convert_encoding(trim($EstudianteInformacionInstitucion->logo_uno),'ISO-8859-1','UTF-8');
            $codigo_infraestructura = mb_convert_encoding(trim($EstudianteInformacionInstitucion->codigo_institucion),'ISO-8859-1','UTF-8');
            $nombre_director = mb_convert_encoding(trim($EstudianteInformacionInstitucion->full_name),'ISO-8859-1','UTF-8');
            $firma_director = "/img/".mb_convert_encoding(trim($EstudianteInformacionInstitucion->logo_dos),'ISO-8859-1','UTF-8');
            $sello_direccion = "/img/".mb_convert_encoding(trim($EstudianteInformacionInstitucion->logo_tres),'ISO-8859-1','UTF-8');
        }

        // CATALOGO ASIGNATURA (SÓLO UNA VEZ)
        $catalogo_area_asignatura_codigo = array();
        $catalogo_area_asignatura_area = array();
        $CatalogoAreaAsignatura = DB::table('catalogo_area_asignatura')->select('codigo','descripcion')->get();
        foreach($CatalogoAreaAsignatura as $response_area){
            $catalogo_area_asignatura_codigo[] = (trim($response_area->codigo));
            $catalogo_area_asignatura_area[] = (trim($response_area->descripcion));
        }

        // ASIGNACIÓN DE ASIGNATURAS SEGÚN EL GRADO (SÓLO UNA VEZ)
        $AsignacionAsignatura = DB::table('a_a_a_bach_o_ciclo as aaa')
            ->join('asignatura as a','a.codigo','=','aaa.codigo_asignatura')
            ->select('aaa.orden','a.nombre as nombre_asignatura','a.codigo as codigo_asignatura','a.codigo_cc as concepto_calificacion','a.codigo_area')
            ->where([
                ['codigo_bach_o_ciclo', '=', $codigo_modalidad],
                ['codigo_grado', '=', $codigo_grado],
                ['codigo_ann_lectivo', '=', $codigo_annlectivo],
            ])
            ->orderBy('aaa.orden','asc')
            ->get();

        $datos_asignatura = ["codigo" => [], "nombre" => [], "concepto" => [], "codigo_area" => []];                   
        foreach($AsignacionAsignatura as $response_i){
            $datos_asignatura["codigo"][] = mb_convert_encoding(trim($response_i->codigo_asignatura),"ISO-8859-1","UTF-8");
            $datos_asignatura["nombre"][] = mb_convert_encoding(trim($response_i->nombre_asignatura),"ISO-8859-1","UTF-8");
            $datos_asignatura["concepto"][] = mb_convert_encoding(trim($response_i->concepto_calificacion),"ISO-8859-1","UTF-8");
            $datos_asignatura["codigo_area"][] = mb_convert_encoding(trim($response_i->codigo_area),"ISO-8859-1","UTF-8");
        }

    $periodos_a = ['PERIODO 1', 'PERIODO 2', 'PERIODO 3', 'PERIODO 4', 'PERIODO 5', 'PROMEDIO FINAL', 'R'];
    $actividad_periodo = ['A1','A2','PO','R','PP','PF'];
    $alto_cell = [5]; // Altura de celda estándar
    $ancho_cell = [60, 10, 50]; // [Nombre Asig, Notas, Periodos]


        // --------------------------------------------------------
        // 4. BUCLE PRINCIPAL: RECORRER MATRÍCULA POR MATRÍCULA
        // --------------------------------------------------------
        foreach ($arrayMatriculas as $id_matricula) {                   
            // Variables lógicas que se deben resetear en cada hoja
            $catalogo_area_basica = true;
            $catalogo_area_formativa = true;
            $catalogo_area_tecnica = true;
            $catalogo_area_cc = true;
            $catalogo_area_complementaria = true;

            // Consulta de las notas del alumno específico
            $EstudianteBoleta = DB::table('alumno as a')
                ->join('alumno_matricula AS am','a.id_alumno','=','am.codigo_alumno')
                ->join('nota AS n','am.id_alumno_matricula','=','n.codigo_matricula')
                ->join('bachillerato_ciclo AS bach', 'bach.codigo','=','am.codigo_bach_o_ciclo')
                ->join('grado_ano AS gr', 'gr.codigo','=','am.codigo_grado')
                ->join('seccion AS sec', 'sec.codigo','=','am.codigo_seccion')
                ->join('turno AS tur', 'tur.codigo','=','am.codigo_turno')
                ->join('asignatura AS asig','asig.codigo','=','n.codigo_asignatura')
                ->join('ann_lectivo AS ann','ann.codigo','=','am.codigo_ann_lectivo')
                ->select('a.id_alumno as codigo_alumno','a.codigo_nie','a.nombre_completo',"a.apellido_paterno",'a.apellido_materno', 'a.foto', 'a.codigo_genero', 'a.direccion_email as correo_estudiante',
                        'am.id_alumno_matricula as codigo_matricula','n.id_notas','n.codigo_asignatura',
                        'bach.nombre AS nombre_modalidad', 'gr.nombre as nombre_grado', 'sec.nombre as nombre_seccion','tur.nombre as nombre_turno',
                        'bach.codigo as codigo_modalidad', 'gr.codigo as codigo_grado', 'sec.codigo as codigo_seccion','tur.codigo as codigo_turno',
                        'asig.codigo_area',
                        'n.nota_a1_1', 'n.nota_a2_1', 'n.nota_a3_1', 'nota_r_1', 'n.nota_p_p_1', 
                        'n.nota_a1_2', 'n.nota_a2_2', 'n.nota_a3_2', 'nota_r_2', 'n.nota_p_p_2',
                        'n.nota_a1_3', 'n.nota_a2_3', 'n.nota_a3_3', 'nota_r_3', 'n.nota_p_p_3', 
                        'n.nota_a1_4', 'n.nota_a2_4', 'n.nota_a3_4', 'nota_r_4', 'n.nota_p_p_4',
                        'n.nota_a1_5', 'n.nota_a2_5', 'n.nota_a3_5', 'nota_r_5', 'n.nota_p_p_5', 
                        'n.nota_final', 'n.recuperacion', 'n.nota_recuperacion_2',
                        'asig.codigo_area', 'ann.nombre as nombre_annlectivo',
                        DB::raw("TRIM(CONCAT(BTRIM(a.nombre_completo), CAST(' ' AS VARCHAR), BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno))) as full_nombres_apellidos"))
                        ->where([
                                ['am.id_alumno_matricula', '=', $id_matricula],
                                ['n.orden', '<>', 0], // Esta es la línea que filtra los que no son cero
                                ])
                ->orderBy('n.orden','asc')
                ->get();

            if ($EstudianteBoleta->isEmpty()) {
                continue; // Si no hay notas para esta matrícula, saltar al siguiente
            }
            
        // =========================================================================
        // OPTIMIZADO: CALCULAR PROMEDIOS GENERALES SÓLO CON ÁREAS BÁSICAS ("01") Y TÉCNICAS ("03")
        // =========================================================================
        // Realizamos un join con asignatura para conocer el código de área antes de promediar
        $CalcularPromediosBoleta = DB::table('nota as n')
            ->join('asignatura as a', 'a.codigo', '=', 'n.codigo_asignatura')
            ->select('n.nota_p_p_1', 'n.nota_p_p_2', 'n.nota_p_p_3', 'n.nota_p_p_4', 'n.nota_p_p_5', 'n.nota_final')
            ->where('n.codigo_matricula', $id_matricula)
            ->where('n.orden', '<>', 0)
            ->whereIn('a.codigo_area', ['01', '03']) // <--- ¡AQUÍ ESTÁ EL FILTRO EXCLUSIVO!
            ->get();

        // Inicializamos el contenedor de promedios en limpio para este estudiante
        $this->fpdf->promedios = ['p1'=>'0.0', 'p2'=>'0.0', 'p3'=>'0.0', 'p4'=>'0.0', 'p5'=>'0.0', 'final'=>'0.0'];

        if ($CalcularPromediosBoleta->count() > 0) {
            // Promediamos los valores mayores a cero de las columnas correspondientes
            if ($cantidad_periodos >= 1) {
                $this->fpdf->promedios['p1'] = number_format($CalcularPromediosBoleta->where('nota_p_p_1', '>', 0)->avg('nota_p_p_1') ?? 0, 1);
            }
            if ($cantidad_periodos >= 2) {
                $this->fpdf->promedios['p2'] = number_format($CalcularPromediosBoleta->where('nota_p_p_2', '>', 0)->avg('nota_p_p_2') ?? 0, 1);
            }
            if ($cantidad_periodos >= 3) {
                $this->fpdf->promedios['p3'] = number_format($CalcularPromediosBoleta->where('nota_p_p_3', '>', 0)->avg('nota_p_p_3') ?? 0, 1);
            }
            if ($cantidad_periodos >= 4) {
                $this->fpdf->promedios['p4'] = number_format($CalcularPromediosBoleta->where('nota_p_p_4', '>', 0)->avg('nota_p_p_4') ?? 0, 1);
            }
            if ($cantidad_periodos >= 5) {
                $this->fpdf->promedios['p5'] = number_format($CalcularPromediosBoleta->where('nota_p_p_5', '>', 0)->avg('nota_p_p_5') ?? 0, 1);
            }
            $this->fpdf->promedios['final'] = number_format($CalcularPromediosBoleta->where('nota_final', '>', 0)->avg('nota_final') ?? 0, 1);
        }
        // =========================================================================

        // Agregar una hoja nueva para cada alumno (Ahora el Footer() se lanzará con $num_periodos y promedios reales)
        $this->fpdf->AddPage();
        $this->fpdf->SetX(30);

            // Extraer datos fijos de la cabecera del alumno
            $primerRegistro = $EstudianteBoleta->first();
            $nombre_completo = mb_convert_encoding(trim($primerRegistro->full_nombres_apellidos),'ISO-8859-1','UTF-8');
            $codigo_nie = trim($primerRegistro->codigo_nie);
            $correo_estudiante = trim($primerRegistro->correo_estudiante);
            $nombre_modalidad = mb_convert_encoding(trim($primerRegistro->nombre_modalidad),'ISO-8859-1','UTF-8');
            $codigo_modalidad = mb_convert_encoding(trim($primerRegistro->codigo_modalidad),'ISO-8859-1','UTF-8');
            $codigo_area = mb_convert_encoding(trim($primerRegistro->codigo_area),'ISO-8859-1','UTF-8');
            $nombre_grado = mb_convert_encoding(trim($primerRegistro->nombre_grado),'ISO-8859-1','UTF-8');
            $nombre_seccion = mb_convert_encoding(trim($primerRegistro->nombre_seccion),'ISO-8859-1','UTF-8');
            $nombre_turno = mb_convert_encoding(trim($primerRegistro->nombre_turno),'ISO-8859-1','UTF-8');
            $nombre_annlectivo = mb_convert_encoding(trim($primerRegistro->nombre_annlectivo),'ISO-8859-1','UTF-8');
            $nombre_foto = trim($primerRegistro->foto);
            $codigo_genero = trim($primerRegistro->codigo_genero);
            $codigo_seccion = trim($primerRegistro->codigo_seccion);
            $codigo_turno = trim($primerRegistro->codigo_turno);
            $codigo_alumno_seguro = trim($primerRegistro->codigo_alumno);


    // 1. Consultamos el encargado de la sección
        $registroEncargado = DB::table('encargado_grado as eg')
            ->join('personal as p', 'p.id_personal', '=', 'eg.codigo_docente')
            ->select(
                'p.id_personal',
                'p.firma',
                DB::raw("TRIM(CONCAT(BTRIM(p.nombres), ' ', BTRIM(p.apellidos))) as full_name")
            )
            ->where([
                ['codigo_bachillerato', '=', $codigo_modalidad],
                ['codigo_grado', '=', $codigo_grado],
                ['codigo_ann_lectivo', '=', $codigo_annlectivo],
                ['codigo_seccion', '=', $codigo_seccion],
                ['codigo_turno', '=', $codigo_turno],
                ['encargado', '=', 'true'],
            ])
            ->first(); // Obtenemos solo el primer resultado

        // 2. Validamos si existe y preparamos el nombre para FPDF
        if ($registroEncargado) {
            $nombre_personal = mb_convert_encoding(trim($registroEncargado->full_name), "ISO-8859-1", "UTF-8");
        } else {
            $nombre_personal = "NOMBRE DEL DOCENTE NO ENCONTRADO";
        }


            $alto_cell = array('5'); 
            $ancho_cell = array('60','6','30','30','180');

            // AQUÍ DIBUJAMOS LA CABECERA DEL ALUMNO
            $this->fpdf->image(URL::to($logo_uno),10,10,20,25);
            $this->fpdf->Cell(40, $alto_cell[0],"CENTRO ESCOLAR:",1,0,'L');       
            $this->fpdf->Cell(135, $alto_cell[0],$codigo_infraestructura . " - " .$nombre_institucion,1,1,'L');       

            $this->fpdf->SetX(30); 
            $this->fpdf->Cell(40,$alto_cell[0],"Estudiante",1,0,'L');       
            $this->fpdf->Cell(135,$alto_cell[0],$codigo_nie . " - " . $nombre_completo,1,1,'L');       
            $this->fpdf->SetX(30); 
                                $this->fpdf->Cell(40,$alto_cell[0],mb_convert_encoding("Correo Electrónico","ISO-8859-1","UTF-8"),1,0,'L');       
                                $this->fpdf->Cell(135,$alto_cell[0],$correo_estudiante,1,1,'L');       
                                //Nivel
                                $this->fpdf->SetX(30); 
                                $this->fpdf->Cell(40,$alto_cell[0],mb_convert_encoding("Nivel","ISO-8859-1","UTF-8"),1,0,'L');       
                                $this->fpdf->Cell(115,$alto_cell[0],$nombre_modalidad,1,1,'L');       
                                // Grado
                                $this->fpdf->SetX(30); 
                                $this->fpdf->Cell(15,$alto_cell[0],"Grado",1,0,'L');       
                                $this->fpdf->Cell(70,$alto_cell[0],$nombre_grado,1,0,'L');       
                                // Sección.
                                $this->fpdf->Cell(15,$alto_cell[0],mb_convert_encoding("Sección","ISO-8859-1","UTF-8"),1,0,'L');       
                                $this->fpdf->Cell(10,$alto_cell[0],$nombre_seccion,1,0,'C');       
                                // Turno
                                $this->fpdf->Cell(20,$alto_cell[0],"Turno",1,0,'L');       
                                $this->fpdf->Cell(30,$alto_cell[0],$nombre_turno,1,0,'C');       
                                // Año Lectivo
                                $this->fpdf->Cell(22,$alto_cell[0],mb_convert_encoding("Año Lectivo","ISO-8859-1","UTF-8"),1,0,'L');       
                                $this->fpdf->Cell(10,$alto_cell[0],mb_convert_encoding($nombre_annlectivo,"ISO-8859-1","UTF-8"),1,1,'C');       
                                // FOTO DEL ESTUDIANTE.
                                    if (file_exists('c:/wamp64/www/registro_academico/img/fotos/'.$codigo_institucion.'/'.$nombre_foto))
                                        {
                                            $img = 'c:/wamp64/www/registro_academico/img/fotos/'.$codigo_institucion.'/'.$nombre_foto;	
                                            $this->fpdf->image($img,240,5,35,40);
                                        }else if($codigo_genero == '01'){
                                                $fotos = 'avatar_masculino.png';
                                                $img = '/img/'.$fotos;
                                                $this->fpdf->image(URL::to($img),240,5,35,40);
                                            }
                                            else{
                                                $fotos = 'avatar_femenino.png';
                                                $img = '/img/'.$fotos;
                                                $this->fpdf->image(URL::to($img),240,5,35,40);
                                            }
                                //
                            // VALIDAR VARIABGLES PARA MOSTRAR CABECERA Y CALIFICACIONES.
                            if($codigo_modalidad >= '03' && $codigo_modalidad <= '05'){ // EDUCACI{ON BASICA}
                                $valor_periodo = 2; $valor_actividades = 15; $ancho_area_asignatura = 180;
                            }else if($codigo_modalidad >= '17' && $codigo_modalidad <= '19'){ // EDUCACI{ON BASICA} PL2025
                                $valor_periodo = 2; $valor_actividades = 15; $ancho_area_asignatura = 180;
                            }else if($codigo_modalidad >= '06' && $codigo_modalidad <= '09'){   // EDUCACION MEDIA
                                $valor_periodo = 3; $valor_actividades = 20; $ancho_area_asignatura = 210;
                            }else if($codigo_modalidad >= '10' && $codigo_modalidad <= '12'){   // NOCTURNA
                                $valor_periodo = 4; $valor_actividades = 25; $ancho_area_asignatura = 240;
                            }else if($codigo_modalidad == '15' || $codigo_modalidad == '21'){   // bachillerato general pl2025 y modulos
                                $valor_periodo = 3; $valor_actividades = 20; $ancho_area_asignatura = 210;
                            }
                            else{
                                $valor_periodo = 3; $valor_actividades = 15; $ancho_area_asignatura = 180;    // DEFAULT PUEDE SER PARVULARIA
                            }


                                $this->fpdf->SetX(30); 
                                $this->fpdf->SetFont('Arial', 'B', '7');
                                // fila de información
                                $this->fpdf->Cell(30,$alto_cell[0],"A1->Actividad 1 (35%)",'LR',0,'L');       
                                $this->fpdf->Cell(30,$alto_cell[0],"A2->Actividad 2 (35%)",'LR',0,'L');       
                                $this->fpdf->Cell(35,$alto_cell[0],"PO->Prueba Objetiva (30%)",'LR',0,'L'); 
                                $this->fpdf->Cell(35,$alto_cell[0],"PP->Promedio Periodo",'LR',0,'L');      
                                $this->fpdf->Cell(30,$alto_cell[0],"PF->Promedio Final",'LR',1,'L');          
                                // fila de información 
                                $this->fpdf->SetX(30);       
                                $mensaje_1 = mb_convert_encoding("NR1->Nota Recuperación 1",'ISO-8859-1','UTF-8');
                                $mensaje_2 = mb_convert_encoding("NR1->Nota Recuperación 2",'ISO-8859-1','UTF-8');
                                $this->fpdf->Cell(35,$alto_cell[0],$mensaje_1,'LR',0,'L');             
                                $this->fpdf->Cell(35,$alto_cell[0],$mensaje_2,'LR',0,'L');                
                                $this->fpdf->Cell(20,$alto_cell[0],("A->Aprobado"),'LR',0,'L');                
                                $this->fpdf->Cell(20,$alto_cell[0],("R->Reprobado"),'LR',0,'L');                
                                $this->fpdf->Cell(20,$alto_cell[0],("NF->Nota Final"),'LR',1,'L');                
                            //  $this->fpdf->ln();
                                // cabecera de la tabla de calificaicone4s por periodo
                                $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],"",'LRT',0,'L');
                                for ($pp=0; $pp <= $valor_periodo; $pp++) { 
                                    if($valor_periodo == $pp){
                                        $this->fpdf->Cell($ancho_cell[2],$alto_cell[0],$periodos_a[$pp],1,1,'C');
                                    }else{
                                        $this->fpdf->Cell($ancho_cell[2],$alto_cell[0],$periodos_a[$pp],1,0,'C');
                                    }
                                }
                                // COMPONENTE DE ESTUDIO Y PRIMER FILA DE LAS ACTIVIDADES Y PROMEDIOS
                                $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],"Componente del Plan de Estudio",'LRB',0,'C');             
                                for ($pp=0; $pp <= $valor_periodo; $pp++) { 
                                    for ($ap=0; $ap < count($actividad_periodo) -1; $ap++) { 
                                            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$actividad_periodo[$ap],1,0,'C');
                                    }
                                    if($valor_periodo == $pp){
                                        // colocar celda PF
                                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$actividad_periodo[4].strval($valor_periodo+1),1,0,'C');
                                    }
                                }
                                    // colocar celda NR1
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'NR1',1,0,'C');
                                    // colocar celda NR2
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'NR2',1,0,'C');
                                    // colocar celda NF
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'NF',1,0,'C');
                                    // COLOCAR CELDA RESULTADO.
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$periodos_a[6],1,1,'C');
                                ///////////////////////////////////////////////////////////////////////////////////////////////////
                                /////VERIFICAR ENCABEZADO de AREA DE ASIGNATURAS///////////////////////////////////////////////////
                                ///////////////////////////////////////////////////////////////////////////////////////////////////		
                                /*	"01"	"Básica                                                                     " 0
                                    "02"	"Formativa                                                                  " 1
                                    "03"	"Técnica                                                                    " 2
                                    "04"	"Experiencia y Desarrollo Personal y Social                                 " 3
                                    "05"	"Experiencia y Desarrollo de la Expresión, Comunicación y Representación    " 4
                                    "06"	"Experiencia y Desarrollo de la Relación con el Entorno                     " 5
                                    "07"	"Competencias Ciudadanas                                                    " 6
                                    "08"	"Complementaria                                                             " 7
                                    "09"	"Alertas                                                                    " 8
                                */                                                                 
                                //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                                // DAR FORMATO. -1 en la matriz
                                    //Colores, ancho de l�nea y fuente en negrita
                                    $this->fpdf->SetFillColor(212, 230, 252);
                                    $this->fpdf->SetTextColor(0,0,0);
                                    $this->fpdf->SetFont('Times','B',12);
                                    //print_r($catalogo_area_asignatura_codigo);
                                    //$encabezado_ = EncabezadoCatalogoAreaAsignatura($catalogo_area_asignatura_codigo, $codigo_area);
                                //	print $descripcion_area;
                                    //exit;
                                   //Restauraci�n de colos y fuentes
                                    $this->fpdf->SetFillColor(212, 230, 252);
                                    $this->fpdf->SetTextColor(0);
                                    $this->fpdf->SetFont('Times','',7);	

                        // --- DENTRO DEL BUCLE DE ESTUDIANTES ---
                        // 1. Obtener las materias de este estudiante en particular
                        $notas_estudiante = DB::table('nota as n')
                            ->join('asignatura as a', 'a.codigo', '=', 'n.codigo_asignatura')
                            ->select('n.*', 'a.nombre as nombre_asignatura', 'a.codigo_area')
                            ->where('n.codigo_matricula', $id_matricula) // El NIE del estudiante actual
                            ->where('n.codigo_alumno', $codigo_alumno_seguro) // Asegurarse de que sólo traemos las notas de este alumno
                            ->orderBy('a.codigo_area', 'asc') // Ordenar por área
                            ->orderBy('n.orden', 'asc') // Ordenar por orden de asignatura
                            ->get();


                            // =========================================================================
                            // ¡AQUÍ ESTÁ LA CLAVE!: ARRAY DE CONTROL DE ÁREAS IMPRESAS PARA ESTA HOJA
                            // =========================================================================
                            // Al declararlo vacío justo aquí, nos aseguramos de que cada estudiante empiece de cero limpio
                            $areas_impresas = [];

                        // 2. Reiniciar flags de encabezados de área para cada estudiante
                        $catalogo_area_basica = true; $catalogo_area_formativa = true; 
                        $catalogo_area_tecnica = true; $catalogo_area_cc = true; 
                        $catalogo_area_complementaria = true;

                        // 3. Empieza el detalle de filas por asignatura
                        foreach ($notas_estudiante as $response) {
                            // Re-mapeo del array de notas tal cual lo tienes
                            $nota_actividades_0 = array('',
                                $response->nota_a1_1, $response->nota_a2_1, $response->nota_a3_1, $response->nota_r_1, $response->nota_p_p_1, 
                                $response->nota_a1_2, $response->nota_a2_2, $response->nota_a3_2, $response->nota_r_2, $response->nota_p_p_2, 
                                $response->nota_a1_3, $response->nota_a2_3, $response->nota_a3_3, $response->nota_r_3, $response->nota_p_p_3, 
                                $response->nota_a1_4, $response->nota_a2_4, $response->nota_a3_4, $response->nota_r_4, $response->nota_p_p_4, 
                                $response->nota_a1_5, $response->nota_a2_5, $response->nota_a3_5, $response->nota_r_5, $response->nota_p_p_5, 
                                $response->recuperacion, $response->nota_recuperacion_2, $response->nota_final);

                            $codigo_area = trim($response->codigo_area);
                            $codigo_asignatura = $response->codigo_asignatura;
                            $nombre_asignatura_db = $response->nombre_asignatura;

                            // --- LÓGICA DE ÁREAS (CABECERAS DE SECCIÓN) ---
                            $this->fpdf->SetFillColor(212, 230, 252);
                            $this->fpdf->SetFont('Times','B',8);

                           
                            // =========================================================================
                            // NUEVA LÓGICA DE ÁREAS CON ARRAY (100% EFECTIVA EN BUCLES)
                            // =========================================================================
                            $indice_area = array_search($codigo_area, $catalogo_area_asignatura_codigo);

                            if ($indice_area !== false) {
                                
                                // Si el código de área ACTUAL NO está en nuestro array de "ya impresas", lo dibujamos
                                if (!in_array($codigo_area, $areas_impresas)) {
                                    
                                    // Configuramos el estilo visual del separador de área
                                    $this->fpdf->SetFillColor(212, 230, 252);
                                    $this->fpdf->SetTextColor(0, 51, 102); // Azul institucional
                                    $this->fpdf->SetFont('Arial', 'B', 8);

                                    $nombre_area_print = strtoupper(mb_convert_encoding($catalogo_area_asignatura_area[$indice_area], "ISO-8859-1", "UTF-8"));

                                    // Pintamos la franja horizontal del área
                                    $this->fpdf->Cell($ancho_area_asignatura, 5, $nombre_area_print, 1, 1, 'L', true);

                                    // Registramos esta área como "ya impresa" para el estudiante actual
                                    $areas_impresas[] = $codigo_area;
                                }
                            }

                            // Restaurar fuentes para la fila de notas de la materia
                            $this->fpdf->SetTextColor(0);
                            $this->fpdf->SetFont('Arial', '', 7);



                            // Determinar anchos según Bachillerato Técnico (Modalidad 15)
                            if($codigo_area == "03" && $codigo_modalidad == "15"){
                                $NumeroAnchoColumna = 4; // Ajustar según tu array $ancho_cell
                                $NombreStringAncho = 165;
                            } else {
                                $NumeroAnchoColumna = 0;
                                $NombreStringAncho = 60;
                            }

                            // Nombre de la Asignatura
                            //$this->fpdf->Cell($ancho_cell[$NumeroAnchoColumna], $alto_cell[0], $codigo_asignatura . "-" . substr(mb_convert_encoding($nombre_asignatura_db, "ISO-8859-1", "UTF-8"), 0, $NombreStringAncho), 1, 0, 'L');
                            $this->fpdf->Cell($ancho_cell[$NumeroAnchoColumna], $alto_cell[0], substr(mb_convert_encoding($nombre_asignatura_db, "ISO-8859-1", "UTF-8"), 0, $NombreStringAncho), 1, 0, 'L');

                            // --- BUCLE DINÁMICO DE NOTAS ---
                            // Aquí es donde aplicamos $valor_actividades que calculaste en la cabecera
                            if(!($codigo_area == "03" && $codigo_modalidad == "15")){
                                for ($na=1; $na <= $valor_actividades; $na++) { 
                                    // Resaltar promedios de periodo (PP)
                                    if($na % 5 == 0){
                                        $this->fpdf->SetFillColor(218,215,215);
                                        $this->fpdf->SetFont('Arial', 'B', '7');
                                    } else {
                                        $this->fpdf->SetFillColor(255,255,255);
                                        $this->fpdf->SetFont('Arial', '', '7');
                                    }

                                    $valor_nota = ($nota_actividades_0[$na] == 0) ? '' : $nota_actividades_0[$na];
                                    
                                    // Caso especial: Competencias Ciudadanas (Conceptos en vez de números)
                                    if($codigo_area == '07' && $na % 5 == 0 && $valor_nota != ''){
                                        $result_concepto = resultado_concepto($codigo_modalidad, $valor_nota);
                                        $this->fpdf->Cell($ancho_cell[1], $alto_cell[0], $result_concepto, 1, 0, 'C', true);
                                    } else {
                                        $this->fpdf->Cell($ancho_cell[1], $alto_cell[0], $valor_nota, 1, 0, 'C', true);
                                    }
                                }
                            }

                            // --- COLUMNAS FINALES (NF, NR1, NR2, RESULTADO) ---
                            $this->fpdf->SetFont('Arial', 'B', '7');
                            
                            // Promedio Final
                            $this->fpdf->Cell($ancho_cell[1], $alto_cell[0], ($nota_actividades_0[28] == 0 ? '' : $nota_actividades_0[28]), 1, 0, 'C');
                            
                            // Recuperaciones
                            $this->fpdf->Cell($ancho_cell[1], $alto_cell[0], ($nota_actividades_0[26] == 0 ? '' : $nota_actividades_0[26]), 1, 0, 'C');
                            $this->fpdf->Cell($ancho_cell[1], $alto_cell[0], ($nota_actividades_0[27] == 0 ? '' : $nota_actividades_0[27]), 1, 0, 'C');

                            // Cálculo de Resultado Final (Aprobado/Reprobado)
                            if($nota_actividades_0[28] > 0){
                                $result = resultado_final($codigo_modalidad, $nota_actividades_0[26], $nota_actividades_0[27], $nota_actividades_0[28], $codigo_area);
                                
                                if($result[0] == "R") $this->fpdf->SetTextColor(255,0,0);
                                
                                $this->fpdf->Cell($ancho_cell[1], $alto_cell[0], round($result[1], 0), 1, 0, 'C');
                                $this->fpdf->Cell($ancho_cell[1], $alto_cell[0], $result[0], 1, 1, 'C');
                                
                                $this->fpdf->SetTextColor(0); // Reset color
                            } else {
                                $this->fpdf->Cell($ancho_cell[1], $alto_cell[0], '', 1, 0, 'C');
                                $this->fpdf->Cell($ancho_cell[1], $alto_cell[0], '', 1, 1, 'C');
                            }
                        } // Fin bucle materias

                        // --- CALCULAR PROMEDIOS PARA EL FOOTER ---
          /*  if ($notas_estudiante->count() > 0) {
                // Pasamos la modalidad para que el Footer sepa cuántas celdas dibujar
                //$this->fpdf->num_periodos = $valor_periodo; 

                // Calculamos promedios usando la colección de Laravel
                $this->fpdf->promedios['p1'] = number_format($notas_estudiante->avg('nota_p_p_1'), 1);
                $this->fpdf->promedios['p2'] = number_format($notas_estudiante->avg('nota_p_p_2'), 1);
                $this->fpdf->promedios['p3'] = number_format($notas_estudiante->avg('nota_p_p_3'), 1);
                $this->fpdf->promedios['p4'] = number_format($notas_estudiante->avg('nota_p_p_4'), 1);
                $this->fpdf->promedios['p5'] = number_format($notas_estudiante->avg('nota_p_p_5'), 1);
                $this->fpdf->promedios['final'] = number_format($notas_estudiante->avg('nota_final'), 1);
            }*/
                        // --- SECCIÓN DE FIRMAS Y SELLOS (FINAL DE BOLETA) ---

                    $y_pos = $this->fpdf->GetY() + 2; // Punto de partida para el bloque de firmas

                    // Control de salto de página
               /*     if ($y_pos > 100) { 
                        $this->fpdf->AddPage();
                        $y_pos = 0;
                    }*/

                    // --- 1. POSICIONAR FIRMAS Y SELLO PRIMERO (Para que queden arriba del texto) ---

                    // Firma del Director (Izquierda)
                    if(!empty($firma_director)){
                        $ruta_f_dir = public_path($firma_director); 
                        if(file_exists($ruta_f_dir)){
                            // Ancho 40, Alto proporcional (0). 
                            // La colocamos en X=25 para que quede centrada sobre su texto
                            $this->fpdf->image($ruta_f_dir, 25, $y_pos, 25, 0);
                        }
                    }

                    // Sello de Dirección (Más a la derecha de la firma del director)
                    if(!empty($sello_direccion)){
                        $ruta_s_dir = public_path($sello_direccion);
                        if(file_exists($ruta_s_dir)){
                            // Diámetro 3cm (30x30). X=70 para que no choque con la firma
                            $this->fpdf->image($ruta_s_dir, 75, $y_pos - 1, 30, 30);
                        }
                    }

                    // Firma del Docente (Derecha)
                    if(!empty($firma_docente)){
                        $ruta_firma_docente = public_path('img/firmas/'.$codigo_institucion.'/'.$firma_docente);
                        if (file_exists($ruta_firma_docente)) {
                            // La colocamos alineada a la derecha (X=180 aprox)
                            $this->fpdf->image($ruta_firma_docente, 185, $y_pos, 40, 0);
                        }
                    }

                    // --- 2. POSICIONAR EL TEXTO ABAJO (Nombres y Cargos) ---

                    // Bajamos el cursor para escribir debajo de donde se pusieron las firmas
                    // Sumamos unos 15-20mm para dejar espacio a la imagen de la firma
                    $this->fpdf->SetY($y_pos + 18); 

                    // Nombres
                    $this->fpdf->SetFont('Arial', 'B', 9);
                    $this->fpdf->Cell(140, 5, $nombre_director, 0, 0, 'L');
                    $this->fpdf->Cell(0, 5, $nombre_personal, 0, 1, 'L');

                    // Líneas de cargos
                    $this->fpdf->SetFont('Arial', '', 8);
                    $this->fpdf->Cell(140, 4, 'Director(a) Institucional', 0, 0, 'L');
                    $this->fpdf->Cell(0, 4, 'Docente responsable', 0, 1, 'L');


        } // fin del recorrido de los id matrcicula

        // 5. Salida del PDF
        $nombre_archivo = 'Boletas_Masivas_' . date('Ymd_His') . '.pdf';
        $modoSalida = ($accion === 'descargar') ? 'D' : 'I'; 

        return response($this->fpdf->Output($modoSalida, $nombre_archivo))
                ->header('Content-Type', 'application/pdf');
    }


}




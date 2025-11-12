<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estudiante;
use App\Models\Tablas\Annlectivo;
use App\Models\Tablas\Calificaciones;
use App\Models\Tablas\CargaDocente;
use App\Models\Tablas\EstudianteMatricula;
use App\Models\Tablas\Institucion;
use Illuminate\Support\Facades\DB;

use Brian2694\Toastr\Facades\Toastr;
use App\Http\Controllers\PdfController;

use Carbon\Carbon;
// mail envio
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log; // Recomendado para registrar errores

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ===== AÑADE ESTAS 4 LÍNEAS =====
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Codedge\Fpdf\Fpdf\Fpdf; // La librería FPDF
use Illuminate\Support\Facades\URL;

class CalificacionesPorAsignaturaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // vERIFICAR EL AÑO LECTIVO ACTIVO
        $annlectivo=Annlectivo::where('estatus', true)->orderBy('codigo', 'desc')->pluck('nombre','codigo')->toarray();
            return view('CalificacionPorAsignatura.index', compact('annlectivo'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        //Mail::to('carlos.w.cerna@gmail.com')->send(new BoletaEstudiantes());

        $CargaDocente = CargaDocente::join('bachillerato_ciclo', 'bachillerato_ciclo.codigo', '=', 'carga_docente.codigo_bachillerato')
            ->join('grado_ano', 'grado_ano.codigo', '=', 'carga_docente.codigo')
            ->join('seccion', 'seccion.turno', '=', 'carga_docente.codigo')
            ->join('turno','turno.codigo', '=', 'carga_docente.codigo')
            ->select('carga_docente.codigo_bachillerato','carga_docente.codigo_grado','carga_docente.codigo_seccion','carga_docente.codigo_turno','carga_docente.codigo_ann_lectivo',
            'bachillerato.nombre as nombre_modalidad','grado_ano.nombre as nombre_grado','seccion.nombre as nombre_seccion','turno.nombre as nombre_turno')
            ->get();

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    /**
    * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getGradoSeccion()
    {
        $codigo_personal = $_POST['id'];
        $codigo_annlectivo = $_POST['codigo_annlectivo'];
        $GradoSeccionTurno = array();

            $CargaDocente = DB::table('carga_docente')
                ->distinct()
                ->join('bachillerato_ciclo','carga_docente.codigo_bachillerato','=','bachillerato_ciclo.codigo')
                ->join('grado_ano','carga_docente.codigo_grado','=','grado_ano.codigo')
                ->join('seccion', 'carga_docente.codigo_seccion', '=', 'seccion.codigo')                
                ->join('turno','carga_docente.codigo_turno', '=', 'turno.codigo')
                ->select('codigo_bachillerato', 'codigo_grado','codigo_seccion', 'codigo_turno', 'codigo_docente','bachillerato_ciclo.nombre as nombre_bachillerato', 'grado_ano.nombre as nombre_grado'
                ,'seccion.nombre as nombre_seccion', 'turno.nombre as nombre_turno')
                ->where('codigo_docente', '=', $codigo_personal)
                ->where([
                    ['codigo_docente', '=', $codigo_personal],
                    ['codigo_ann_lectivo', '=', $codigo_annlectivo],
                    ])
                ->get();
                
                $fila_array = 0;
                foreach($CargaDocente as $response){  //Llenar el arreglo con datos
                    $codigos_ = $response->codigo_grado . $response->codigo_seccion . $response->codigo_turno . $response->codigo_bachillerato; 
                    $nombres_ = trim($response->nombre_grado) . ' ' . trim($response->nombre_seccion) . ' - ' . trim($response->nombre_turno) . ' - ' . trim($response->nombre_bachillerato);
                    $GradoSeccionTurno[$fila_array] = array ( 
                        "codigo_gradoseccionturno" => $codigos_,
                        "nombre_gradoseccionturno" => $nombres_,
                    ); 
                    $fila_array++;
                }
            return $GradoSeccionTurno;
    }
    // EXAMINA LA CARGA DOCENTE ASIGNADA
    public function getGradoSeccionAsignaturas()
    {
        $codigo_personal = $_POST['id'];
        $codigo_annlectivo = $_POST['codigo_annlectivo'];
        $codigo_gradoseccionturno = $_POST['codigo_gradoseccionturno'];
        $codigo_grado = substr($codigo_gradoseccionturno,0,2);
        $codigo_seccion = substr($codigo_gradoseccionturno,2,2);
        $codigo_turno = substr($codigo_gradoseccionturno,4,2);
        $codigo_modalidad = substr($codigo_gradoseccionturno,6,2);

        $GradoSeccionTurnoAsignaturas = array();
           
            $CargaDocente = DB::table('carga_docente')
                ->join('bachillerato_ciclo','carga_docente.codigo_bachillerato','=','bachillerato_ciclo.codigo')
                ->join('grado_ano','carga_docente.codigo_grado','=','grado_ano.codigo')
                ->join('seccion', 'carga_docente.codigo_seccion', '=', 'seccion.codigo')                
                ->join('turno','carga_docente.codigo_turno', '=', 'turno.codigo')
                ->join('asignatura','carga_docente.codigo_asignatura','=','asignatura.codigo')
                ->select('codigo_bachillerato', 'codigo_grado','codigo_seccion', 'codigo_turno', 'codigo_docente','bachillerato_ciclo.nombre as nombre_bachillerato', 'grado_ano.nombre as nombre_grado'
                ,'seccion.nombre as nombre_seccion', 'turno.nombre as nombre_turno','codigo_asignatura','asignatura.nombre as nombre_asignatura','asignatura.codigo_area')
                ->where('codigo_docente', '=', $codigo_personal)
                ->where([
                    ['codigo_docente', '=', $codigo_personal],
                    ['codigo_ann_lectivo', '=', $codigo_annlectivo],
                    ['codigo_grado', '=', $codigo_grado],
                    ['codigo_seccion', '=', $codigo_seccion],
                    ['codigo_turno', '=', $codigo_turno],
                    ['codigo_bachillerato', '=', $codigo_modalidad],
                    ])
                ->orderBy('codigo_asignatura','asc')
                ->get();
                
                $fila_array = 0;
                foreach($CargaDocente as $response){  //Llenar el arreglo con datos
                    $codigos_ = trim($response->codigo_asignatura);
                    $nombres_ = trim($response->nombre_asignatura);
                    $codigo_area_ = trim($response->codigo_area);
                    $GradoSeccionTurnoAsignaturas[$fila_array] = array ( 
                        "codigo_asignatura" => $codigos_,
                        "nombre_asignatura" => $nombres_,
                        "codigo_area" => $codigo_area_
                    ); 
                    $fila_array++;
                }
            return $GradoSeccionTurnoAsignaturas;
    }
    // EXAMINA LAS FECHAS ASIGNADAS POR CADA PERIODO PARA EL INGRESO DE LAS CALIFICACIONES.
    public function getPeriodo()
    {
        $codigo_personal = $_POST['id'];
        $codigo_annlectivo = $_POST['codigo_annlectivo'];
        $codigo_gradoseccionturno = $_POST['codigo_gradoseccionturno'];
        $codigo_grado = substr($codigo_gradoseccionturno,0,2);
        $codigo_seccion = substr($codigo_gradoseccionturno,2,2);
        $codigo_turno = substr($codigo_gradoseccionturno,4,2);
        $codigo_modalidad = substr($codigo_gradoseccionturno,6,2);
        // Evaluar el codigo modalidad.
        // VALIDAR VARIABGLES PARA MOSTRAR CABECERA Y CALIFICACIONES.
        if($codigo_modalidad >= '03' && $codigo_modalidad <= '05'){ // EDUCACI{ON BASICA}
           // $codigo_modalidad = '03';
        }else if($codigo_modalidad >= '06' && $codigo_modalidad <= '09'){   // EDUCACION MEDIA
            //$codigo_modalidad = '06';
        }else if($codigo_modalidad >= '10' && $codigo_modalidad <= '12'){   // NOCTURNA
            //$codigo_modalidad = '10';
        }else{
            //$codigo_modalidad = '03';    // DEFAULT PUEDE SER PARVULARIA
        }
        // Array
        $Periodo = array();
        // Variable fecha.
            $hoy = Carbon::now();
            $date = $hoy->format('Y-m-d');
            $PeriodoCalendario = DB::table('periodo_calendario')
                ->join('catalogo_periodo','catalogo_periodo.id_','=','periodo_calendario.codigo_periodo')
                ->select('catalogo_periodo.codigo','catalogo_periodo.descripcion','periodo_calendario.codigo_modalidad','periodo_calendario.codigo_annlectivo',
                'periodo_calendario.fecha_desde','periodo_calendario.fecha_registro_academico','periodo_calendario.estatus')
                ->where([
                    ['codigo_annlectivo', '=', $codigo_annlectivo],
                    ['codigo_modalidad', '=', $codigo_modalidad],
                    //['estatus', '=', 'true'],
                    //['fecha_desde', '>=', $date],
                    //['fecha_registro_academico', '<=', $date],
                    ])
                //->whereBetween('fecha_desde',[$date])
                ->whereDate('fecha_desde','<=',$date)
                ->whereDate('fecha_registro_academico','>=',$date)
                //->whereDate('fecha_registro_academico','<=',$date)
                ->orderBy('codigo','asc')
                ->get();
                
                $fila_array = 0;
                foreach($PeriodoCalendario as $response){  //Llenar el arreglo con datos
                    $codigos_ = trim($response->codigo);
                    $nombres_ = trim($response->descripcion);
                    $fecha_desde_ = trim($response->fecha_desde);
                    $Periodo[$fila_array] = array ( 
                        "codigo_periodo" => $codigos_,
                        "nombre_periodo" => $nombres_,
                        "fecha"=> $date,
                        "fecha_desde"=> $fecha_desde_,
                    ); 
                    $fila_array++;
                }
            return $Periodo;
    }
    // 
    public function getGradoSeccionCalificacionesAsignaturas()
    {
        $codigo_annlectivo = $_POST['codigo_annlectivo'];
        $codigo_gradoseccionturno = $_POST['codigo_gradoseccionturno'];
        $codigo_asignatura = $_POST['codigo_asignatura'];
        $codigo_area = $_POST['codigo_area'];
        $codigo_actividad = $_POST['codigo_actividad'];
        $codigo_periodo = $_POST['codigo_periodo'];
        $codigo_grado = substr($codigo_gradoseccionturno,0,2);
        $codigo_seccion = substr($codigo_gradoseccionturno,2,2);
        $codigo_turno = substr($codigo_gradoseccionturno,4,2);
        $codigo_modalidad = substr($codigo_gradoseccionturno,6,2);
         // CAMBIAR EL VALOR DE LA VARIABLE "ACTIVIDAD PORCENTAJE" DEPENDIENDO DEL PERIODO
         // 01 - PERIODO 1 ... 05 - PERIODO 5
         // CODIGO ACTIVIDAD
         // 01- NOTA_A1_1 ... 03 - NOTA_A2_1

         // LOS CODIGOS DE AREA SON 
            /*
                ESTAS MOSTRARAN LOS PORCENTAJES DE CADA PERIODO 35% 35% 30%
                01 - BASICA , 02 - FORMATIVA, 03 - TECNICA, 08 - COMPLEMENTARIA

                ESTAS MOSTRARAN SOLO EL PERIODO OSEA NOTA_P_P_1...
                04 - 05 - 06 - 07 - 09
            */

            $nombre_periodos = array('nota_p_p_','recuperacion','nota_recuperacion_2','nota_final');
            $nombre_actividades = array('nota_a1_','nota_a2_','nota_a3_');
            $nombre_recuperaciones = array('nota_r_');
            $nombre_observacion = array('observacion_');
            $numero_periodo = 0;
            switch ($codigo_periodo) {
                case '01':  // nota_p_p_1
                    $nombre_periodo = $nombre_periodos[0] . '1';
                    $nombre_recuperacion = $nombre_recuperaciones[0] . '1';
                    $numero_periodo = '1';
                break;
                case '02':  // nota_p_p_2
                    $nombre_periodo = $nombre_periodos[0] . '2';
                    $nombre_recuperacion = $nombre_recuperaciones[0] . '2';
                    $numero_periodo = '2';
                break;
                case '03':  // nota_p_p_3
                    $nombre_periodo = $nombre_periodos[0] . '3';
                    $nombre_recuperacion = $nombre_recuperaciones[0] . '3';
                    $numero_periodo = '3';
                break;
                case '04':  // nota_p_p_4
                    $nombre_periodo = $nombre_periodos[0] . '4';
                    $nombre_recuperacion = $nombre_recuperaciones[0] . '4';
                    $numero_periodo = '4';
                break;
                case '05':  // nota_p_p_5
                    $nombre_periodo = $nombre_periodos[0] . '5';
                    $nombre_recuperacion = $nombre_recuperaciones[0] . '5';
                    $numero_periodo = '5';
                break;
                case '06':  // recuperacion
                    $nombre_periodo = $nombre_periodos[1];
                    $numero_periodo = '6';
                break;
                case '07':  // nota_recuperacion_2
                    $nombre_periodo = $nombre_periodos[2];
                    $numero_periodo = '7';
                break;
                case '08':  // nota_FINAL - para el bachillerato tecnico auxiliar contable.
                    $nombre_periodo = $nombre_periodos[3];
                    $numero_periodo = '8';
                break;
            }
            // ACTIVIDADES
            // EVALUAR EL AREA DE LA ASIGNATURA
            if($codigo_area == '01' || $codigo_area == '02' || $codigo_area == '03' || $codigo_area == '08')
            {
                // EVALUAR SOMALENTE MUCI.
                if($codigo_area == '08' && $codigo_asignatura == '234' || $codigo_asignatura == '736' || $codigo_asignatura == '726'){
                    // cambiar el nombre de actividad por el nombre del periodo
                    // cuando es LA CONVIVENCIA CIUDADANA O MUCI
                        $nombre_actividad = $nombre_periodo;
                }else if($codigo_area == "03" && $codigo_modalidad == "15"){
                        $nombre_actividad = $nombre_periodo;
                }else{
                    switch ($codigo_actividad) {
                        case '01':
                            $nombre_actividad = $nombre_actividades[0] .  $numero_periodo;
                        break;
                        case '02':
                            $nombre_actividad = $nombre_actividades[1] . $numero_periodo;
                        break;
                        case '03':
                            $nombre_actividad = $nombre_actividades[2] .  $numero_periodo;
                        break;
                        case '04':
                            $nombre_actividad = $nombre_recuperaciones[0] .  $numero_periodo;
                        break;
                        case '06':
                            $nombre_actividad = $nombre_periodos[1];
                        break;
                        case '07':
                            $nombre_actividad = $nombre_periodos[2];
                        break;
                        case '08':
                            $nombre_actividad = $nombre_periodos[3];
                        break;
                    }
                }
                
            }else{
                // cambiar el nombre de actividad por el nombre del periodo
                // cuando es LA CONVIVENCIA CIUDADANA O MUCI
                    $nombre_actividad = $nombre_periodo;
            }
        $EstudiantesMatricula = DB::table('alumno as a')
                ->join('alumno_matricula as am','a.id_alumno','=','am.codigo_alumno')
                ->join('nota as n','am.id_alumno_matricula','=','n.codigo_matricula')
                ->select('a.id_alumno as codigo_alumno','a.codigo_nie','a.nombre_completo',"a.apellido_paterno",'a.apellido_materno','am.id_alumno_matricula as codigo_matricula',
                        'n.id_notas', "$nombre_actividad as nota_actividad",'n.nota_final',
                        DB::raw("TRIM(CONCAT(BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno), CAST(' ' AS VARCHAR), BTRIM(a.nombre_completo))) as full_name"))
                ->where([
                    ['am.codigo_bach_o_ciclo', '=', $codigo_modalidad],
                    ['am.codigo_ann_lectivo', '=', $codigo_annlectivo],
                    ['am.codigo_grado', '=', $codigo_grado],
                    ['am.codigo_seccion', '=', $codigo_seccion],
                    ['am.codigo_turno', '=', $codigo_turno],
                    ['n.codigo_asignatura', '=', $codigo_asignatura],
                    ['am.retirado', '=', 'f'],
                    ])
                ->orderBy('full_name','asc')
                ->get();

             //   Toastr::success('Messages in here', 'Title', ["positionClass" => "toast-top-center"]);
                    return $EstudiantesMatricula;
    }
    // evalua ACTUALIZACIÓN DE CALIFICACIONES CON RESPECTO A MODALIDAD, GRADO, PERIODO Y ACTIVIDAD.
    function getActualizarCalificacion(Request $request){
        $fila = $request->fila;
        $codigo_calificacion['codigo_calificacion'] = $request->codigo_calificacion;
        $calificacion['calificacion'] = $request->calificacion;
        $codigo_actividad = $request->codigo_actividad;
        $codigo_periodo = $request->codigo_periodo;
        $codigo_area = $request->codigo_area;
        $codigo_modalidad = substr($request->codigo_gradoseccionturno,6,2);
        $codigo_asignatura = $request->codigo_asignatura;
        // CAMBIAR EL VALOR DE LA VARIABLE "ACTIVIDAD PORCENTAJE" DEPENDIENDO DEL PERIODO
        // 01 - PERIODO 1 ... 05 - PERIODO 5
        // CODIGO ACTIVIDAD
        // 01- NOTA_A1_1 ... 03 - NOTA_A2_1
                // echo "<pre>";
                // print_r($calificacion);
                //  echo "</pre>";
                $nombre_periodos = array('nota_p_p_','recuperacion','nota_recuperacion_2','nota_final');
                $nombre_actividades = array('nota_a1_','nota_a2_','nota_a3_');
                $nombre_recuperaciones = array('nota_r_');
                $numero_periodo = 0;
                $numero_p2 = 2; $numero_p3 = 3;

                    switch ($codigo_periodo) {
                        case '01':  // nota_p_p_1
                            $nombre_periodo = $nombre_periodos[0] . '1';
                            $nombre_recuperacion = $nombre_recuperaciones[0] . '1';
                            $numero_periodo = '1';
                        break;
                        case '02':  // nota_p_p_2
                            $nombre_periodo = $nombre_periodos[0] . '2';
                            $nombre_recuperacion = $nombre_recuperaciones[0] . '2';
                            $numero_periodo = '2';
                        break;
                        case '03':  // nota_p_p_3
                            $nombre_periodo = $nombre_periodos[0] . '3';
                            $nombre_recuperacion = $nombre_recuperaciones[0] . '3';
                            $numero_periodo = '3';
                        break;
                        case '04':  // nota_p_p_4
                            $nombre_periodo = $nombre_periodos[0] . '4';
                            $nombre_recuperacion = $nombre_recuperaciones[0] . '4';
                            $numero_periodo = '4';
                        break;
                        case '05':  // nota_p_p_5
                            $nombre_periodo = $nombre_periodos[0] . '5';
                            $nombre_recuperacion = $nombre_recuperaciones[0] . '5';
                            $numero_periodo = '5';
                        break;
                        case '06':  // recuperacion
                            $nombre_periodo = $nombre_periodos[1];
                            $numero_periodo = '6';
                        break;
                        case '07':  // nota_recuperacion_2
                            $nombre_periodo = $nombre_periodos[2];
                            $numero_periodo = '7';
                        break;
                        case '08':  // nota final
                            $nombre_periodo = $nombre_periodos[3];
                            $numero_periodo = '8';
                        break;
                    }
                // EVALUAR EL AREA DE LA ASIGNATURA
                    $calcular_promedio = false;
                if($codigo_area == '01' || $codigo_area == '02' || $codigo_area == '03' || $codigo_area == '08')
                {
                    // ACTIVIDADES
                    switch ($codigo_actividad) {
                        case '01':
                            $nombre_actividad = $nombre_actividades[0] .  $numero_periodo;
                        break;
                        case '02':
                            $nombre_actividad = $nombre_actividades[1] . $numero_periodo;
                        break;
                        case '03':
                            $nombre_actividad = $nombre_actividades[2] .  $numero_periodo;
                        break;
                        case '04':
                            $nombre_actividad = $nombre_recuperaciones[0] .  $numero_periodo;
                        break;
                        case '08':
                            $nombre_actividad = $nombre_periodo;
                        break;
                    }
                }else{
                        // cambiar el nombre de actividad por el nombre del periodo
                        $nombre_actividad = $nombre_periodo;
                }
                $nombre_actividad_1 = $nombre_actividades[0] .  $numero_periodo;
                $nombre_actividad_2 = $nombre_actividades[1] .  $numero_periodo;
                $nombre_actividad_3 = $nombre_actividades[2] .  $numero_periodo;
                $nombre_actividad_r = $nombre_recuperaciones[0] .  $numero_periodo;
                // FORMAR EL STRING DE EL UPDATE.
                    $actual = array();
                        for ($i=0; $i < $fila; $i++) { 
                            $id_notas_ = $codigo_calificacion['codigo_calificacion'][$i];
                            $calificacion_ = floatval($calificacion['calificacion'][$i]);
                            if($calificacion_ == 0 || $calificacion_ == '0'){
                                $calificacion_ = intval($calificacion_);
                            }
                            //////////////////////////////////////////////////////////////////////////
                            // QUERY DB ACTUALIZAR.
                            ////////////////////////////////////////////////////////////////////////
                                if($codigo_area == '01' || $codigo_area == '02' || $codigo_area == '03' || $codigo_area == '08' || $codigo_area == '07') 
                                {
                                    // AGREGAR QUE SEA UN ENTERO YT UN DECIMAL PARA EDUCACION MEDIA.
                                    //
                                    switch ($codigo_modalidad) {
                                        case ($codigo_modalidad == '03' || $codigo_modalidad == '04' || $codigo_modalidad == '05' || $codigo_modalidad == '17' || $codigo_modalidad == '18' || $codigo_modalidad == '19'):
                                            if($numero_periodo == '6' || $numero_periodo == '7'){   // CONDICIÓN PARA COMPETENCIA CIUDADANA, NOTA (RECUPERACION, NOTA_RECUPERACION_2)
                                                DB::update("UPDATE nota set $nombre_periodo = ? where id_notas = ?", [$calificacion_,  $id_notas_]); // ACTUALIZAR LA CALIFICACION, A1, A2, PO, R
                                            }else{
                                                
                                                if($codigo_asignatura == '235' || $codigo_asignatura == '234' || $codigo_area == '07'){
                                                    DB::update("UPDATE nota set $nombre_actividades[0]$numero_periodo = ?, $nombre_actividades[1]$numero_periodo = ?, $nombre_actividades[2]$numero_periodo = ? where id_notas = ?", [$calificacion_, $calificacion_, $calificacion_, $id_notas_]); // ACTUALIZAR LA CALIFICACION, A1, A2, PO, R
                                                }else{
                                                    DB::update("UPDATE nota set $nombre_actividad = ? where id_notas = ?", [$calificacion_ , $id_notas_]); // ACTUALIZAR LA CALIFICACION, A1, A2, PO, R
                                                }
                                                //////////////////////////////////////////////////////////////////////////////////////////////////// 
                                                // EXTRAR LA INFORMACION DE LA TABLA NOTA PARA CALCULAR EL NUEVO PROMEDIO. PP
                                                ////////////////////////////////////////////////////////////////////////////////////////////////////
                                                        $CalificacionRecuperacion = DB::table('nota')
                                                        ->select("$nombre_actividades[0]$numero_periodo as actividad_1","$nombre_actividades[1]$numero_periodo as actividad_2","$nombre_actividades[2]$numero_periodo as actividad_3")
                                                        ->where([
                                                            ['id_notas', '=', $id_notas_],
                                                            ])
                                                        ->orderBy('id_notas','asc')
                                                        ->get();
                                                        
                                                        $fila_array = 0;
                                                        foreach($CalificacionRecuperacion as $response){  //Llenar el arreglo con datos
                                                            $actividad_1_ = trim($response->actividad_1);
                                                            $actividad_2_ = trim($response->actividad_2);
                                                            $actividad_3_ = trim($response->actividad_3);
                                                            $fila_array++;
                                                        }
                                                //
                                                //  EVALUAR SI CODIGO ACTIVIDAD ES IGUAL A "04" QUE ES LA CALIFICACIÓN DE RECUPERACIÓN.
                                                //
                                                if($codigo_actividad == '04'){
                                                    // ACTUALIZAR PROMEDIO DEL PERIODO
                                                    if($calificacion_ == 0){
                                                        DB::update("UPDATE nota set $nombre_periodo = round(($nombre_actividad_1 * 0.35) + ($nombre_actividad_2 * 0.35) + ($nombre_actividad_3 * 0.30),0) where id_notas = ?", [$id_notas_]);
                                                    }else{
                                                        // RECALCULAR PROMEDIO EN A1 O A2.
                                                            if($actividad_1_ > $actividad_2_){
                                                                DB::update("UPDATE nota set $nombre_periodo = round(($nombre_actividad_1 * 0.35) + ($nombre_recuperacion * 0.35) + ($nombre_actividad_3 * 0.30),0) where id_notas = ?", [$id_notas_]);
                                                            }else{
                                                                DB::update("UPDATE nota set $nombre_periodo = round(($nombre_recuperacion * 0.35) + ($nombre_actividad_2 * 0.35) + ($nombre_actividad_3 * 0.30),0) where id_notas = ?", [$id_notas_]);
                                                            }
                                                            $actual['update'] = $actividad_1_ . " - " . $actividad_2_ . " - " . $actividad_3_;
                                                    }
                                                }else{
                                                    // actualizar cuando el periodo es normal
                                                    DB::update("UPDATE nota set $nombre_periodo = round(($nombre_actividad_1 * 0.35) + ($nombre_actividad_2 * 0.35) + ($nombre_actividad_3 * 0.30),0) where id_notas = ?", [$id_notas_]);
                                                }
                                            }
                                        break;
                                        case ($codigo_modalidad == '06' || $codigo_modalidad == '07' || $codigo_modalidad == '08' || $codigo_modalidad == '09'):  // EDUCACION MEDIA.*********//////
                                            if($codigo_area == '07' || $numero_periodo == '6' || $numero_periodo == '7'){   // CONDICIÓN PARA COMPETENCIA CIUDADANA, NOTA (RECUPERACION, NOTA_RECUPERACION_2)
                                                $actual['update'] = DB::update("UPDATE nota set $nombre_periodo = ? where id_notas = ?", [$calificacion_ , $id_notas_]);
                                            }else{
                                                DB::update("UPDATE nota set $nombre_actividad = ? where id_notas = ?", [$calificacion_ , $id_notas_]); // ACTUALIZAR LA CALIFICACION, A1, A2, PO, R
                                                //////////////////////////////////////////////////////////////////////////////////////////////////// 
                                                // EXTRAR LA INFORMACION DE LA TABLA NOTA PARA CALCULAR EL NUEVO PROMEDIO. PP
                                                ////////////////////////////////////////////////////////////////////////////////////////////////////
                                                $CalificacionRecuperacion = DB::table('nota')
                                                ->select("$nombre_actividades[0]$numero_periodo as actividad_1","$nombre_actividades[1]$numero_periodo as actividad_2","$nombre_actividades[2]$numero_periodo as actividad_3")
                                                ->where([
                                                    ['id_notas', '=', $id_notas_],
                                                    ])
                                                ->orderBy('id_notas','asc')
                                                ->get();
                                                
                                                $fila_array = 0;
                                                foreach($CalificacionRecuperacion as $response){  //Llenar el arreglo con datos
                                                    $actividad_1_ = trim($response->actividad_1);
                                                    $actividad_2_ = trim($response->actividad_2);
                                                    $actividad_3_ = trim($response->actividad_3);
                                                    $fila_array++;
                                                }
                                                //
                                                //  EVALUAR SI CODIGO ACTIVIDAD ES IGUAL A "04" QUE ES LA CALIFICACIÓN DE RECUPERACIÓN.
                                                //
                                                if($codigo_actividad == '04'){
                                                    // ACTUALIZAR PROMEDIO DEL PERIODO
                                                    if($calificacion_ == 0){
                                                        DB::update("UPDATE nota set $nombre_periodo = round(($nombre_actividad_1 * 0.35) + ($nombre_actividad_2 * 0.35) + ($nombre_actividad_3 * 0.30),1) where id_notas = ?", [$id_notas_]);
                                                    }else{
                                                        // RECALCULAR PROMEDIO EN A1 O A2.
                                                        if($actividad_1_ > $actividad_2_){
                                                                DB::update("UPDATE nota set $nombre_periodo = round(($nombre_actividad_1 * 0.35) + ($nombre_recuperacion * 0.35) + ($nombre_actividad_3 * 0.30),1) where id_notas = ?", [$id_notas_]);
                                                            }else{
                                                                DB::update("UPDATE nota set $nombre_periodo = round(($nombre_recuperacion * 0.35) + ($nombre_actividad_2 * 0.35) + ($nombre_actividad_3 * 0.30),1) where id_notas = ?", [$id_notas_]);
                                                            }
                                                            $actual['update'] = $nombre_periodo . " - " . $nombre_actividad_1 . " - " . $nombre_recuperacion ." - " . $nombre_actividad_3;
                                                    }
                                                }else{
                                                    // actualizar cuando el periodo es normal
                                                    DB::update("UPDATE nota set $nombre_periodo = round(($nombre_actividad_1 * 0.35) + ($nombre_actividad_2 * 0.35) + ($nombre_actividad_3 * 0.30),1) where id_notas = ?", [$id_notas_]);
                                                }
                                            }
                                        break;
                                        case ($codigo_modalidad >= '10' && $codigo_modalidad <= '12'): // NOCTURNA *******//
                                            if($codigo_area == '07' || $numero_periodo == '6' || $numero_periodo == '7'){   // CONDICIÓN PARA COMPETENCIA CIUDADANA, NOTA (RECUPERACION, NOTA_RECUPERACION_2)
                                                DB::update("UPDATE nota set $nombre_periodo = ? where id_notas = ?", [$calificacion_ , $id_notas_]);
                                            }else{
                                                DB::update("UPDATE nota set $nombre_actividad = ? where id_notas = ?", [$calificacion_ , $id_notas_]); // ACTUALIZAR LA CALIFICACION, A1, A2, PO, R
                                                                                                //////////////////////////////////////////////////////////////////////////////////////////////////// 
                                                // EXTRAR LA INFORMACION DE LA TABLA NOTA PARA CALCULAR EL NUEVO PROMEDIO. PP
                                                ////////////////////////////////////////////////////////////////////////////////////////////////////
                                                $CalificacionRecuperacion = DB::table('nota')
                                                ->select("$nombre_actividades[0]$numero_periodo as actividad_1","$nombre_actividades[1]$numero_periodo as actividad_2","$nombre_actividades[2]$numero_periodo as actividad_3")
                                                ->where([
                                                    ['id_notas', '=', $id_notas_],
                                                    ])
                                                ->orderBy('id_notas','asc')
                                                ->get();
                                                
                                                $fila_array = 0;
                                                foreach($CalificacionRecuperacion as $response){  //Llenar el arreglo con datos
                                                    $actividad_1_ = trim($response->actividad_1);
                                                    $actividad_2_ = trim($response->actividad_2);
                                                    $actividad_3_ = trim($response->actividad_3);
                                                    $fila_array++;
                                                }
                                                //
                                                //  EVALUAR SI CODIGO ACTIVIDAD ES IGUAL A "04" QUE ES LA CALIFICACIÓN DE RECUPERACIÓN.
                                                //
                                                if($codigo_actividad == '04'){
                                                    // ACTUALIZAR PROMEDIO DEL PERIODO
                                                    if($calificacion_ == 0){
                                                        DB::update("UPDATE nota set $nombre_periodo = round(($nombre_actividad_1 * 0.35) + ($nombre_actividad_2 * 0.35) + ($nombre_actividad_3 * 0.30),0) where id_notas = ?", [$id_notas_]);
                                                    }else{

                                                        // RECALCULAR PROMEDIO EN A1 O A2.
                                                            if($actividad_1_ > $actividad_2_){
                                                                DB::update("UPDATE nota set $nombre_periodo = round(($nombre_actividad_1 * 0.35) + ($nombre_recuperacion * 0.35) + ($nombre_actividad_3 * 0.30),0) where id_notas = ?", [$id_notas_]);
                                                            }else{
                                                                DB::update("UPDATE nota set $nombre_periodo = round(($nombre_recuperacion * 0.35) + ($nombre_actividad_2 * 0.35) + ($nombre_actividad_3 * 0.30),0) where id_notas = ?", [$id_notas_]);
                                                            }
                                                    }
                                                }else{
                                                    // actualizar cuando el periodo es normal
                                                    DB::update("UPDATE nota set $nombre_periodo = round(($nombre_actividad_1 * 0.35) + ($nombre_actividad_2 * 0.35) + ($nombre_actividad_3 * 0.30),0) where id_notas = ?", [$id_notas_]);
                                                }
                                            }
                                        break;
                                        case ($codigo_modalidad == '15' || $codigo_modalidad == '21'):  // EDUCACION MEDIA.*********//////
                                            if($codigo_area == '07' || $numero_periodo == '6' || $numero_periodo == '7' || $codigo_area == '03'){   // CONDICIÓN PARA COMPETENCIA CIUDADANA, NOTA (RECUPERACION, NOTA_RECUPERACION_2)
                                                $actual['update'] = DB::update("UPDATE nota set $nombre_periodo = ? where id_notas = ?", [$calificacion_ , $id_notas_]);
                                            }else{
                                                DB::update("UPDATE nota set $nombre_actividad = ? where id_notas = ?", [$calificacion_ , $id_notas_]); // ACTUALIZAR LA CALIFICACION, A1, A2, PO, R
                                                //////////////////////////////////////////////////////////////////////////////////////////////////// 
                                                // EXTRAR LA INFORMACION DE LA TABLA NOTA PARA CALCULAR EL NUEVO PROMEDIO. PP
                                                ////////////////////////////////////////////////////////////////////////////////////////////////////
                                                $CalificacionRecuperacion = DB::table('nota')
                                                ->select("$nombre_actividades[0]$numero_periodo as actividad_1","$nombre_actividades[1]$numero_periodo as actividad_2","$nombre_actividades[2]$numero_periodo as actividad_3")
                                                ->where([
                                                    ['id_notas', '=', $id_notas_],
                                                    ])
                                                ->orderBy('id_notas','asc')
                                                ->get();
                                                
                                                $fila_array = 0;
                                                foreach($CalificacionRecuperacion as $response){  //Llenar el arreglo con datos
                                                    $actividad_1_ = trim($response->actividad_1);
                                                    $actividad_2_ = trim($response->actividad_2);
                                                    $actividad_3_ = trim($response->actividad_3);
                                                    $fila_array++;
                                                }
                                                //
                                                //  EVALUAR SI CODIGO ACTIVIDAD ES IGUAL A "04" QUE ES LA CALIFICACIÓN DE RECUPERACIÓN.
                                                //
                                                if($codigo_actividad == '04'){
                                                    // ACTUALIZAR PROMEDIO DEL PERIODO
                                                    if($calificacion_ == 0){
                                                        DB::update("UPDATE nota set $nombre_periodo = round(($nombre_actividad_1 * 0.35) + ($nombre_actividad_2 * 0.35) + ($nombre_actividad_3 * 0.30),1) where id_notas = ?", [$id_notas_]);
                                                    }else{
                                                        // RECALCULAR PROMEDIO EN A1 O A2.
                                                        if($actividad_1_ > $actividad_2_){
                                                                DB::update("UPDATE nota set $nombre_periodo = round(($nombre_actividad_1 * 0.35) + ($nombre_recuperacion * 0.35) + ($nombre_actividad_3 * 0.30),1) where id_notas = ?", [$id_notas_]);
                                                            }else{
                                                                DB::update("UPDATE nota set $nombre_periodo = round(($nombre_recuperacion * 0.35) + ($nombre_actividad_2 * 0.35) + ($nombre_actividad_3 * 0.30),1) where id_notas = ?", [$id_notas_]);
                                                            }
                                                            $actual['update'] = $nombre_periodo . " - " . $nombre_actividad_1 . " - " . $nombre_recuperacion ." - " . $nombre_actividad_3;
                                                    }
                                                }else{
                                                    // actualizar cuando el periodo es normal
                                                    DB::update("UPDATE nota set $nombre_periodo = round(($nombre_actividad_1 * 0.35) + ($nombre_actividad_2 * 0.35) + ($nombre_actividad_3 * 0.30),1) where id_notas = ?", [$id_notas_]);
                                                }
                                            }
                                        break;
                                        default:
                                                break;
                                    }
                                }
                            // CODIGO MODALIDAD PARA REALIZAR LA ACTUALIZACION PROMEDIO FINAL
                                /// VALIDAR PRIMERO A QUE MODALIDAD PERTENECE
                                // 03 -> PRIMER CICLO
                                // 04 -> SEGUNDO CICLO
                                // 05 -> TERCER CICLO
                                // 06 -> BACHILLERATO GENERAL
                                // 07 -> BACHILLERATO TECNICO
                                // 08 -> BACHILLERATO TECNICO VOCACIONAL SECRETARIADO
                                // 09 -> BACHILLERATO TECNICO VOCACIONAL CONTADUR
                                // 10 -> TERCER CICLO NOCTURNA
                                // 11 -> BACHILLERATO GENERAL NOCTURNA
                                // 12 -> EDUCACION BASDICA DE ADULTOS NOCTURNA
                                // 15 -> EDUCACIÓN MEDIA TECNICO MODULAR.
                                if ($numero_periodo >= '1' && $numero_periodo <= '5') {
                                    switch ($codigo_modalidad) {
                                        case ($codigo_modalidad == '03' || $codigo_modalidad == '04' || $codigo_modalidad == '05' || $codigo_modalidad == '17' || $codigo_modalidad == '18' || $codigo_modalidad == '19'):
                                            DB::update("update nota set  nota_final = round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) where id_notas = ?", [$id_notas_]);                                        
                                        break;
                                        case ($codigo_modalidad == '06' || $codigo_modalidad == '07' || $codigo_modalidad == '08' || $codigo_modalidad == '09' || $codigo_modalidad == '15' || $codigo_modalidad == '21'):  // EDUCACION MEDIA.*********//////
                                            DB::update("update nota set  nota_final = round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4)/4,0) where id_notas = ?", [$id_notas_]);                                                                                
                                        break;
                                        case ($codigo_modalidad == '10' || $codigo_modalidad == '11'):
                                            DB::update("update nota set  nota_final = round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4 + nota_p_p_5)/5,0) where id_notas = ?", [$id_notas_]);
                                        break;
                                        default:
                                            DB::update("update nota set  nota_final = round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) where id_notas = ?", [$id_notas_]);                                        
                                            break;
                                    }
                                }

                            }
        return $actual;
    }

/**
 * Recibe una lista de estudiantes y envía sus boletas usando PHPMailer.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\Response
 */
public function enviarCorreosMasivos(Request $request)
{
    try {
        // 1. Validar la información recibida del AJAX
        $request->validate([
            'estudiantes' => 'required|array',
            'estudiantes.*.email' => 'required|email',
            'estudiantes.*.datos_pdf' => 'required|string',
            'codigo_institucion' => 'required|string',
        ]);

        // 2. Obtener los "datos generales de la institución"
        $institucion = Institucion::where('id_institucion', $request->codigo_institucion)->first();
        if (!$institucion) {
            return response()->json(['status' => 'error', 'message' => 'Institución no encontrada.'], 404);
        }

        // 3. Recorrer la lista de estudiantes
        foreach ($request->estudiantes as $estudianteData) {

                $emailDestino = $estudianteData['email'];
                $datosPdfString = $estudianteData['datos_pdf'];

                // ===== START: UPDATE STUDENT EMAIL IF EMPTY =====
                try {
                    // Extract codigo_alumno (assuming it's the second part of datos_pdf)
                    $partesPdf = explode('-', $datosPdfString);
                    if (isset($partesPdf[1])) {
                        $codigo_alumno = $partesPdf[1];

                        // Find the student record (using Eloquent or DB facade)
                        // Option 1: Using Eloquent Model (if you have an Estudiante model)
                         $alumno = Estudiante::where('id_alumno', $codigo_alumno)->first();

                        // Option 2: Using DB Facade
                        // $alumno = DB::table('alumno')->where('id_alumno', $codigo_alumno)->first();
                           // Log::info('alumno antes del if($alumno) ' . $alumno . 'Código Estudiante: Antes del $alumno...' . $codigo_alumno . " Correo: " . $emailDestino);
                        if ($alumno) {
                            if (trim($alumno->direccion_email ?? '') === '') {
                                // Update the email
                                // Option 1: Eloquent
                                 $alumno->direccion_email = $emailDestino;
                                 $alumno->save();

                                // Option 2: DB Facade
                                // DB::table('alumno')
                                  //  ->where('id_alumno', $codigo_alumno)
                                    //->update(['direccion_email' => $emailDestino]);

                                Log::info("Email actualizado para alumno ID {$codigo_alumno} a {$emailDestino}");
                            }else{
                                // Log::info("El email ya existe para el alumno ID {$codigo_alumno}, no se actualiza. Email existente: {$alumno->direccion_email}"); // Tu log anterior
                                    Log::info("El email NO está vacío para el alumno ID {$codigo_alumno}. Contenido: '{$alumno->direccion_email}'"); // Log mejorado
                                }
                        } else {
                             Log::warning("No se encontró alumno con ID {$codigo_alumno} para actualizar email.");
                        }
                    } else {
                         Log::warning("No se pudo extraer codigo_alumno de datos_pdf: {$datosPdfString}");
                    }
                } catch (\Exception $updateException) {
                     Log::error("Error al intentar actualizar email para datos {$datosPdfString}: " . $updateException->getMessage());
                     // Continue processing the email even if the update fails
                }
                // ===== END: UPDATE STUDENT EMAIL IF EMPTY =====

            $mail = new PHPMailer(true); // Crea una nueva instancia de PHPMailer

            try {
                // ===== 4. CONFIGURACIÓN MANUAL DE GMAIL =====
                // (La misma que funcionó en Tinker)
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Descomenta para ver logs
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'coed.10391@gmail.com'; // Tu usuario
                $mail->Password   = 'vpdcqqxrwbncehkq'; // Tu contraseña de app de 16 letras
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                // ===========================================

                //Receptores
                $mail->setFrom('coed.10391@gmail.com', convertirTexto($institucion->nombre_institucion)); // De:

                // ===== ¡MODIFICACIÓN DE PRUEBA! =====
                 //$mail->addAddress($estudianteData['email']); // <-- Línea de Producción
                //$mail->addAddress('carlos.w.cerna@gmail.com'); // <-- Línea de Prueba
                $mail->addAddress('carlos.wilfredo.cerna@clases.edu.sv'); // <-- TU NUEVA LÍNEA DE PRUEBA
                    // =
                // ===================================

                // Genera el PDF llamando a la función que pegamos
                    $pdfOutput = $this->generarBoletaPdf($estudianteData['datos_pdf']);

                //Contenido
                $mail->isHTML(true);
                $mail->Subject = 'Boleta de Calificaciones - ' . convertirTexto($institucion->nombre_institucion);
                $mail->Body    = "Estimado estudiante,<br><br>Adjuntamos su boleta de calificaciones.<br><br>Atentamente,<br>" . $institucion->nombre_institucion;
                $mail->AltBody = "Estimado estudiante, adjuntamos su boleta de calificaciones.";

                // Adjuntar el PDF (generado como string)
                $mail->addStringAttachment($pdfOutput, 'Boleta.pdf', 'base64', 'application/pdf');

                $mail->send();

            } catch (PHPMailerException $e) {
                // Captura errores por cada correo individual
                Log::error("PHPMailer no pudo enviar a {$estudianteData['email']}. Error: {$mail->ErrorInfo}");
            }
        } // Fin del foreach

        // 5. Responder al AJAX
        return response()->json([
            'status' => 'success', 
            'message' => '¡' . count($request->estudiantes) . ' correos han sido procesados por PHPMailer!'
        ]);

    } catch (\Exception $e) {
        // Captura errores generales (ej. Validación)
        Log::error('Error general en enviarCorreosMasivos: ' . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

   /**
     * Función privada para generar la Boleta de Calificaciones usando FPDF.
     * Esta función toma el string de datos, busca la información en la BD
     * y genera el PDF como un string.
     *
     * @param string $datosPdfString El string (NIE-Alumno-Matricula-etc...)
     * @return string Los datos binarios del PDF
     */
    private function generarBoletaPdf($datosPdfString)
    {
        Log::info('Iniciando generación de PDF (FPDF) para: ' . $datosPdfString);

        try {
            // =================================================================
            // 1. INICIALIZAR FPDF (Como en tu PdfController)
            // =================================================================
            $fpdf = new Fpdf('L','mm','Letter');	// Formato Letter;
            $fpdf->SetMargins(15, 5, 5);
            $fpdf->SetAutoPageBreak(true, 5);
            $fpdf->SetX(30);
            // =================================================================
            // 2. PARSEAR EL STRING DE DATOS (Como en tu PdfController)
            // =================================================================
            // NIE - ID - CODIGO MATRICULA - (CODIGO GRADO - SECCION - TURNO -MODALIDAD) - ANNLECTIVO - INSTITUCION - PERSONAL - SI/NO
            $EstudianteMatricula = explode("-", $datosPdfString);

            // IMPORTANTE: Asumimos que la lógica siempre viene de un solo estudiante,
            // no del "Tablero". Si necesitas la lógica del tablero aquí, házmelo saber.
            if (count($EstudianteMatricula) < 7) {
                 Log::error('Error PDF (FPDF): String de datos incompleto.', $EstudianteMatricula);
                 throw new \Exception('String de datos incompleto.');
            }
            $codigo_nie = $EstudianteMatricula[0];
            $codigo_alumno = $EstudianteMatricula[1];
            $codigo_matricula = $EstudianteMatricula[2];
            $codigo_gradoseccionturnomodalidad = $EstudianteMatricula[3];
            $codigo_modalidad = substr($codigo_gradoseccionturnomodalidad, 6, 2);
            $codigo_turno = substr($codigo_gradoseccionturnomodalidad, 4, 2);
            $codigo_seccion = substr($codigo_gradoseccionturnomodalidad, 2, 2);
            $codigo_grado = substr($codigo_gradoseccionturnomodalidad, 0, 2);
            $codigo_annlectivo = $EstudianteMatricula[4];
            $codigo_institucion = $EstudianteMatricula[5];
            $codigo_personal_docente = $EstudianteMatricula[6]; // Docente que generó
            // $crear_archivos = $EstudianteMatricula[7]; // No lo necesitamos aquí

            // =================================================================
            // 3. OBTENER DATOS DE LA BASE DE DATOS (COPIADO DE TU PdfController)
            // =================================================================
            
            // --- Catálogo Area Asignatura ---
            $catalogo_area_asignatura_codigo = array();
            $catalogo_area_asignatura_area = array();
            // ... (variables lógicas $catalogo_area_basica, etc.) ...
             $catalogo_area_basica = true; $catalogo_area_formativa = true; $catalogo_area_tecnica = true; $catalogo_area_edps = true; $catalogo_area_edecr = true; $catalogo_area_edre = true; $catalogo_area_complementaria = true; $catalogo_area_cc = true; $catalogo_area_alertas = true;
            $CatalogoAreaAsignatura = DB::table('catalogo_area_asignatura')->select('codigo','descripcion')->get();
            foreach($CatalogoAreaAsignatura as $response_area){
                $catalogo_area_asignatura_codigo[] = (trim($response_area->codigo));
                $catalogo_area_asignatura_area[] = (trim($response_area->descripcion));
            }

            // --- Asignaturas del Grado ---
            $AsignacionAsignatura = DB::table('a_a_a_bach_o_ciclo as aaa')
                ->join('asignatura as a','a.codigo','=','aaa.codigo_asignatura')
                ->select('aaa.orden','a.nombre as nombre_asignatura','a.codigo as codigo_asignatura','a.codigo_cc as concepto_calificacion','a.codigo_area')
                ->where([
                    ['codigo_bach_o_ciclo', '=', $codigo_modalidad],
                    ['codigo_grado', '=', $codigo_grado],
                    ['codigo_ann_lectivo', '=', $codigo_annlectivo],
                ])->orderBy('aaa.orden','asc')->get();
            $datos_asignatura = ["codigo" => [], "nombre" => [], "concepto" => [], "codigo_area" => []];
            $fila_array_asignatura = 0;
            foreach($AsignacionAsignatura as $response_i){
                $datos_asignatura["codigo"][$fila_array_asignatura] = trim($response_i->codigo_asignatura);
                $datos_asignatura["nombre"][$fila_array_asignatura] = mb_convert_encoding(trim($response_i->nombre_asignatura),"ISO-8859-1","UTF-8");
                $datos_asignatura["concepto"][$fila_array_asignatura] = trim($response_i->concepto_calificacion);
                $datos_asignatura["codigo_area"][$fila_array_asignatura] = trim($response_i->codigo_area);
                $fila_array_asignatura++;
            }

            // --- Encargado del Grado ---
            $nombre_personal = ''; $firma_docente = ''; // Inicializar por si no se encuentra
            $EncargadoGrado = DB::table('encargado_grado as eg')
                ->join('personal as p','p.id_personal','=','eg.codigo_docente')
                ->select('p.id_personal','p.firma', DB::raw("TRIM(CONCAT(BTRIM(p.nombres), CAST(' ' AS VARCHAR), BTRIM(p.apellidos))) as full_name"))
                ->where([
                    ['codigo_bachillerato', '=', $codigo_modalidad],
                    ['codigo_grado', '=', $codigo_grado],
                    ['codigo_ann_lectivo', '=', $codigo_annlectivo],
                    ['codigo_seccion', '=', $codigo_seccion],
                    ['codigo_turno', '=', $codigo_turno],
                    ['encargado', '=', 'true'],
                ])->orderBy('p.id_personal','asc')->first(); // Usar first() si solo esperas uno
            if ($EncargadoGrado) {
                 $nombre_personal = mb_convert_encoding(trim($EncargadoGrado->full_name),"ISO-8859-1","UTF-8");
                 $firma_docente = trim($EncargadoGrado->firma); // No necesita conversión si es solo nombre de archivo
            } else {
                 Log::warning("No se encontró docente encargado para Grado: $codigo_grado, Seccion: $codigo_seccion, Turno: $codigo_turno, Año: $codigo_annlectivo");
            }

            // --- Información de la Institución ---
            $EstudianteInformacionInstitucion = DB::table('informacion_institucion as inf')
                ->leftjoin('personal as p','p.id_personal','=',DB::raw("CAST(inf.nombre_director AS INTEGER)"))
                ->select('inf.id_institucion','inf.codigo_institucion','inf.nombre_institucion','inf.telefono_uno','inf.logo_uno','inf.direccion_institucion','inf.nombre_director',
                            'inf.logo_dos','inf.logo_tres', DB::raw("TRIM(CONCAT(BTRIM(p.nombres), CAST(' ' AS VARCHAR), BTRIM(p.apellidos))) as full_name"))
                ->where('inf.id_institucion', '=', $codigo_institucion)
                ->orderBy('inf.id_institucion','asc')->limit(1)->first(); // Usar first()

             $nombre_institucion = ''; $nombre_director = ''; $codigo_institucion_infra = ''; $logo_uno_path = ''; $firma_director_path = ''; $sello_direccion_path = '';
             if($EstudianteInformacionInstitucion){
                 $nombre_institucion = mb_convert_encoding(trim($EstudianteInformacionInstitucion->nombre_institucion),'ISO-8859-1','UTF-8');
                 $nombre_director = mb_convert_encoding(trim($EstudianteInformacionInstitucion->full_name),'ISO-8859-1','UTF-8');
                 $codigo_institucion_infra = (trim($EstudianteInformacionInstitucion->codigo_institucion));
                 // *** CAMBIO IMPORTANTE PARA IMÁGENES ***
                 $logo_uno_path = public_path('img/' . trim($EstudianteInformacionInstitucion->logo_uno));
                 $firma_director_path = public_path('img/' . trim($EstudianteInformacionInstitucion->logo_dos));
                 $sello_direccion_path = public_path('img/' . trim($EstudianteInformacionInstitucion->logo_tres));
             } else {
                 Log::error("No se encontró información para la Institución ID: $codigo_institucion");
                 throw new \Exception("Institución no encontrada.");
             }

            // --- Datos del Estudiante y Notas ---
            $EstudianteBoleta = DB::table('alumno as a')
                ->join('alumno_matricula AS am','a.id_alumno','=','am.codigo_alumno')
                ->join('nota AS n','am.id_alumno_matricula','=','n.codigo_matricula')
                ->join('bachillerato_ciclo AS bach', 'bach.codigo','=','am.codigo_bach_o_ciclo')
                ->join('grado_ano AS gr', 'gr.codigo','=','am.codigo_grado')
                ->join('seccion AS sec', 'sec.codigo','=','am.codigo_seccion')
                ->join('turno AS tur', 'tur.codigo','=','am.codigo_turno')
                ->join('asignatura AS asig','asig.codigo','=','n.codigo_asignatura')
                ->join('ann_lectivo AS ann','ann.codigo','=','am.codigo_ann_lectivo')
                ->select('a.*', // Seleccionar todo de alumno para tener foto, genero, etc.
                         'am.*', // Seleccionar todo de matricula
                         'n.*', // Seleccionar todo de nota
                         'bach.nombre AS nombre_modalidad', 'gr.nombre as nombre_grado', 'sec.nombre as nombre_seccion','tur.nombre as nombre_turno',
                         'asig.codigo_area',
                         'ann.nombre as nombre_annlectivo', 'a.direccion_email as correo_estudiante',
                        DB::raw("TRIM(CONCAT(BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno), CAST(' ' AS VARCHAR), BTRIM(a.nombre_completo))) as full_name"),
                        DB::raw("TRIM(CONCAT(BTRIM(a.nombre_completo), CAST(' ' AS VARCHAR), BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno))) as full_nombres_apellidos")
                        )
                ->where('n.codigo_matricula', '=', $codigo_matricula)
                ->orderBy('n.orden','asc') // Asumiendo que n.orden ordena las asignaturas correctamente
                ->get();

            if($EstudianteBoleta->isEmpty()){
                 Log::error("No se encontraron notas para la matrícula ID: $codigo_matricula");
                 throw new \Exception("Notas no encontradas.");
            }

            // =================================================================
            // 4. CONSTRUIR EL PDF (COPIADO DE TU PdfController)
            // =================================================================
            $fpdf->AddPage();
            $fpdf->SetFont('Arial', 'B', 9);
            $fpdf->SetX(30);

            $fila = 1; $fill = true;
            $alto_cell = array('5'); $ancho_cell = array('60','6','30','30','180'); // Anchos base
            
            // --- Bucle principal para las notas/asignaturas ---
            foreach($EstudianteBoleta as $response){
                // Extraer datos comunes solo en la primera iteración
                if($fila == 1){
                     $nombre_completo = convertirTexto(trim($response->full_nombres_apellidos));
                     $codigo_nie = (trim($response->codigo_nie));
                     $nombre_modalidad = mb_convert_encoding(trim($response->nombre_modalidad),'ISO-8859-1','UTF-8');
                     $nombre_grado = mb_convert_encoding(trim($response->nombre_grado),'ISO-8859-1','UTF-8');
                     $nombre_seccion = mb_convert_encoding(trim($response->nombre_seccion),'ISO-8859-1','UTF-8');
                     $nombre_turno = mb_convert_encoding(trim($response->nombre_turno),'ISO-8859-1','UTF-8');
                     $nombre_annlectivo = mb_convert_encoding(trim($response->nombre_annlectivo),'ISO-8859-1','UTF-8');
                     $nombre_foto = (trim($response->foto));
                     $codigo_genero = (trim($response->codigo_genero));
                     $correo_estudiante = (trim($response->correo_estudiante));

                    // --- Encabezado del PDF ---
                    if (file_exists($logo_uno_path)) { $fpdf->Image($logo_uno_path, 10, 10, 20, 25); }
                    $fpdf->Cell(40, $alto_cell[0], "CENTRO ESCOLAR:", 1, 0, 'L');
                    $fpdf->Cell(135, $alto_cell[0], $codigo_institucion_infra . " - " . $nombre_institucion, 1, 1, 'L');
                    $fpdf->SetX(30);
                    $fpdf->Cell(40, $alto_cell[0], "Estudiante", 1, 0, 'L');
                    $fpdf->Cell(135, $alto_cell[0], $codigo_nie . " - " . $nombre_completo, 1, 1, 'L');
                    $fpdf->SetX(30);
                    $fpdf->Cell(40, $alto_cell[0], mb_convert_encoding("Correo Electrónico", "ISO-8859-1", "UTF-8"), 1, 0, 'L');
                    $fpdf->Cell(135, $alto_cell[0], $correo_estudiante, 1, 1, 'L');
                    $fpdf->SetX(30);
                    $fpdf->Cell(40, $alto_cell[0], mb_convert_encoding("Nivel", "ISO-8859-1", "UTF-8"), 1, 0, 'L');
                    $fpdf->Cell(115, $alto_cell[0], $nombre_modalidad, 1, 1, 'L'); // Ajustado ancho
                    $fpdf->SetX(30);
                    $fpdf->Cell(15, $alto_cell[0], "Grado", 1, 0, 'L');
                    $fpdf->Cell(70, $alto_cell[0], $nombre_grado, 1, 0, 'L');
                    $fpdf->Cell(15, $alto_cell[0], mb_convert_encoding("Sección", "ISO-8859-1", "UTF-8"), 1, 0, 'L');
                    $fpdf->Cell(10, $alto_cell[0], $nombre_seccion, 1, 0, 'C');
                    $fpdf->Cell(20, $alto_cell[0], "Turno", 1, 0, 'L');
                    $fpdf->Cell(30, $alto_cell[0], $nombre_turno, 1, 0, 'C');
                    $fpdf->Cell(22, $alto_cell[0], mb_convert_encoding("Año Lectivo", "ISO-8859-1", "UTF-8"), 1, 0, 'L');
                    $fpdf->Cell(10, $alto_cell[0], mb_convert_encoding($nombre_annlectivo, "ISO-8859-1", "UTF-8"), 1, 1, 'C');

                    // --- Foto del estudiante ---
                    $foto_path_rel = 'img/fotos/'.$codigo_institucion_infra.'/'.$nombre_foto;
                    $foto_path_abs = public_path($foto_path_rel);
                    if (file_exists($foto_path_abs) && !empty($nombre_foto)) {
                        $fpdf->Image($foto_path_abs, 240, 5, 35, 40);
                    } else {
                        $avatar = ($codigo_genero == '01') ? 'avatar_masculino.png' : 'avatar_femenino.png';
                        $avatar_path = public_path('img/' . $avatar);
                         if (file_exists($avatar_path)) { $fpdf->Image($avatar_path, 240, 5, 35, 40); }
                    }

                    // --- Leyenda de notas ---
                    $fpdf->SetX(30);
                    $fpdf->SetFont('Arial', 'B', '7');
                    $fpdf->Cell(30, $alto_cell[0], "A1->Actividad 1 (35%)", 'LR', 0, 'L');
                    $fpdf->Cell(30, $alto_cell[0], "A2->Actividad 2 (35%)", 'LR', 0, 'L');
                    $fpdf->Cell(35, $alto_cell[0], "PO->Prueba Objetiva (30%)", 'LR', 0, 'L');
                    $fpdf->Cell(35, $alto_cell[0], "PP->Promedio Periodo", 'LR', 0, 'L');
                    $fpdf->Cell(30, $alto_cell[0], "PF->Promedio Final", 'LR', 1, 'L');
                    $fpdf->SetX(30);
                    $mensaje_1 = mb_convert_encoding("NR1->Nota Recuperación 1", 'ISO-8859-1', 'UTF-8');
                    $mensaje_2 = mb_convert_encoding("NR2->Nota Recuperación 2", 'ISO-8859-1', 'UTF-8'); // Corregido NR1 a NR2
                    $fpdf->Cell(35, $alto_cell[0], $mensaje_1, 'LR', 0, 'L');
                    $fpdf->Cell(35, $alto_cell[0], $mensaje_2, 'LR', 0, 'L');
                    $fpdf->Cell(20, $alto_cell[0], ("A->Aprobado"), 'LR', 0, 'L');
                    $fpdf->Cell(20, $alto_cell[0], ("R->Reprobado"), 'LR', 0, 'L');
                    $fpdf->Cell(20, $alto_cell[0], ("NF->Nota Final"), 'LR', 1, 'L');

                    // --- Cabecera de la tabla de calificaciones ---
                    // Calcular $valor_periodo, $valor_actividades, $ancho_area_asignatura basado en $codigo_modalidad (como en tu PdfController)
                     if($codigo_modalidad >= '03' && $codigo_modalidad <= '05'){ $valor_periodo = 2; $valor_actividades = 15; $ancho_area_asignatura = 180; }
                     else if($codigo_modalidad >= '06' && $codigo_modalidad <= '09' || $codigo_modalidad == '15'){ $valor_periodo = 3; $valor_actividades = 20; $ancho_area_asignatura = 210; }
                     else if($codigo_modalidad >= '10' && $codigo_modalidad <= '12'){ $valor_periodo = 4; $valor_actividades = 25; $ancho_area_asignatura = 240; }
                     else if($codigo_modalidad == '21' || $codigo_modalidad == '17'){ $valor_periodo = 3; $valor_actividades = 20; $ancho_area_asignatura = 210; } // Asumiendo 21 y 17 como Media
                     else{ $valor_periodo = 2; $valor_actividades = 15; $ancho_area_asignatura = 186; }

                    $periodos_a = array('PERIODO 1', 'PERIODO 2', 'PERIODO 3', 'PERIODO 4', 'PERIODO 5', 'PROMEDIO FINAL', 'R');
                    $actividad_periodo = array('A1','A2','PO','R','PP','PF'); // PF no se usa en el loop

                    $fpdf->Cell($ancho_cell[0], $alto_cell[0], "", 'LRT', 0, 'L');
                    for ($pp = 0; $pp <= $valor_periodo; $pp++) {
                        $border = ($pp == $valor_periodo) ? 1 : 'LRTB';
                        $newLine = ($pp == $valor_periodo) ? 1 : 0;
                        $fpdf->Cell($ancho_cell[2], $alto_cell[0], $periodos_a[$pp], $border, $newLine, 'C');
                    }

                    $fpdf->Cell($ancho_cell[0], $alto_cell[0], "Componente del Plan de Estudio", 'LRB', 0, 'C');
                    for ($pp = 0; $pp <= $valor_periodo; $pp++) {
                        // A1, A2, PO, R, PP
                        for ($ap = 0; $ap < count($actividad_periodo) - 1; $ap++) { // Quitamos PF del loop
                            $fpdf->Cell($ancho_cell[1], $alto_cell[0], $actividad_periodo[$ap], 1, 0, 'C');
                        }
                        // La celda PF solo se aplica al promedio final
                        if ($pp == $valor_periodo) {
                            $fpdf->Cell($ancho_cell[1], $alto_cell[0], $actividad_periodo[5], 1, 0, 'C'); // PF
                        }
                    }
                    // NR1, NR2, NF, Resultado
                    $fpdf->Cell($ancho_cell[1], $alto_cell[0], 'NR1', 1, 0, 'C');
                    $fpdf->Cell($ancho_cell[1], $alto_cell[0], 'NR2', 1, 0, 'C');
                    $fpdf->Cell($ancho_cell[1], $alto_cell[0], 'NF', 1, 0, 'C');
                    $fpdf->Cell($ancho_cell[1], $alto_cell[0], $periodos_a[6], 1, 1, 'C'); // R
                } // Fin del if($fila == 1)

                // --- Datos específicos de la asignatura ---
                $codigo_asignatura = (trim($response->codigo_asignatura));
                $codigo_area = (trim($response->codigo_area));
                $nota_final = (trim($response->nota_final)); // Nota final calculada en BD
                // Array de notas (ajustado índice)
                $nota_actividades_0 = array(
                    null, // índice 0 no usado
                    $response->nota_a1_1, $response->nota_a2_1, $response->nota_a3_1, $response->nota_r_1, $response->nota_p_p_1, // 1-5
                    $response->nota_a1_2, $response->nota_a2_2, $response->nota_a3_2, $response->nota_r_2, $response->nota_p_p_2, // 6-10
                    $response->nota_a1_3, $response->nota_a2_3, $response->nota_a3_3, $response->nota_r_3, $response->nota_p_p_3, // 11-15
                    $response->nota_a1_4, $response->nota_a2_4, $response->nota_a3_4, $response->nota_r_4, $response->nota_p_p_4, // 16-20
                    $response->nota_a1_5, $response->nota_a2_5, $response->nota_a3_5, $response->nota_r_5, $response->nota_p_p_5, // 21-25
                    $response->recuperacion, $response->nota_recuperacion_2, $response->nota_final // 26, 27, 28
                );

                // --- Encabezado de Área (Separador) ---
                $fpdf->SetFillColor(200, 200, 200); $fpdf->SetTextColor(0); $fpdf->SetFont('Times', 'B', 12);
                if($codigo_area == '01' && $catalogo_area_basica){ $fpdf->Cell($ancho_area_asignatura, 6, strtoupper(mb_convert_encoding($catalogo_area_asignatura_area[0], "ISO-8859-1", "UTF-8")), 1, 1, 'L', true); $catalogo_area_basica = false; }
                if($codigo_area == '02' && $catalogo_area_formativa){ $fpdf->Cell($ancho_area_asignatura, 6, strtoupper(mb_convert_encoding($catalogo_area_asignatura_area[1], "ISO-8859-1", "UTF-8")), 1, 1, 'L', true); $catalogo_area_formativa = false; }
                if($codigo_area == '03' && $catalogo_area_tecnica){ $fpdf->Cell($ancho_area_asignatura, 6, strtoupper(mb_convert_encoding($catalogo_area_asignatura_area[2], "ISO-8859-1", "UTF-8")), 1, 1, 'L', true); $catalogo_area_tecnica = false; }
                if($codigo_area == '07' && $catalogo_area_cc){ $fpdf->Cell($ancho_area_asignatura, 6, strtoupper(mb_convert_encoding($catalogo_area_asignatura_area[6], "ISO-8859-1", "UTF-8")), 1, 1, 'L', true); $catalogo_area_cc = false; }
                if($codigo_area == '08' && $catalogo_area_complementaria){ $fpdf->Cell($ancho_area_asignatura, 6, strtoupper(mb_convert_encoding($catalogo_area_asignatura_area[7], "ISO-8859-1", "UTF-8")), 1, 1, 'L', true); $catalogo_area_complementaria = false; }
                // ... (Añadir if para otras áreas si es necesario) ...
                $fpdf->SetFillColor(212, 230, 252); $fpdf->SetTextColor(0); $fpdf->SetFont('Times', '', 10);

                // --- Fila de Notas de la Asignatura ---
                $fpdf->SetFont('Arial', '', '7');
                $buscar = array_search($codigo_asignatura, $datos_asignatura['codigo']);
                $NombreAsignatura = ($buscar !== false) ? $datos_asignatura['nombre'][$buscar] : 'Asignatura Desconocida';
                $ConceptoAsignatura = ($buscar !== false) ? $datos_asignatura['concepto'][$buscar] : '';

                // Ajustar ancho de celda nombre asignatura
                 if($codigo_area == "03" and $codigo_modalidad == "15"){ $NumeroAnchoColumna = 4; $NombreStringAncho = 165; }
                 else { $NumeroAnchoColumna = 0; $NombreStringAncho = 60; }
                 $fpdf->Cell($ancho_cell[$NumeroAnchoColumna], $alto_cell[0], $codigo_asignatura . "-" . substr($NombreAsignatura, 0, $NombreStringAncho), 1, 0, 'L');

                // Notas por periodo y actividades
                if(!($codigo_area == "03" and $codigo_modalidad == "15")){ // Si NO es Técnico Modular
                    for ($na = 1; $na <= $valor_actividades; $na++) {
                        $isPromedioPeriodo = ($na % 5 === 0);
                        $notaActual = $nota_actividades_0[$na];
                        $celdaNota = ($notaActual == 0 && $notaActual !== null && $notaActual !== '0.0') ? '' : $notaActual; // Mostrar 0 si es 0.0, sino vacío

                        if ($isPromedioPeriodo) { $fpdf->SetFillColor(218, 215, 215); $fpdf->SetFont('Arial', 'B', '7'); }
                        else { $fpdf->SetFillColor(255, 255, 255); $fpdf->SetFont('Arial', '', '7'); }

                        // Lógica de Conceptos para Competencia Ciudadana (Area 07)
                        if ($codigo_area == '07' && $isPromedioPeriodo && $celdaNota !== '') {
                             $celdaNota = resultado_concepto($codigo_modalidad, $celdaNota);
                             $fpdf->Cell($ancho_cell[1], $alto_cell[0], $celdaNota, 1, 0, 'C', true);
                        } else if ($codigo_area == '07' && !$isPromedioPeriodo) {
                             $fpdf->Cell($ancho_cell[1], $alto_cell[0], '', 'TB', 0, 'C', true); // Vacío para A1,A2,PO,R en CC
                        } else {
                             $fpdf->Cell($ancho_cell[1], $alto_cell[0], $celdaNota, 1, 0, 'C', true);
                        }
                    } // fin for $na
                } // fin if NO Tecnico Modular

                // --- Columnas Finales (PF, NR1, NR2, NF, Resultado) ---
                $fpdf->SetFont('Arial', 'B', '7'); $fpdf->SetFillColor(255, 255, 255);

                // PF (Promedio Final calculado en BD)
                $pf_mostrar = ($nota_actividades_0[28] == 0 && $nota_actividades_0[28] !== null) ? '' : $nota_actividades_0[28];
                $fpdf->Cell($ancho_cell[1], $alto_cell[0], $pf_mostrar, 1, 0, 'C');

                // NR1
                $nr1_mostrar = ($nota_actividades_0[26] == 0 && $nota_actividades_0[26] !== null) ? '' : $nota_actividades_0[26];
                $fpdf->Cell($ancho_cell[1], $alto_cell[0], $nr1_mostrar, 1, 0, 'C');

                // NR2
                $nr2_mostrar = ($nota_actividades_0[27] == 0 && $nota_actividades_0[27] !== null) ? '' : $nota_actividades_0[27];
                $fpdf->Cell($ancho_cell[1], $alto_cell[0], $nr2_mostrar, 1, 0, 'C');

                // NF y Resultado (Usando helper)
                if($pf_mostrar === ''){
                    $fpdf->Cell($ancho_cell[1], $alto_cell[0], '', 1, 0, 'C'); // NF vacío
                    $fpdf->Cell($ancho_cell[1], $alto_cell[0], '', 1, 1, 'C'); // Resultado vacío
                } else {
                     // Llama al helper resultado_final para obtener NF y Resultado
                     $result = resultado_final($codigo_modalidad, $nota_actividades_0[26], $nota_actividades_0[27], $nota_actividades_0[28], $codigo_area);
                     $nf_final = round($result[1], 0); // Nota Final redondeada
                     $resultado_letra = $result[0]; // 'A' o 'R'

                     if($resultado_letra == "R"){ $fpdf->SetTextColor(255,0,0); } else { $fpdf->SetTextColor(0); }
                     $fpdf->Cell($ancho_cell[1], $alto_cell[0], $nf_final, 1, 0, 'C');      // NF
                     $fpdf->Cell($ancho_cell[1], $alto_cell[0], $resultado_letra, 1, 1, 'C'); // Resultado
                     $fpdf->SetTextColor(0); // Restaurar color
                }
                $fila++; // Incrementar contador de filas procesadas
            } // FIN DEL FOREACH $EstudianteBoleta

            // =================================================================
            // 5. PIE DE PÁGINA Y FIRMAS (COPIADO DE TU PdfController)
            // =================================================================
            $ultima_linea = $fpdf->GetY();
            if ($ultima_linea > 170) { $fpdf->AddPage(); $ultima_linea = 10; } // Evitar que firmas queden cortadas
            
            $fpdf->SetY($ultima_linea + 20); // Ajustar espacio para firmas
            $fpdf->SetFont('Arial', '', 9); // Reset font
            $fpdf->SetX(40);
            $fpdf->Cell($ancho_cell[1], $alto_cell[0], $nombre_director, 0, 0, 'C'); // Centrado
            $fpdf->Cell(120, $alto_cell[0], '', 0, 0, 'C');
            $fpdf->Cell($ancho_cell[1], $alto_cell[0], $nombre_personal, 0, 1, 'C'); // Centrado
            $fpdf->SetX(40);
            $fpdf->Cell($ancho_cell[1], $alto_cell[0], 'Director', 0, 0, 'C'); // Centrado
            $fpdf->Cell(120, $alto_cell[0], '', 0, 0, 'C');
            $fpdf->Cell($ancho_cell[1], $alto_cell[0], 'Docente responsable', 0, 1, 'C'); // Centrado

            // --- Firma Docente ---
            if (!empty($firma_docente)) {
                 $firma_docente_path_rel = 'img/firmas/'.$codigo_institucion_infra.'/'.$firma_docente;
                 $firma_docente_path_abs = public_path($firma_docente_path_rel);
                 if (file_exists($firma_docente_path_abs)) {
                    $fpdf->Image($firma_docente_path_abs, $fpdf->GetX() + 130, $fpdf->GetY() - 25, 20, 30); // Ajustar posición X
                 }
            }
            // --- Firma Director y Sello ---
             if (file_exists($firma_director_path)) { $fpdf->Image($firma_director_path, 30, $ultima_linea + 5, 20, 15); } // Ajustar posición X
             if (file_exists($sello_direccion_path)) { $fpdf->Image($sello_direccion_path, 60, $ultima_linea + 5, 25, 25); } // Ajustar posición X

            // =================================================================
            // 6. DEVOLVER EL PDF COMO STRING
            // =================================================================
            return $fpdf->Output('S'); // 'S' devuelve el contenido como string

        } catch (\Exception $e) {
            Log::error('Error al generar PDF (FPDF): ' . $e->getMessage() . ' en línea ' . $e->getLine() . ' Archivo: ' . $e->getFile());
            // Genera un PDF de error simple si falla
            $fpdfError = new Fpdf('P', 'mm', 'Letter');
            $fpdfError->AddPage();
            $fpdfError->SetFont('Arial', 'B', 12);
            $fpdfError->Cell(0, 10, 'Error al generar la boleta', 0, 1, 'C');
            $fpdfError->SetFont('Arial', '', 10);
            $fpdfError->MultiCell(0, 5, 'Se produjo un error al procesar su solicitud. Por favor, contacte al administrador.');
            $fpdfError->MultiCell(0, 5, 'Error: ' . mb_convert_encoding($e->getMessage(), 'ISO-8859-1', 'UTF-8'));
            return $fpdfError->Output('S');
        }
    }

}   // fin de la función.

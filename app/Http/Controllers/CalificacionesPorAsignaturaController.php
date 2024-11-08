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
use App\Mail\BoletaEstudiantes;


class CalificacionesPorAsignaturaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
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

            $nombre_periodos = array('nota_p_p_','recuperacion','nota_recuperacion_2',"nota_final");
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
                $nombre_periodos = array('nota_p_p_','recuperacion','nota_recuperacion_2');
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
                                        case ($codigo_modalidad >= '03' && $codigo_modalidad <= '05'):
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
                                        case ($codigo_modalidad == '15'): // EDUCACIÓN MEDIA TÉCNICA MODULAR PRIMER AÑO//
                                            if($codigo_area == '07' || $numero_periodo == '6' || $numero_periodo == '7' || $codigo_asignatura = '726'){   // CONDICIÓN PARA COMPETENCIA CIUDADANA, NOTA (RECUPERACION, NOTA_RECUPERACION_2)
                                                
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
                                        case ($codigo_modalidad >= '03' && $codigo_modalidad <= '05'):
                                            DB::update("update nota set  nota_final = round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) where id_notas = ?", [$id_notas_]);                                        
                                        break;
                                        case ($codigo_modalidad == '06' || $codigo_modalidad == '07' || $codigo_modalidad == '08' || $codigo_modalidad == '09'|| $codigo_modalidad == '15'):  // EDUCACION MEDIA.*********//////
                                            DB::update("update nota set  nota_final = round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4)/4,0) where id_notas = ?", [$id_notas_]);                                                                                
                                        break;
                                        case ($codigo_modalidad >= '10' && $codigo_modalidad <= '12'):
                                            DB::update("update nota set  nota_final = round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4 + nota_p_p_4)/5,0) where id_notas = ?", [$id_notas_]);
                                        break;
                                        default:
                                            DB::update("update nota set  nota_final = round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) where id_notas = ?", [$id_notas_]);                                        
                                            break;
                                    }
                                }

                            }
        return $actual;
    }

}

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

class CalificacionesPorAsignaturaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $annlectivo=Annlectivo::where('estatus', true)->orderBy('codigo', 'desc')->pluck('nombre','codigo')->toarray();
        //$query = DB::table('tablethis')->where('id', $result)->where('type', 'like')->orderBy('created_at', 'desc');
        //$annlectivo = Annlectivo::pluck('nombre','codigo')->toarray();
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
    public function update(Request $request, $id)
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
                    $nombres_ = trim($response->nombre_grado) . ' ' . trim($response->nombre_seccion) . ' ' . trim($response->nombre_turno);
                    $GradoSeccionTurno[$fila_array] = array ( 
                        "codigo_gradoseccionturno" => $codigos_,
                        "nombre_gradoseccionturno" => $nombres_
                    ); 
                    $fila_array++;
                }
            return $GradoSeccionTurno;
    }
    public function getGradoSeccionAsignaturas()
    {
        $codigo_personal = $_POST['id'];
        $codigo_annlectivo = $_POST['codigo_annlectivo'];
        $codigo_gradoseccionturno = $_POST['codigo_gradoseccionturno'];
        $codigo_grado = substr($codigo_gradoseccionturno,0,2);
        $codigo_seccion = substr($codigo_gradoseccionturno,2,2);
        $codigo_turno = substr($codigo_gradoseccionturno,4,2);

        $GradoSeccionTurnoAsignaturas = array();
           
            $CargaDocente = DB::table('carga_docente')
                ->join('bachillerato_ciclo','carga_docente.codigo_bachillerato','=','bachillerato_ciclo.codigo')
                ->join('grado_ano','carga_docente.codigo_grado','=','grado_ano.codigo')
                ->join('seccion', 'carga_docente.codigo_seccion', '=', 'seccion.codigo')                
                ->join('turno','carga_docente.codigo_turno', '=', 'turno.codigo')
                ->join('asignatura','carga_docente.codigo_asignatura','=','asignatura.codigo')
                ->select('codigo_bachillerato', 'codigo_grado','codigo_seccion', 'codigo_turno', 'codigo_docente','bachillerato_ciclo.nombre as nombre_bachillerato', 'grado_ano.nombre as nombre_grado'
                ,'seccion.nombre as nombre_seccion', 'turno.nombre as nombre_turno','codigo_asignatura','asignatura.nombre as nombre_asignatura')
                ->where('codigo_docente', '=', $codigo_personal)
                ->where([
                    ['codigo_docente', '=', $codigo_personal],
                    ['codigo_ann_lectivo', '=', $codigo_annlectivo],
                    ['codigo_grado', '=', $codigo_grado],
                    ['codigo_seccion', '=', $codigo_seccion],
                    ['codigo_turno', '=', $codigo_turno],
                    ])
                ->get();
                
                $fila_array = 0;
                foreach($CargaDocente as $response){  //Llenar el arreglo con datos
                    $codigos_ = $response->codigo_asignatura;
                    $nombres_ = trim($response->nombre_asignatura);
                    $GradoSeccionTurnoAsignaturas[$fila_array] = array ( 
                        "codigo_asignatura" => $codigos_,
                        "nombre_asignatura" => $nombres_
                    ); 
                    $fila_array++;
                }
            return $GradoSeccionTurnoAsignaturas;
    }

    public function getGradoSeccionCalificacionesAsignaturas()
    {
        $codigo_personal = $_POST['id'];
        $codigo_annlectivo = $_POST['codigo_annlectivo'];
        $codigo_gradoseccionturno = $_POST['codigo_gradoseccionturno'];
        $codigo_asignatura = $_POST['codigo_asignatura'];
        $codigo_actividad = $_POST['codigo_actividad'];
        $codigo_periodo = $_POST['codigo_periodo'];
        $codigo_grado = substr($codigo_gradoseccionturno,0,2);
        $codigo_seccion = substr($codigo_gradoseccionturno,2,2);
        $codigo_turno = substr($codigo_gradoseccionturno,4,2);
        $codigo_modalidad = substr($codigo_gradoseccionturno,6,2);
        $BuscarMatricula = array(); $BuscarCalificaciones = array(); $BuscarCalificacionesF = array();
           
            $EstudiantesMatricula = DB::table('alumno_matricula')
                ->select('id_alumno_matricula as codigo_matricula','codigo_alumno')
                ->where([
                    ['codigo_bach_o_ciclo', '=', $codigo_modalidad],
                    ['codigo_ann_lectivo', '=', $codigo_annlectivo],
                    ['codigo_grado', '=', $codigo_grado],
                    ['codigo_seccion', '=', $codigo_seccion],
                    ['codigo_turno', '=', $codigo_turno],
                    ])
                ->get();
                
                $fila_array = 0;
                foreach($EstudiantesMatricula as $response){  //Llenar el arreglo con datos
                    $codigo_matricula = $response->codigo_matricula;
                    $codigo_alumno = $response->codigo_alumno;

                    $BuscarCalificaciones = DB::table('nota')
                    ->join('alumno','nota.codigo_alumno','=','alumno.id_alumno')
                    ->join('asignatura','nota.codigo_asignatura','=','asignatura.codigo')
                    ->select('id_notas as codigo_calificacion','asignatura.nombre as nombre_asignatura','nombre_completo','apellido_paterno','apellido_materno')
                    ->where([
                        ['codigo_matricula', '=', $codigo_matricula],
                        ['codigo_alumno', '=', $codigo_alumno],
                        ['codigo_asignatura', '=', $codigo_asignatura],
                        ])
                    ->get();

                    foreach($BuscarCalificaciones as $response){  //Llenar el arreglo con dato
                        $codigo_calificacion = $response->codigo_calificacion;
                        $nombre_asignatura = $response->nombre_asignatura;
                        $nombre_completo = trim($response->nombre_completo) . " " . trim($response->apellido_paterno) . " " . trim($response->apellido_materno);
                            // recorrer matriz
                            $BuscarCalificaciones[$fila_array] = array ( 
                                "codigo_calificacion" => $codigo_calificacion,
                                "nombre_asignatura" => $nombre_asignatura,
                                "nombre_completo" => $nombre_completo
                            ); 
                            array_push($BuscarCalificacionesF,$BuscarCalificaciones);
                        // aumentar fila
                            $fila_array++;
                        }
                }

            return $BuscarCalificacionesF;
    }
}
